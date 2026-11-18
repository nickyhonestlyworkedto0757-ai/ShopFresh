<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use App\Models\NotificationLog;
use App\Models\User;
use App\Support\Notifier;
use App\Support\PhoneVerificationService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function orders(Request $request)
    {
        $orders = $request->user()->orders()->latest()->paginate(10);
        return view('account.orders', compact('orders'));
    }

    public function order(Request $request, int $order)
    {
        $order = $request->user()->orders()->with('items')->findOrFail($order);
        return view('account.order', compact('order'));
    }

    public function preferences(Request $request)
    {
        $prefs = NotificationPreference::for($request->user());
        $phoneVerified = $request->user()->phoneVerifications()->whereNotNull('verified_at')->exists();
        $logs = NotificationLog::where('user_id', $request->user()->id)->latest()->take(10)->get();
        return view('account.preferences', compact('prefs', 'phoneVerified', 'logs'));
    }

    public function updatePreferences(Request $request)
    {
        $data = $request->validate([
            'email_order_updates' => 'nullable|boolean',
            'sms_order_updates' => 'nullable|boolean',
            'email_new_products' => 'nullable|boolean',
            'sms_new_products' => 'nullable|boolean',
            'email_promotions' => 'nullable|boolean',
            'sms_promotions' => 'nullable|boolean',
        ]);
        $prefs = NotificationPreference::for($request->user());
        $prefs->fill([
            'email_order_updates' => $request->boolean('email_order_updates'),
            'sms_order_updates' => $request->boolean('sms_order_updates'),
            'email_new_products' => $request->boolean('email_new_products'),
            'sms_new_products' => $request->boolean('sms_new_products'),
            'email_promotions' => $request->boolean('email_promotions'),
            'sms_promotions' => $request->boolean('sms_promotions'),
        ]);
        $prefs->save();
        return back()->with('success', 'Notification preferences saved.');
    }

    public function sendPhoneCode(Request $request)
    {
        $validated = $request->validate(['phone' => 'nullable|string|max:20']);
        [$ok, $message] = PhoneVerificationService::sendCode($request->user(), $validated['phone'] ?? $request->user()->phone);
        return back()->with($ok ? 'success' : 'error', $message);
    }

    public function verifyPhone(Request $request)
    {
        $validated = $request->validate(['code' => 'required|string|size:6']);
        $ok = PhoneVerificationService::verify($request->user(), $validated['code']);
        return back()->with($ok ? 'success' : 'error', $ok ? 'Phone number verified.' : 'Invalid or expired code.');
    }
}
