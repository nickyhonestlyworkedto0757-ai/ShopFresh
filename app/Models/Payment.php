<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'method', 'phone', 'amount', 'status',
        'transaction_id', 'checkout_request_id', 'merchant_request_id',
        'receipt_number', 'result_code', 'result_desc',
        'provider', 'raw_response', 'callback_payload', 'callback_received_at', 'verified_at',
    ];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime', 'callback_received_at' => 'datetime', 'amount' => 'decimal:2'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isMpesa(): bool
    {
        return $this->method === 'mpesa';
    }
}
