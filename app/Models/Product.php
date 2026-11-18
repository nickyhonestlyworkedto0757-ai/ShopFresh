<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category_id', 'name', 'slug', 'sku', 'barcode', 'description', 'specifications', 'price', 'cost_price', 'sale_price', 'stock', 'low_stock_threshold', 'image', 'is_featured', 'is_active', 'is_best_seller', 'is_sponsored', 'is_official_store', 'flash_sale_ends_at'])]
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'is_best_seller' => 'boolean',
            'is_sponsored' => 'boolean',
            'is_official_store' => 'boolean',
            'flash_sale_ends_at' => 'datetime',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock' => 'integer',
            'low_stock_threshold' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->low_stock_threshold;
    }

    public function effectivePrice(): float
    {
        return (float) ($this->sale_price ?? $this->price);
    }

    public function averageRating(): float
    {
        return round((float) $this->reviews()->avg('rating'), 1);
    }

    public function imageUrl(): ?string
    {
        if ($this->image) {
            return str_starts_with($this->image, 'http') ? $this->image : asset('storage/' . $this->image);
        }
        $first = $this->images()->first();
        return $first?->url();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Total units sold (from order items). */
    public function discountPercent(): ?int
    {
        if (! $this->sale_price || (float) $this->price <= 0) {
            return null;
        }
        return (int) round((1 - (float) $this->sale_price / (float) $this->price) * 100);
    }

    public function unitsSold(): int
    {
        return (int) $this->hasMany(OrderItem::class)->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))->sum('quantity');
    }
}
