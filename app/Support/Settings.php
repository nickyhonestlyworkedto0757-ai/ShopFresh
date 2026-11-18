<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class Settings
{
    private const DEFAULTS = [
        'store_name' => 'ShopFresh',
        'store_logo' => '',
        'favicon' => '',
        'store_email' => '',
        'store_phone' => '',
        'store_address' => '',
        'currency' => 'KES',
        'delivery_fee' => '0',
        'social_facebook' => '',
        'social_instagram' => '',
        'social_twitter' => '',
        'social_tiktok' => '',
        'promo_image' => '',
        'promo_link' => '',
        'notify_new_order' => '1',
        'notify_new_customer' => '1',
        'notify_low_stock' => '1',
        'notify_payment' => '1',
        'mpesa_env' => 'sandbox',
        'mpesa_consumer_key' => '',
        'mpesa_consumer_secret' => '',
        'mpesa_shortcode' => '',
        'mpesa_passkey' => '',
        'mpesa_callback_url' => '',
        'admin_approved_emails' => 'admin@shopfresh.test, 40947743n@gmail.com',
    ];

    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache === null) {
            self::$cache = array_merge(self::DEFAULTS, Setting::pluck('value', 'key')->toArray());
        }
        return self::$cache;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::all()[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        self::$cache = null;
        Cache::forget('settings');
    }
}
