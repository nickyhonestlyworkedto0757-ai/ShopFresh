<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AdminNotification extends Model { protected $fillable = ['type', 'title', 'body', 'link', 'read_at']; protected function casts(): array { return ['read_at' => 'datetime']; } }
