<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = ['key', 'name', 'channel', 'subject', 'body', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public static function render(string $body, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $body = str_replace('{{' . $key . '}}', (string) $value, $body);
        }
        return $body;
    }
}