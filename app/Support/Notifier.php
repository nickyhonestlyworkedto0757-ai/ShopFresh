<?php

namespace App\Support;

use App\Models\NotificationLog;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Notifier
{
    /**
     * Send an email (with dedupe + logging).
     *
     * @return bool true when sent (or queued), false when skipped/failed
     */
    public static function email(?User $user, string $type, string $subject, string $body, ?string $uniqueKey = null): bool
    {
        $recipient = $user?->email;
        if (! $recipient) {
            return false;
        }
        return self::deliver('email', $type, $recipient, $subject, $body, $user?->id, $uniqueKey, function () use ($recipient, $subject, $body) {
            Mail::raw($body, fn ($mail) => $mail->to($recipient)->subject($subject));
            return [true, null, 0.0];
        });
    }

    /** Send an SMS (with dedupe + logging). */
    public static function sms(?User $user, string $type, string $message, ?string $uniqueKey = null): bool
    {
        $recipient = $user?->phone;
        if (! $recipient) {
            return false;
        }
        return self::deliver('sms', $type, $recipient, null, $message, $user?->id, $uniqueKey, function () use ($recipient, $message) {
            [$ok, $error] = app(SmsService::class)->send($recipient, $message);
            return [$ok, $error, $ok ? 0.5 : 0.0];
        });
    }

    private static function deliver(string $channel, string $type, string $recipient, ?string $subject, string $body, ?int $userId, ?string $uniqueKey, callable $sender): bool
    {
        if ($uniqueKey && NotificationLog::where('unique_key', $uniqueKey)->where('status', 'sent')->exists()) {
            Log::info('Notification skipped (already sent)', ['unique_key' => $uniqueKey, 'channel' => $channel]);
            return false;
        }

        [$ok, $error, $cost] = $sender();

        NotificationLog::create([
            'channel' => $channel,
            'type' => $type,
            'recipient' => $recipient,
            'subject' => $subject,
            'body' => $body,
            'unique_key' => $uniqueKey,
            'status' => $ok ? 'sent' : 'failed',
            'attempts' => 1,
            'cost' => $cost,
            'error' => $error,
            'user_id' => $userId,
        ]);

        return $ok;
    }

    /** Mark a previously failed log and resend it. */
    public static function retry(NotificationLog $log): bool
    {
        $user = $log->user_id ? User::find($log->user_id) : null;

        switch ($log->channel) {
            case 'email':
                try {
                    Mail::raw($log->body, fn ($mail) => $mail->to($log->recipient)->subject($log->subject ?? config('app.name')));
                    $ok = true;
                    $error = null;
                } catch (\Throwable $e) {
                    $ok = false;
                    $error = $e->getMessage();
                }
                break;
            case 'sms':
                [$ok, $error] = app(SmsService::class)->send($log->recipient, $log->body);
                break;
            default:
                $ok = false;
                $error = 'Unknown channel.';
        }

        $log->update([
            'attempts' => $log->attempts + 1,
            'status' => $ok ? 'sent' : 'failed',
            'error' => $ok ? null : $error,
        ]);

        return $ok;
    }

    /** Convenience: send order event notification to customer (respects preferences). */
    public static function orderUpdate(\App\Models\Order $order, string $event): void
    {
        $user = $order->user;
        $prefs = NotificationPreference::for($user);
        $orderLink = route('account.orders');

        $messages = [
            'placed' => ['subject' => 'Order received: ' . $order->order_number, 'body' => "Hi {$order->customer_name},\n\nWe have received your order {$order->order_number} (total: {$order->total}). We will confirm it shortly.\n\nTrack it here: {$orderLink}"],
            'paid' => ['subject' => 'Payment confirmed: ' . $order->order_number, 'body' => "Hi {$order->customer_name},\n\nYour payment for order {$order->order_number} has been confirmed.\n\nTrack it here: {$orderLink}"],
            'failed' => ['subject' => 'Payment failed: ' . $order->order_number, 'body' => "Hi {$order->customer_name},\n\nUnfortunately we could not confirm payment for order {$order->order_number}. Please retry from your orders page.\n\n{$orderLink}"],
            'confirmed' => ['subject' => 'Order confirmed: ' . $order->order_number, 'body' => "Hi {$order->customer_name},\n\nYour order {$order->order_number} has been confirmed and is being prepared.\n\n{$orderLink}"],
            'processing' => ['subject' => 'Your order is being processed: ' . $order->order_number, 'body' => "Hi {$order->customer_name},\n\nYour order {$order->order_number} is being processed and packed.\n\n{$orderLink}"],
            'shipped' => ['subject' => 'Order shipped: ' . $order->order_number, 'body' => "Hi {$order->customer_name},\n\nYour order {$order->order_number} has been shipped and is on its way to you.\n\n{$orderLink}"],
            'out_for_delivery' => ['subject' => 'Out for delivery: ' . $order->order_number, 'body' => "Hi {$order->customer_name},\n\nYour order {$order->order_number} is out for delivery today. Please be available to receive it."],
            'delivered' => ['subject' => 'Delivered: ' . $order->order_number, 'body' => "Hi {$order->customer_name},\n\nYour order {$order->order_number} has been delivered. Thank you for shopping with us!"],
            'cancelled' => ['subject' => 'Order cancelled: ' . $order->order_number, 'body' => "Hi {$order->customer_name},\n\nYour order {$order->order_number} has been cancelled. If you were already charged, a refund will be processed within 3 business days."],
        ];

        $msg = $messages[$event] ?? null;
        if (! $msg) {
            return;
        }

        $key = 'order:' . $order->order_number . ':' . $event;

        // Order updates are transactional: respect order-update preference (default on)
        if ($user) {
            if ($prefs->email_order_updates) {
                self::email($user, 'order_' . $event, $msg['subject'], $msg['body'], $key . ':email');
            }
            if ($prefs->sms_order_updates) {
                self::sms($user, 'order_' . $event, $msg['subject'] . ' — ' . $msg['body'], $key . ':sms');
            }
        } else {
            self::email(null, 'order_' . $event, $msg['subject'], $msg['body'], $key . ':email'); // logged as failed (no email) — harmless
        }

        if (! $user) {
            // Guest checkout: notify admin when a guest order updates
            Notify::admin('new_order', $msg['subject'], $msg['body'], '/admin/orders/' . $order->id);
        }
    }

    /** Notify subscribed customers about a new product. */
    public static function newProduct(\App\Models\Product $product): int
    {
        $sent = 0;
        $link = route('product', $product->slug);
        $price = number_format((float) ($product->sale_price ?? $product->price), 2);
        $currency = \App\Support\Settings::get('currency', 'KES');

        $users = User::where('role', 'customer')
            ->whereHas('notificationPreference', fn ($q) => $q->where('email_new_products', true)->orWhere('sms_new_products', true))
            ->with('notificationPreference')
            ->get();

        foreach ($users as $user) {
            $pref = NotificationPreference::for($user);
            $body = "Hi {$user->name},\n\nNew on our shelves: {$product->name} — {$price} {$currency}\n{$link}\n\nYou're receiving this because you opted in to new product announcements. Update your preferences in your account.";
            $key = 'product:' . $product->id . ':' . $user->id;
            if ($pref->email_new_products) {
                if (self::email($user, 'new_product', 'New: ' . $product->name, $body, $key . ':email')) {
                    $sent++;
                }
            }
            if ($pref->sms_new_products) {
                if (self::sms($user, 'new_product', "New on our shelves: {$product->name} — {$price}. {$link}", $key . ':sms')) {
                    $sent++;
                }
            }
        }

        return $sent;
    }
}
