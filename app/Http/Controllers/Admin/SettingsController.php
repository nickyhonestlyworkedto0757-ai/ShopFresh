<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = Settings::all();
        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'store_name' => 'required|string|max:255',
            'store_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'favicon' => 'nullable|file|mimes:jpg,jpeg,png,ico,webp,svg|max:4096',
            'store_email' => 'nullable|email|max:255',
            'store_phone' => 'nullable|string|max:50',
            'store_address' => 'nullable|string|max:500',
            'currency' => 'required|string|max:10',
            'delivery_fee' => 'required|numeric|min:0',
            'social_facebook' => 'nullable|string|max:500',
            'social_instagram' => 'nullable|string|max:500',
            'social_twitter' => 'nullable|string|max:500',
            'social_tiktok' => 'nullable|string|max:500',
            'notify_new_order' => 'nullable|boolean',
            'notify_new_customer' => 'nullable|boolean',
            'notify_low_stock' => 'nullable|boolean',
            'notify_payment' => 'nullable|boolean',
            'promo_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'promo_link' => 'nullable|string|max:500',
            'mpesa_env' => 'required|in:sandbox,production',
            'mpesa_consumer_key' => 'nullable|string|max:255',
            'mpesa_consumer_secret' => 'nullable|string|max:255',
            'mpesa_shortcode' => 'nullable|string|max:20',
            'mpesa_passkey' => 'nullable|string|max:500',
            'mpesa_callback_url' => 'nullable|url|max:500',
            'admin_approved_emails' => 'nullable|string',
        ]);
        $data['admin_approved_emails'] = implode(', ', array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string) $data['admin_approved_emails']))));

        if ($request->hasFile('store_logo')) {
            $old = Settings::get('store_logo');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            $data['store_logo'] = $request->file('store_logo')->store('store', 'public');
        } else {
            unset($data['store_logo']);
        }

        if ($request->hasFile('favicon')) {
            $oldFavicon = Settings::get('favicon');
            if ($oldFavicon) {
                Storage::disk('public')->delete($oldFavicon);
            }
            $data['favicon'] = $request->file('favicon')->store('store', 'public');
        } else {
            unset($data['favicon']);
        }

        if ($request->boolean('remove_favicon')) {
            $oldFavicon = Settings::get('favicon');
            if ($oldFavicon) {
                Storage::disk('public')->delete($oldFavicon);
            }
            $data['favicon'] = '';
        }

        if ($request->hasFile('promo_image')) {
            $oldPromo = Settings::get('promo_image');
            if ($oldPromo) {
                Storage::disk('public')->delete($oldPromo);
            }
            $data['promo_image'] = $request->file('promo_image')->store('promo', 'public');
        } else {
            unset($data['promo_image']);
        }

        // M-Pesa secrets: blank = keep existing
        foreach (['mpesa_consumer_secret', 'mpesa_passkey'] as $secret) {
            if (empty($data[$secret])) {
                unset($data[$secret]);
            }
        }
        foreach (['mpesa_consumer_key', 'mpesa_shortcode', 'mpesa_callback_url'] as $keepIfEmpty) {
            if (! array_key_exists($keepIfEmpty, $data) || $data[$keepIfEmpty] === null) {
                $data[$keepIfEmpty] = '';
            }
        }

        if ($request->boolean('remove_promo_image')) {
            $oldPromo = Settings::get('promo_image');
            if ($oldPromo) {
                Storage::disk('public')->delete($oldPromo);
            }
            $data['promo_image'] = '';
        }

        foreach (['notify_new_order', 'notify_new_customer', 'notify_low_stock', 'notify_payment'] as $toggle) {
            $data[$toggle] = $request->boolean($toggle) ? '1' : '0';
        }

        foreach ($data as $key => $value) {
            Settings::set($key, $value);
        }

        ActivityLogger::log('settings.update', 'settings', 'Store settings updated');
        return back()->with('success', 'Settings saved.');
    }

    /** Send a small test STK push (1 unit) to the provided phone using current M-Pesa config. */
    public function testMpesa(Request $request)
    {
        $validated = $request->validate(['phone' => 'required|string|max:20', 'amount' => 'required|numeric|min:1']);
        [$ok, $response] = app(\App\Services\MpesaService::class)->stkPush(
            $validated['phone'],
            (float) $validated['amount'],
            'TEST-' . strtoupper(substr(md5(now()), 0, 6)),
            'ShopFresh test'
        );
        ActivityLogger::log('mpesa.test', 'settings', ($ok ? 'Test STK push sent' : 'Test failed') . ' to ' . $validated['phone']);
        return back()->with($ok ? 'success' : 'error', ($ok ? 'STK push sent! Check your phone. ' : 'Test failed: ') . ($response['message'] ?? ''));
    }
}
