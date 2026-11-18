<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['parent_id', 'name', 'slug', 'description', 'icon', 'image', 'sort_order'])]
class Category extends Model
{
    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }
        return str_starts_with($this->image, 'http') ? $this->image : asset('storage/' . $this->image);
    }
}
