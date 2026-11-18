<?php

namespace App\Support;

class AdminAllowlist
{
    public static function emails(): array
    {
        return array_values(array_filter(array_map('trim', array_map('strtolower', explode(',', (string) Settings::get('admin_approved_emails', ''))))));
    }

    public static function add(?string $email): void
    {
        if (empty($email)) { return; }
        $emails = self::emails();
        $email = strtolower(trim($email));
        if (! in_array($email, $emails, true)) {
            $emails[] = $email;
            Settings::set('admin_approved_emails', implode(', ', $emails));
        }
    }

    public static function remove(?string $email): void
    {
        if (empty($email)) { return; }
        $email = strtolower(trim($email));
        $emails = array_values(array_filter(self::emails(), fn ($e) => $e !== $email));
        Settings::set('admin_approved_emails', implode(', ', $emails));
    }
}
