<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = ['user_id', 'email_order_updates', 'sms_order_updates', 'email_new_products', 'sms_new_products', 'email_promotions', 'sms_promotions'];

    protected function casts(): array
    {
        return ['email_order_updates' => 'boolean', 'sms_order_updates' => 'boolean', 'email_new_products' => 'boolean', 'sms_new_products' => 'boolean', 'email_promotions' => 'boolean', 'sms_promotions' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function for(?User $user): self
    {
        if (! $user) {
            return new self(['email_order_updates' => true, 'sms_order_updates' => false, 'email_new_products' => false, 'sms_new_products' => false, 'email_promotions' => false, 'sms_promotions' => false]);
        }
        return $user->notificationPreference()->firstOrCreate(['user_id' => $user->id]);
    }
}