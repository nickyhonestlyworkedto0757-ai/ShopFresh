<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = User::where('role', '!=', 'customer')->orderByRaw("CASE role WHEN 'super_admin' THEN 0 ELSE 1 END")->orderBy('name')->paginate(20);
        return view('admin.admin-users.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.admin-users.form', ['admin' => new User(), 'roles' => $this->staffRoles()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:super_admin,product_manager,order_manager,support,delivery',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $admin = User::create(array_merge($data, [
            'is_admin' => true,
            'api_token' => Str::random(60),
        ]));
        \App\Support\AdminAllowlist::add($admin->email);
        ActivityLogger::log('admin.create', $admin->email, 'Role: ' . $admin->role);
        return redirect()->route('admin.admin-users.index')->with('success', 'Admin account created.');
    }

    public function edit(User $admin)
    {
        abort_if($admin->role === 'customer', 404);
        return view('admin.admin-users.form', ['admin' => $admin, 'roles' => $this->staffRoles()]);
    }

    public function update(Request $request, User $admin)
    {
        abort_if($admin->role === 'customer', 404);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:super_admin,product_manager,order_manager,support,delivery',
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'is_blocked' => 'nullable|boolean',
        ]);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $data['is_blocked'] = $request->boolean('is_blocked');
        if ($admin->id === Auth::guard('admin')->id() && $data['role'] !== 'super_admin') {
            return back()->withErrors(['role' => 'You cannot demote your own super admin account.']);
        }
        $admin->update($data);
        \App\Support\AdminAllowlist::add($admin->email);
        ActivityLogger::log('admin.update', $admin->email, 'Role: ' . $admin->role);
        return redirect()->route('admin.admin-users.index')->with('success', 'Admin account updated.');
    }

    public function destroy(User $admin)
    {
        abort_if($admin->role === 'customer', 404);
        if ($admin->id === Auth::guard('admin')->id()) {
            return back()->withErrors(['admin' => 'You cannot delete your own account.']);
        }
        \App\Support\AdminAllowlist::remove($admin->email);
        ActivityLogger::log('admin.delete', $admin->email, 'Admin account deleted');
        $admin->delete();
        return back()->with('success', 'Admin account deleted.');
    }

    private function staffRoles(): array
    {
        return collect(User::ROLES)->except('customer')->all();
    }
}
