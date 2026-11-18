<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('admin.profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($request->filled('current_password') || $request->filled('password')) {
            $request->validate([
                'current_password' => 'required|string',
                'password' => ['required', 'confirmed', Password::min(8)],
            ]);
            if (! Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => 'Current password does not match.'])->withInput();
            }
            $validated['password'] = $request->input('password');
        }

        $user->fill($validated);
        $user->save();

        ActivityLogger::log('profile.update', $user->email, 'Admin profile updated' . (isset($validated['password']) ? ' (password changed)' : ''));

        return back()->with('success', 'Profile updated.' . (isset($validated['password']) ? ' Keep your password safe!' : ''));
    }
}
