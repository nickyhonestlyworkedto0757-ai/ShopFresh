<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProductVariation extends Model { protected $fillable = ['product_id', 'type', 'value', 'price_delta', 'stock', 'sku']; protected function casts(): array { return ['price_delta' => 'decimal:2', 'stock' => 'integer']; } public function product(): BelongsTo { return $this->belongsTo(Product::class); } }
