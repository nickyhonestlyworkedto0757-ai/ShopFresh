<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/email/verify/{id}/{hash}', function (Illuminate\Http\Request $request, int $id, string $hash) {
    $request->validate(['signature' => 'required']);
    if (! $request->hasValidSignature()) {
        abort(403, 'Invalid verification link.');
    }
    $user = \App\Models\User::findOrFail($id);
    if (hash_equals(sha1($user->email), $hash) && ! $user->email_verified_at) {
        $user->email_verified_at = now();
        $user->save();
        return redirect()->route('home')->with('success', 'Email verified. Thank you!');
    }
    return redirect()->route('account.preferences')->with('success', 'Email is already verified.');
})->name('verification.verify')->middleware('signed');

Route::post('/cookie-consent', [\App\Http\Controllers\CookieConsentController::class, 'store'])->name('cookie.consent');
Route::post('/newsletter', [\App\Http\Controllers\NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::view('/legal', 'legal')->name('legal');

Route::get('/', [ShopController::class, 'home'])->name('home');
Route::get('/shop', [ShopController::class, 'catalog'])->name('catalog');
Route::get('/product/{slug}', [ShopController::class, 'product'])->name('product');
Route::post('/product/{product}/review', [ShopController::class, 'review'])->middleware('auth')->name('product.review');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{product}', [CartController::class, 'remove'])->name('cart.remove');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon');
    Route::delete('/checkout/coupon', [CheckoutController::class, 'removeCoupon'])->name('checkout.coupon.remove');
    Route::post('/checkout', [CheckoutController::class, 'placeOrder'])->name('checkout.place');
    Route::get('/order/{order}/success', [CheckoutController::class, 'success'])->name('order.success');
    Route::get('/order/{order}/payment', [CheckoutController::class, 'paymentWaiting'])->name('order.payment');
    Route::get('/order/{order}/payment-status', [CheckoutController::class, 'paymentStatus'])->name('order.payment-status');
    Route::post('/order/{order}/payment-retry', [CheckoutController::class, 'paymentRetry'])->name('order.payment-retry');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/forgot-password', [\App\Http\Controllers\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [\App\Http\Controllers\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [\App\Http\Controllers\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [\App\Http\Controllers\ResetPasswordController::class, 'reset'])->name('password.update');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [AccountController::class, 'order'])->name('order');
    Route::get('/preferences', [AccountController::class, 'preferences'])->name('preferences');
    Route::put('/preferences', [AccountController::class, 'updatePreferences'])->name('preferences.update');
    Route::post('/phone/send', [AccountController::class, 'sendPhoneCode'])->name('phone.send');
    Route::post('/phone/verify', [AccountController::class, 'verifyPhone'])->name('phone.verify');
});
Route::get('/admin/login', [\App\Http\Controllers\AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [\App\Http\Controllers\AdminAuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [\App\Http\Controllers\AdminAuthController::class, 'logout'])->name('admin.logout');

Route::middleware(['admin.auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [Admin\ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('perm:products')->group(function () {
        Route::resource('products', Admin\ProductController::class)->except('show');
        Route::patch('/products/{product}/toggle-active', [Admin\ProductController::class, 'toggleActive'])->name('products.toggle');
        Route::delete('/products/{product}/images/{image}', [Admin\ProductController::class, 'deleteImage'])->name('products.images.destroy');
        Route::get('/homepage-sections', [Admin\HomepageSectionController::class, 'index'])->name('homepage-sections.index');
        Route::get('/homepage-sections/{section}/edit', [Admin\HomepageSectionController::class, 'edit'])->name('homepage-sections.edit');
        Route::put('/homepage-sections/{section}', [Admin\HomepageSectionController::class, 'update'])->name('homepage-sections.update');
        Route::patch('/homepage-sections/{section}/toggle', [Admin\HomepageSectionController::class, 'toggle'])->name('homepage-sections.toggle');
        Route::post('/homepage-sections/reorder', [Admin\HomepageSectionController::class, 'reorder'])->name('homepage-sections.reorder');
    });
    Route::middleware('perm:categories')->group(function () {
        Route::resource('categories', Admin\CategoryController::class)->except('show');
    });
    Route::middleware('perm:banners')->group(function () {
        Route::resource('banners', Admin\BannerController::class)->except('show');
    });
    Route::middleware('perm:coupons')->group(function () {
        Route::resource('coupons', Admin\CouponController::class)->except('show');
    });

    Route::get('/orders', [Admin\OrderController::class, 'index'])->middleware('perm:orders.view')->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->middleware('perm:orders.view')->name('orders.show');
    Route::get('/orders/{order}/invoice', [Admin\OrderController::class, 'invoice'])->middleware('perm:orders.view')->name('orders.invoice');
    Route::get('/orders/{order}/invoice.pdf', [Admin\OrderController::class, 'invoicePdf'])->middleware('perm:orders.view')->name('orders.invoice.pdf');
    Route::patch('/orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->middleware('perm:orders.update')->name('orders.status');
    Route::patch('/orders/{order}/assign', [Admin\OrderController::class, 'assign'])->middleware('perm:orders')->name('orders.assign');
    Route::post('/orders/{order}/refund', [Admin\OrderController::class, 'refund'])->middleware('perm:payments')->name('orders.refund');
    Route::post('/orders/{order}/notify', [Admin\OrderController::class, 'notify'])->middleware('perm:orders.update')->name('orders.notify');
    Route::middleware('perm:payments')->group(function () {
        Route::get('/payments', [Admin\PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/export.csv', [Admin\PaymentController::class, 'exportCsv'])->name('payments.export.csv');
        Route::get('/payments/export.pdf', [Admin\PaymentController::class, 'exportPdf'])->name('payments.export.pdf');
        Route::get('/payments/{payment}', [Admin\PaymentController::class, 'show'])->name('payments.show');
        Route::post('/payments/{payment}/query-mpesa', [Admin\PaymentController::class, 'queryMpesa'])->name('payments.query');
        Route::patch('/payments/{payment}/verify', [Admin\PaymentController::class, 'verify'])->name('payments.verify');
        Route::patch('/payments/{payment}/fail', [Admin\PaymentController::class, 'markFailed'])->name('payments.fail');
    });
    Route::middleware('perm:delivery')->group(function () {
        Route::get('/delivery/zones', [Admin\DeliveryZoneController::class, 'index'])->name('delivery.zones');
        Route::post('/delivery/zones', [Admin\DeliveryZoneController::class, 'store'])->name('delivery.zones.store');
        Route::patch('/delivery/zones/{zone}', [Admin\DeliveryZoneController::class, 'update'])->name('delivery.zones.update');
        Route::delete('/delivery/zones/{zone}', [Admin\DeliveryZoneController::class, 'destroy'])->name('delivery.zones.destroy');
    });
    Route::middleware('perm:customers')->group(function () {
        Route::get('/customers', [Admin\CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{user}', [Admin\CustomerController::class, 'show'])->name('customers.show');
        Route::patch('/customers/{user}/block', [Admin\CustomerController::class, 'toggleBlock'])->name('customers.block');
        Route::get('/newsletter', [Admin\NewsletterController::class, 'index'])->name('newsletter.index');
        Route::get('/newsletter.csv', [Admin\NewsletterController::class, 'exportCsv'])->name('newsletter.csv');
    });
    Route::middleware('perm:reports')->group(function () {
        Route::get('/reports/sales', [Admin\ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/sales.csv', [Admin\ReportController::class, 'salesCsv'])->name('reports.sales.csv');
        Route::get('/reports/sales.pdf', [Admin\ReportController::class, 'salesPdf'])->name('reports.sales.pdf');
        Route::get('/reports/profit', [Admin\ReportController::class, 'profit'])->name('reports.profit');
        Route::get('/reports/products', [Admin\ReportController::class, 'products'])->name('reports.products');
        Route::get('/reports/customers', [Admin\ReportController::class, 'customers'])->name('reports.customers');
    });

    Route::middleware('perm:settings')->group(function () {
        Route::get('/settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/mpesa-test', [Admin\SettingsController::class, 'testMpesa'])->name('settings.mpesa-test');
    });
    Route::middleware('perm:admin_users')->group(function () {
        Route::resource('admin-users', Admin\AdminUserController::class)->except('show')->parameters(['admin-users' => 'admin']);
    });

    Route::get('/notifications', [Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [Admin\NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::get('/notifications/{notification}/open', [Admin\NotificationController::class, 'open'])->name('notifications.open');
    Route::delete('/notifications', [Admin\NotificationController::class, 'clear'])->name('notifications.clear');
    Route::middleware('perm:banners')->group(function () {
        Route::get('/notifications/send', [Admin\NotificationController::class, 'sendForm'])->name('notifications.send');
        Route::post('/notifications/send', [Admin\NotificationController::class, 'send'])->name('notifications.send.post');
        Route::get('/notifications/history', [Admin\NotificationController::class, 'history'])->name('notifications.history');
        Route::post('/notifications/history/{log}/retry', [Admin\NotificationController::class, 'retry'])->name('notifications.retry');
        Route::get('/notifications/templates', [Admin\NotificationController::class, 'templates'])->name('notifications.templates');
        Route::get('/notifications/templates/create', [Admin\NotificationController::class, 'templatesCreate'])->name('notifications.templates.create');
        Route::post('/notifications/templates', [Admin\NotificationController::class, 'templatesStore'])->name('notifications.templates.store');
        Route::get('/notifications/templates/{template}/edit', [Admin\NotificationController::class, 'templatesEdit'])->name('notifications.templates.edit');
        Route::put('/notifications/templates/{template}', [Admin\NotificationController::class, 'templatesUpdate'])->name('notifications.templates.update');
        Route::delete('/notifications/templates/{template}', [Admin\NotificationController::class, 'templatesDestroy'])->name('notifications.templates.destroy');
    });
    Route::get('/activity-log', [Admin\NotificationController::class, 'activity'])->middleware('perm:settings')->name('activity.index');
    Route::middleware('perm:settings')->group(function () {
        Route::get('/cookies', [Admin\CookieController::class, 'index'])->name('cookies.index');
        Route::put('/cookies', [Admin\CookieController::class, 'update'])->name('cookies.update');
    });
});
