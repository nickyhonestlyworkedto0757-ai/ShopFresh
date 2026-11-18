<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DeliveryZone extends Model { protected $fillable = ['name', 'fee', 'is_active']; protected function casts(): array { return ['is_active' => 'boolean', 'fee' => 'decimal:2']; } }
