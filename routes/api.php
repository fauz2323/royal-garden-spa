<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\Admin\AdminSpaServiceController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminSettingsController;
use App\Http\Controllers\Api\Users\UserOrderController;
use App\Http\Controllers\Api\Users\UserSettingsController;
use App\Http\Controllers\Api\Users\UserSpaServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    // Customer routes
    Route::middleware('role:customer')->prefix('customer')->group(function () {
        Route::get('/services', [UserSpaServiceController::class, 'getServicesList']);

        Route::get('/services', [UserOrderController::class, 'getAvailableServices']);
        Route::get('/orders', [UserOrderController::class, 'index']);
        Route::post('/orders', [UserOrderController::class, 'store']);
        Route::get('/orders/{id}', [UserOrderController::class, 'show']);
        Route::put('/orders/{id}', [UserOrderController::class, 'update']);
        Route::post('/orders/{id}/cancel', [UserOrderController::class, 'cancel']);

        Route::post('update-profile', [UserSettingsController::class, 'updateProfile']);
        Route::post('update-password', [UserSettingsController::class, 'updatePassword']);
    });

    // Admin only routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::apiResource('spa-services', AdminSpaServiceController::class);
        Route::post('spa-services/{id}/toggle-status', [AdminSpaServiceController::class, 'toggleStatus']);

        // Order Management Routes
        Route::apiResource('orders', AdminOrderController::class);
        Route::post('orders/{id}/accept', [AdminOrderController::class, 'accept']);
        Route::post('orders/{id}/reject', [AdminOrderController::class, 'reject']);
        Route::post('orders/{id}/start', [AdminOrderController::class, 'startService']);
        Route::post('orders/{id}/complete', [AdminOrderController::class, 'complete']);
        Route::get('orders-statistics', [AdminOrderController::class, 'statistics']);

        Route::post('update-profile', [AdminSettingsController::class, 'updateProfile']);
        Route::post('update-password', [AdminSettingsController::class, 'updatePassword']);
    });
});
