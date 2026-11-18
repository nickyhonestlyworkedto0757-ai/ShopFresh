<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone', 'is_admin', 'role', 'is_blocked', 'api_token'])]
#[Hidden(['password', 'remember_token', 'api_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = [
        'super_admin' => 'Super Admin',
        'product_manager' => 'Product Manager',
        'order_manager' => 'Order Manager',
        'support' => 'Customer Support',
        'delivery' => 'Delivery Personnel',
        'customer' => 'Customer',
    ];

    public const STAFF_ROLES = ['super_admin', 'product_manager', 'order_manager', 'support', 'delivery'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_blocked' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function notificationPreference(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function phoneVerifications(): HasMany
    {
        return $this->hasMany(PhoneVerification::class);
    }

    public function isStaff(): bool
    {
        return $this->role !== 'customer';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Permission matrix per staff role.
     *
     * @return array<string, list<string>>
     */
    public static function permissionMatrix(): array
    {
        return [
            'product_manager' => ['dashboard', 'products', 'categories', 'inventory', 'banners', 'coupons', 'reports'],
            'order_manager' => ['dashboard', 'orders', 'payments', 'delivery', 'reports'],
            'support' => ['dashboard', 'orders.view', 'orders.update', 'customers'],
            'delivery' => ['dashboard', 'orders.view', 'delivery'],
        ];
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if ($this->role === 'customer') {
            return false;
        }
        foreach (self::permissionMatrix()[$this->role] ?? [] as $granted) {
            if ($granted === $permission || $permission === $granted) {
                return true;
            }
            // e.g. "orders" grants "orders.view" and "orders.update"
            if (str_starts_with($permission, $granted . '.')) {
                return true;
            }
        }
        return false;
    }
}
