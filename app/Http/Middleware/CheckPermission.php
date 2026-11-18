<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = Auth::guard('admin')->user();
        if (! $user || ! $user->hasPermission($permission)) {
            abort(403, 'You do not have access to this section.');
        }
        return $next($request);
    }
}
