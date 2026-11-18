<?php

namespace App\Http\Controllers;

use App\Models\CookieConsent;
use Illuminate\Http\Request;

class CookieConsentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'functional' => 'nullable|boolean',
            'analytics' => 'nullable|boolean',
            'marketing' => 'nullable|boolean',
        ]);

        CookieConsent::create([
            'user_id' => $request->user()?->id,
            'session_id' => $request->session()->getId(),
            'ip' => $request->ip(),
            'essential' => true,
            'functional' => $request->boolean('functional'),
            'analytics' => $request->boolean('analytics'),
            'marketing' => $request->boolean('marketing'),
            'consented_at' => now(),
        ]);

        $payload = json_encode([
            'essential' => true,
            'functional' => $request->boolean('functional'),
            'analytics' => $request->boolean('analytics'),
            'marketing' => $request->boolean('marketing'),
            'at' => now()->toIso8601String(),
        ]);

        cookie()->queue(cookie('cookie_consent', $payload, 60 * 24 * 365));

        return response()->json(['ok' => true]);
    }
}
