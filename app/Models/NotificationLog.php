<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = ['channel', 'type', 'recipient', 'subject', 'body', 'unique_key', 'status', 'attempts', 'cost', 'error', 'user_id'];

    protected function casts(): array
    {
        return ['cost' => 'decimal:2'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}