<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'type', 'value', 'min_purchase', 'usage_limit', 'used_count', 'is_active', 'expires_at'])]
class Coupon extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'expires_at' => 'datetime', 'value' => 'decimal:2', 'min_purchase' => 'decimal:2'];
    }

    public function isValid(float $subtotal = 0): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }
        if ($subtotal < (float) $this->min_purchase) {
            return false;
        }
        return true;
    }

    public function discountFor(float $subtotal): float
    {
        return $this->type === 'percent'
            ? round($subtotal * ((float) $this->value / 100), 2)
            : min((float) $this->value, $subtotal);
    }
}
