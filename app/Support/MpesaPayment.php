<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Payment;
use App\Services\MpesaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MpesaPayment
{
    /**
     * Initiate an STK push for an order. Safe to call repeatedly for retries —
     * a new STK push simply supersedes the previous pending one on the order.
     *
     * @return array{0: bool, 1: string, 2: ?Payment}
     */
    public static function initiate(Order $order, string $phone): array
    {
        if ($order->payment_status === 'paid') {
            return [false, 'This order is already paid.', $order->payment];
        }
        $normalized = MpesaService::normalizePhone($phone);
        if (! $normalized) {
            return [false, 'Invalid M-Pesa phone number. Use 07XXXXXXXX, 01XXXXXXXX or 254XXXXXXXXX.', null];
        }

        $payment = $order->payment;
        if (! $payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => 'mpesa',
                'amount' => $order->total,
                'status' => 'pending',
                'provider' => 'daraja',
            ]);
        }

        [$ok, $response] = app(MpesaService::class)->stkPush($normalized, (float) $order->total, $order->order_number, 'ShopFresh ' . $order->order_number);

        if (! $ok) {
            $payment->update(['phone' => $normalized, 'raw_response' => json_encode($response)]);
            return [false, $response['message'] ?? 'STK push failed. Please try again.', $payment];
        }

        $payment->update([
            'phone' => $normalized,
            'status' => 'pending',
            'result_code' => null,
            'result_desc' => null,
            'checkout_request_id' => $response['CheckoutRequestID'] ?? null,
            'merchant_request_id' => $response['MerchantRequestID'] ?? null,
            'transaction_id' => $response['CheckoutRequestID'] ?? null,
            'raw_response' => json_encode($response),
        ]);

        return [true, 'STK push sent to ' . $normalized . '. Enter your M-Pesa PIN to complete payment.', $payment];
    }

    /**
     * Process a Daraja STK callback. Idempotent and amount-verified.
     * Always returns a response Daraja expects.
     *
     * @return array{ResultCode: int, ResultDesc: string}
     */
    public static function handleCallback(array $payload): array
    {
        $callback = $payload['Body']['stkCallback'] ?? null;
        if (! $callback) {
            Log::warning('M-Pesa callback with missing stkCallback', ['payload' => $payload]);
            return ['ResultCode' => 1, 'ResultDesc' => 'Invalid payload'];
        }

        $checkoutRequestId = $callback['CheckoutRequestID'] ?? null;
        $merchantRequestId = $callback['MerchantRequestID'] ?? null;
        $resultCode = (int) ($callback['ResultCode'] ?? -1);
        $resultDesc = (string) ($callback['ResultDesc'] ?? 'Unknown');

        $payment = $checkoutRequestId
            ? Payment::where('checkout_request_id', $checkoutRequestId)->first()
            : ($merchantRequestId ? Payment::where('merchant_request_id', $merchantRequestId)->first() : null);

        if (! $payment) {
            Log::warning('M-Pesa callback for unknown transaction', ['checkout_request_id' => $checkoutRequestId, 'merchant_request_id' => $merchantRequestId]);
            // Still acknowledge so Daraja does not keep retrying.
            return ['ResultCode' => 0, 'ResultDesc' => 'Accepted (transaction not found)'];
        }

        // Idempotency: a confirmed payment never gets downgraded or double-notified.
        if ($payment->status === 'paid') {
            $payment->update(['callback_payload' => json_encode($payload), 'callback_received_at' => now()]);
            return ['ResultCode' => 0, 'ResultDesc' => 'Accepted (already processed)'];
        }

        $metadata = collect($callback['CallbackMetadata']['Item'] ?? [])
            ->mapWithKeys(fn ($item) => [$item['Name'] ?? 'unknown' => $item['Value'] ?? null]);

        $success = $resultCode === 0;
        $amount = (float) ($metadata['Amount'] ?? 0);

        // Verify the paid amount matches the expected order total exactly.
        if ($success && abs($amount - (float) $payment->amount) >= 1) {
            $success = false;
            $resultDesc = 'Amount mismatch: expected ' . $payment->amount . ', got ' . $amount;
            Log::error('M-Pesa amount mismatch — order NOT marked paid', ['payment_id' => $payment->id, 'expected' => $payment->amount, 'received' => $amount]);
        }

        DB::transaction(function () use ($payment, $success, $payload, $metadata, $resultCode, $resultDesc) {
            $order = $payment->order;
            $payment->update([
                'status' => $success ? 'paid' : ($resultCode === 1032 ? 'cancelled' : ($resultCode === 1037 ? 'timeout' : 'failed')),
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
                'receipt_number' => $metadata['MpesaReceiptNumber'] ?? null,
                'phone' => $metadata['PhoneNumber'] ?? $payment->phone,
                'callback_payload' => json_encode($payload),
                'callback_received_at' => now(),
                'raw_response' => json_encode($payload),
                'verified_at' => $success ? now() : null,
            ]);

            if ($order) {
                $order->update(['payment_status' => $success ? 'paid' : 'failed']);
            }

            if ($success && $order) {
                Notify::admin('payment', 'M-Pesa payment received', 'Order ' . $order->order_number . ' paid ' . number_format($amountSafe = (float) $metadata['Amount'], 2) . ' (receipt ' . ($metadata['MpesaReceiptNumber'] ?? '—') . ')', '/admin/payments');
                ActivityLogger::log('payment.mpesa', $order->order_number, 'Paid via M-Pesa, receipt ' . ($metadata['MpesaReceiptNumber'] ?? '—'));
                \App\Support\Notifier::orderUpdate($order, 'paid');
            } elseif (! $success && $order) {
                \App\Support\Notifier::orderUpdate($order, 'failed');
            }
        });

        return ['ResultCode' => 0, 'ResultDesc' => 'Accepted'];
    }
}
