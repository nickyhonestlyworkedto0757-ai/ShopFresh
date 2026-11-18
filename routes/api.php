<?php

use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Route;

Route::get('/homepage', [ApiController::class, 'homepage']);
Route::get('/categories', [ApiController::class, 'categories']);
Route::get('/banners', [ApiController::class, 'banners']);
Route::get('/products', [ApiController::class, 'products']);
Route::get('/products/{slug}', [ApiController::class, 'product']);
Route::get('/delivery-zones', [ApiController::class, 'deliveryZones']);
Route::get('/settings', [ApiController::class, 'storeSettings']);
Route::post('/auth/register', [ApiController::class, 'register']);
Route::post('/auth/login', [ApiController::class, 'login']);
Route::post('/payments/mpesa/callback', [ApiController::class, 'mpesaCallback']);

Route::middleware('auth.api')->group(function () {
    Route::get('/me', [ApiController::class, 'me']);
    Route::get('/me/preferences', [ApiController::class, 'myPreferences']);
    Route::put('/me/preferences', [ApiController::class, 'updatePreferences']);
    Route::get('/orders', [ApiController::class, 'myOrders']);
    Route::get('/orders/{order}/payment-status', [ApiController::class, 'orderPaymentStatus']);
    Route::post('/orders', [ApiController::class, 'placeOrder']);
    Route::post('/payments/mpesa/stk-push', [ApiController::class, 'mpesaStkPush']);
});
