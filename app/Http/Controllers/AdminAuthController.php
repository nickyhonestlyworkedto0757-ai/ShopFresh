<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $staff = Staff::where('email', $credentials['email'])->first();

        if (! $staff || ! Hash::check($credentials['password'], $staff->password)) {
            return back()->withErrors(['email' => 'Invalid credentials or you are not an admin account.'])->onlyInput('email');
        }

        if ($staff->is_blocked) {
            return back()->withErrors(['email' => 'This account is suspended.'])->onlyInput('email');
        }

        $approved = array_filter(array_map('trim', array_map('strtolower', explode(',', (string) \App\Support\Settings::get('admin_approved_emails', '')))));
        if (! in_array(strtolower($staff->email), $approved, true)) {
            return back()->withErrors(['email' => 'This account is not authorised for the admin portal. Contact the super admin.'])->onlyInput('email');
        }

        Auth::guard('admin')->login($staff, $request->boolean('remember'));
        $request->session()->regenerate();
        ActivityLogger::log('admin.login', 'auth', $staff->email);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        ActivityLogger::log('admin.logout', 'auth', Auth::guard('admin')->user()?->email);
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
