<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('admin')->user();
        if (! $user || ! $user->isStaff()) {
            abort(403, 'Admins only.');
        }
        if ($user->is_blocked) {
            abort(403, 'Your account is blocked.');
        }
        $approved = array_filter(array_map('trim', array_map('strtolower', explode(',', (string) \App\Support\Settings::get('admin_approved_emails', '')))));
        if (! in_array(strtolower($user->email), $approved, true)) {
            abort(403, 'Your account is not in the approved admin list.');
        }
        return $next($request);
    }
}
