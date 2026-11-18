<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CookieConsent;
use App\Support\ActivityLogger;
use App\Support\Settings;
use Illuminate\Http\Request;

class CookieController extends Controller
{
    private const CATEGORIES = [
        'essential' => ['label' => 'Essential cookies', 'desc_key' => 'cookie_essential_desc', 'default' => 'Required for login, shopping cart, checkout and website security. Cannot be switched off.'],
        'functional' => ['label' => 'Functional cookies', 'desc_key' => 'cookie_functional_desc', 'default' => 'Remember customer preferences such as language, currency and recently viewed products.'],
        'analytics' => ['label' => 'Analytics cookies', 'desc_key' => 'cookie_analytics_desc', 'default' => 'Help us understand how customers use the store so we can improve it.'],
        'marketing' => ['label' => 'Marketing cookies', 'desc_key' => 'cookie_marketing_desc', 'default' => 'Used for relevant promotional offers and advertising. We never sell personal data.'],
    ];

    public function index()
    {
        $total = CookieConsent::count();
        $acceptAll = CookieConsent::where('functional', true)->where('analytics', true)->where('marketing', true)->count();
        $functional = CookieConsent::where('functional', true)->count();
        $analytics = CookieConsent::where('analytics', true)->count();
        $marketing = CookieConsent::where('marketing', true)->count();
        $recent = CookieConsent::latest()->take(10)->get();
        $lifetime = (int) Settings::get('cookie_lifetime', 365);
        $descriptions = [];
        foreach (self::CATEGORIES as $key => $cat) {
            $descriptions[$key] = Settings::get($cat['desc_key']) ?: $cat['default'];
        }
        return view('admin.cookies.index', compact('total', 'acceptAll', 'functional', 'analytics', 'marketing', 'recent', 'lifetime', 'descriptions'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'cookie_essential_desc' => 'nullable|string|max:500',
            'cookie_functional_desc' => 'nullable|string|max:500',
            'cookie_analytics_desc' => 'nullable|string|max:500',
            'cookie_marketing_desc' => 'nullable|string|max:500',
            'cookie_lifetime' => 'required|integer|min:1|max:3650',
        ]);
        foreach ($validated as $key => $value) {
            Settings::set($key, $value ?? '');
        }
        ActivityLogger::log('cookies.update', 'settings', 'Cookie consent settings updated');
        return back()->with('success', 'Cookie settings saved.');
    }
}
