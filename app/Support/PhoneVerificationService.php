<?php

namespace App\Support;

use App\Models\PhoneVerification;
use App\Models\User;
use App\Services\SmsService;

class PhoneVerificationService
{
    public const PURPOSE_REGISTER = 'register';
    public const PURPOSE_UPDATE = 'update';

    /** Send a 6-digit code. Returns the code in development only for convenience. */
    public static function sendCode(User $user, ?string $phone = null): array
    {
        $phone ??= $user->phone;
        $normalized = SmsService::normalizePhone($phone);
        if (! $normalized) {
            return [false, 'Phone number is not a valid Kenyan mobile number.'];
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        PhoneVerification::create([
            'user_id' => $user->id,
            'phone' => $normalized,
            'code' => hash('sha256', $code),
            'expires_at' => now()->addMinutes(10),
        ]);

        $sent = Notifier::sms($user, 'otp', 'Your ' . \App\Support\Settings::get('store_name') . ' verification code is ' . $code . '. It expires in 10 minutes.', null);

        // Never expose the code outside development/failure fallbacks
        if (! $sent && app()->environment('local')) {
            return [true, 'SMS not configured — for testing, your code is ' . $code];
        }

        return [true, $sent ? 'Code sent to ' . $normalized : 'SMS not configured.'];
    }

    public static function verify(User $user, string $code): bool
    {
        $record = PhoneVerification::where('user_id', $user->id)
            ->where('verified_at', null)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $record || ! hash_equals($record->code, hash('sha256', $code))) {
            return false;
        }

        $record->update(['verified_at' => now()]);
        return true;
    }
}
