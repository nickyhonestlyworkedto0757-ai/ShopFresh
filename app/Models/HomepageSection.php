<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    protected $fillable = ['key', 'title', 'subtitle', 'mode', 'value', 'is_active', 'sort_order', 'max_items'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer', 'max_items' => 'integer'];
    }

    public function productsQuery()
    {
        return match ($this->mode) {
            'flag' => Product::active()->with('category')->orderByDesc('created_at')->tap(function ($q) {
                match ($this->value) {
                    'flash_sale' => $q->where('stock', '>', 0)->whereNotNull('flash_sale_ends_at')->where('flash_sale_ends_at', '>', now()),
                    'best_seller' => $q->where('is_best_seller', true),
                    'sponsored' => $q->where('is_sponsored', true),
                    'official_store' => $q->where('is_official_store', true),
                    'featured' => $q->where('is_featured', true),
                    default => $q, // recent
                };
            }),
            'category' => Product::active()->with('category')->whereHas('category', fn ($w) => $w->where('slug', $this->value))->orderByDesc('created_at'),
            default => Product::active()->with('category')->orderByDesc('created_at'),
        };
    }

    public function products()
    {
        return $this->productsQuery()->withCount('reviews')->limit($this->max_items)->get();
    }

    public function sectionUrl(): string
    {
        return match ($this->mode) {
            'category' => route('catalog', ['category' => $this->value]),
            'flag' => match ($this->value) {
                'best_seller' => route('catalog', ['flag' => 'best_seller']),
                'sponsored' => route('catalog', ['flag' => 'sponsored']),
                'official_store' => route('catalog', ['flag' => 'official_store']),
                'featured' => route('catalog', ['flag' => 'featured']),
                'flash_sale' => route('catalog', ['flag' => 'flash_sale']),
                default => route('catalog'),
            },
            default => route('catalog'),
        };
    }
}