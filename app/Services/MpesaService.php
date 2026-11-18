<?php

namespace App\Services;

use App\Support\Settings;
use Illuminate\Support\Facades\Http;

/**
 * M-Pesa Daraja (Lipa Na M-Pesa Online / STK Push) integration.
 * Docs: https://developer.safaricom.co.ke/
 *
 * Configured from Admin → Settings (stored in the settings table).
 * Environment variable fallbacks (MPESA_* in .env) are still honoured for deployments
 * that prefer env-based secrets.
 */
class MpesaService
{
    private function cfg(string $key, mixed $fallback = null): mixed
    {
        $value = Settings::get($key);

        if ($value === null || $value === '') {
            return match ($key) {
                'mpesa_env' => config('services.daraja.env', 'sandbox'),
                'mpesa_consumer_key' => config('services.daraja.key'),
                'mpesa_consumer_secret' => config('services.daraja.secret'),
                'mpesa_shortcode' => config('services.daraja.shortcode'),
                'mpesa_passkey' => config('services.daraja.passkey'),
                'mpesa_callback_url' => config('services.daraja.callback_url'),
                default => $fallback,
            };
        }

        return $value;
    }

    public function isProduction(): bool
    {
        return $this->cfg('mpesa_env') === 'production';
    }

    private function baseUrl(): string
    {
        return $this->isProduction()
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    public function isConfigured(): bool
    {
        return (bool) ($this->cfg('mpesa_consumer_key') && $this->cfg('mpesa_consumer_secret') && $this->cfg('mpesa_shortcode') && $this->cfg('mpesa_passkey'));
    }

    private function accessToken(): ?string
    {
        $response = Http::withBasicAuth($this->cfg('mpesa_consumer_key'), $this->cfg('mpesa_consumer_secret'))
            ->timeout(30)
            ->get($this->baseUrl() . '/oauth/v1/generate?grant_type=client_credentials');

        return $response->successful() ? $response->json('access_token') : null;
    }

    public static function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '254')) {
            $digits = $digits;
        } elseif (str_starts_with($digits, '0')) {
            $digits = '254' . substr($digits, 1);
        } elseif (str_starts_with($digits, '7') || str_starts_with($digits, '1')) {
            $digits = '254' . $digits;
        }

        return preg_match('/^254[17]\d{8}$/', $digits) ? $digits : null;
    }

    /** @return array{0: bool, 1: array} */
    public function stkPush(string $phone, float $amount, string $accountRef, string $description): array
    {
        if (! $this->isConfigured()) {
            return [false, ['message' => 'M-Pesa is not configured. Go to Admin → Settings → M-Pesa and enter your Daraja credentials.']];
        }
        $normalized = self::normalizePhone($phone);
        if (! $normalized) {
            return [false, ['message' => 'Invalid M-Pesa phone number. Use format 07XXXXXXXX, 01XXXXXXXX or 254XXXXXXXXX.']];
        }
        $token = $this->accessToken();
        if (! $token) {
            return [false, ['message' => 'Could not authenticate with Safaricom Daraja. Check your consumer key and secret.']];
        }

        $timestamp = now()->format('YmdHis');
        $shortcode = $this->cfg('mpesa_shortcode');
        $password = base64_encode($shortcode . $this->cfg('mpesa_passkey') . $timestamp);

        $response = Http::withToken($token)->timeout(30)->post($this->baseUrl() . '/mpesa/stkpush/v1/processrequest', [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) ceil($amount),
            'PartyA' => $normalized,
            'PartyB' => $shortcode,
            'PhoneNumber' => $normalized,
            'CallBackURL' => $this->cfg('mpesa_callback_url'),
            'AccountReference' => $accountRef,
            'TransactionDesc' => $description,
        ]);

        $body = $response->json() ?? ['message' => 'Empty response from Daraja.'];

        if (! $response->successful()) {
            return [false, ['message' => self::darajaError($response->status(), $body)]];
        }
        if (isset($body['ResponseCode']) && (string) $body['ResponseCode'] !== '0') {
            return [false, array_merge($body, ['message' => $body['ResponseDescription'] ?? 'STK push rejected.'])];
        }

        return [true, array_merge($body, ['message' => 'STK push sent. Check your phone and enter your M-Pesa PIN.'])];
    }

    /** @return array{0: bool, 1: array} */
    public function statusQuery(string $checkoutRequestId): array
    {
        if (! $this->isConfigured()) {
            return [false, ['message' => 'M-Pesa is not configured.']];
        }
        $token = $this->accessToken();
        if (! $token) {
            return [false, ['message' => 'Could not authenticate with Daraja.']];
        }

        $timestamp = now()->format('YmdHis');
        $shortcode = $this->cfg('mpesa_shortcode');
        $password = base64_encode($shortcode . $this->cfg('mpesa_passkey') . $timestamp);

        $response = Http::withToken($token)->timeout(30)->post($this->baseUrl() . '/mpesa/stkpushquery/v1/query', [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId,
        ]);

        return [$response->successful(), $response->json() ?? ['message' => 'Empty response from Daraja.']];
    }

    private static function darajaError(int $status, array $body): string
    {
        $description = $body['errorMessage'] ?? $body['ResponseDescription'] ?? $body['message'] ?? 'Unknown Daraja error';
        return "Daraja error (HTTP {$status}): {$description}";
    }
}
