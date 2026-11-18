<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAdminStaff
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('admin')->user();
        if (! $user) {
            return redirect()->guest(route('admin.login'));
        }
        if (! $user->isStaff()) {
            Auth::guard('admin')->logout();
            return redirect()->route('home')->with('error', 'This area is restricted to store staff.');
        }
        if ($user->is_blocked) {
            return redirect()->route('home')->with('error', 'Your staff account is suspended.');
        }
        $approved = array_filter(array_map('trim', array_map('strtolower', explode(',', (string) \App\Support\Settings::get('admin_approved_emails', '')))));
        if (! in_array(strtolower($user->email), $approved, true)) {
            return redirect()->route('home')->with('error', 'Your account is not in the approved admin list. Contact the super admin.');
        }
        return $next($request);
    }
}
