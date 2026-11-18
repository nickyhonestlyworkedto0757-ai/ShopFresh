<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CookieConsent extends Model
{
    protected $fillable = ['user_id', 'session_id', 'ip', 'essential', 'functional', 'analytics', 'marketing', 'consented_at'];

    protected function casts(): array
    {
        return ['essential' => 'boolean', 'functional' => 'boolean', 'analytics' => 'boolean', 'marketing' => 'boolean', 'consented_at' => 'datetime'];
    }
}