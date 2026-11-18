<?php

namespace App\Providers;

use App\Models\AdminNotification;
use App\Support\Settings;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('currency', Settings::get('currency', 'KES'))
                ->with('storeName', Settings::get('store_name', 'ShopFresh'))
                ->with('storeLogo', Settings::get('store_logo') ? asset('storage/' . Settings::get('store_logo')) : null)
                ->with('adminUnreadCount', \Illuminate\Support\Facades\Auth::guard('admin')->check()
                    ? AdminNotification::whereNull('read_at')->count()
                    : 0);
        });
    }
}
