<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Staff extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password', 'phone', 'is_admin', 'role', 'is_blocked', 'api_token'];

    protected $hidden = ['password', 'remember_token', 'api_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_blocked' => 'boolean',
        ];
    }

    /**
     * Staff model ONLY sees staff rows – customers are invisible to the admin provider.
     */
    public function newQuery(): Builder
    {
        return parent::newQuery()->where('role', '!=', 'customer');
    }

    public function isStaff(): bool
    {
        return $this->role !== 'customer';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        foreach (User::permissionMatrix()[$this->role] ?? [] as $granted) {
            if ($granted === $permission || $permission === $granted) {
                return true;
            }
            if (str_starts_with($permission, $granted . '.')) {
                return true;
            }
        }
        return false;
    }

    public function isBlocked(): bool
    {
        return (bool) $this->is_blocked;
    }
}
