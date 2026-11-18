<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Africa's Talking SMS gateway (https://africastalking.com) — built for Kenya (Safaricom/Airtel).
 * Credentials live in .env ONLY: AT_USERNAME, AT_API_KEY, AT_SENDER (optional, e.g. "SHOPFREH").
 * When not configured, SMS is skipped and logged as "skipped" — nothing breaks.
 */
class SmsService
{
    public function isConfigured(): bool
    {
        return (bool) (config('services.africastalking.username') && config('services.africastalking.api_key'));
    }

    /** Normalize a Kenyan mobile number to 254XXXXXXXXX. */
    public static function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '0')) {
            $digits = '254' . substr($digits, 1);
        } elseif (! str_starts_with($digits, '254')) {
            $digits = '254' . $digits;
        }
        return preg_match('/^254[17]\d{8}$/', $digits) ? $digits : null;
    }

    /** @return array{0: bool, 1: string|null} */
    public function send(string $phone, string $message): array
    {
        $normalized = self::normalizePhone($phone);
        if (! $normalized) {
            return [false, 'Invalid phone number: ' . $phone];
        }
        if (! $this->isConfigured()) {
            return [false, 'SMS not configured. Set AT_USERNAME and AT_API_KEY in .env.'];
        }

        $payload = [
            'username' => config('services.africastalking.username'),
            'to' => $normalized,
            'message' => $message,
        ];
        if ($sender = config('services.africastalking.sender')) {
            $payload['from'] = $sender;
        }

        $response = Http::asForm()
            ->withHeaders(['apiKey' => config('services.africastalking.api_key'), 'Accept' => 'application/json'])
            ->timeout(20)
            ->post('https://api.africastalking.com/version1/messaging', $payload);

        if (! $response->successful()) {
            return [false, 'Gateway error: ' . $response->body()];
        }
        $body = $response->json();
        $recipients = data_get($body, 'SMSMessageData.Recipients', []);
        if (isset($recipients[0]['status']) && ! in_array($recipients[0]['status'], ['Success', 'Queued', 'Sent'])) {
            return [false, 'Recipient rejected: ' . ($recipients[0]['status'] ?? 'unknown')];
        }

        return [true, null];
    }
}
