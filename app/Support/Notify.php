<?php

namespace App\Support;

use App\Models\AdminNotification;

class Notify
{
    public static function admin(string $type, string $title, ?string $body = null, ?string $link = null): void
    {
        if (! Settings::get('notify_' . $type, '1')) {
            return;
        }
        AdminNotification::create(['type' => $type, 'title' => $title, 'body' => $body, 'link' => $link]);
    }
}
