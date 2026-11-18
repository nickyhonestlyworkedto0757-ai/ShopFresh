<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(string $action, ?string $subject = null, ?string $description = null, ?int $userId = null): void
    {
        $resolved = $userId;
        if ($resolved === null) {
            $resolved = Auth::guard('admin')->check() ? Auth::guard('admin')->id() : Auth::id();
        }

        ActivityLog::create([
            'user_id' => $resolved,
            'action' => $action,
            'subject' => $subject,
            'description' => $description,
            'ip' => request()->ip(),
        ]);
    }
}
