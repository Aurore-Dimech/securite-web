<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\EnsureTokenIsValid;
use App\Http\Middleware\CheckRoleAuthorization;
use App\Http\Controllers\ShopifyWebhookController;
use App\Http\Middleware\ContentSecurityPolicy;

Route::middleware(['throttle:global', ContentSecurityPolicy::class])->group(function () {

    Route::get('/health', [HealthController::class, 'index']);
    
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/my-user', [AuthController::class, 'show'])->middleware([EnsureTokenIsValid::class, CheckRoleAuthorization::class.':can_get_my_user']);
    Route::get('/users', [AuthController::class, 'index'])->middleware([EnsureTokenIsValid::class, CheckRoleAuthorization::class.':can_get_users']);
    
    Route::post('/products', [ProductController::class, 'store'])->middleware([EnsureTokenIsValid::class, CheckRoleAuthorization::class.':can_add_product']);
    Route::get('/my-products', [ProductController::class, 'show'])->middleware([EnsureTokenIsValid::class, CheckRoleAuthorization::class.':can_get_my_product']);
    Route::get('/my-bestsellers', [ProductController::class, 'bestsellers'])->middleware([EnsureTokenIsValid::class, CheckRoleAuthorization::class.':can_get_my_bestsellers']);
    Route::get('/products', [ProductController::class, 'index'])->middleware([EnsureTokenIsValid::class, CheckRoleAuthorization::class.':can_get_products']);
    Route::post('/webhooks/shopify-sales', [ShopifyWebhookController::class, 'handleSalesWebhook']);
});
