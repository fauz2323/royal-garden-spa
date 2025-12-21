<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\Admin\AdminSpaServiceController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminSettingsController;
use App\Http\Controllers\Api\Users\UserOrderController;
use App\Http\Controllers\Api\Users\UserSettingsController;
use App\Http\Controllers\Api\Users\UserSpaServiceController;
use App\Http\Controllers\Api\Users\UsersPointsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/orders/export', [AdminOrderController::class, 'getExcelOrder']);
Route::post('/set-fcm-token', [App\Http\Controllers\FcmController::class, 'setTokenUser']);
Route::get('/test-fcm', [App\Http\Controllers\FcmController::class, 'testFcm']);


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
        Route::get('/services/{id}/detail', [UserSpaServiceController::class, 'getServicesDetail']);

        // Route::get('/services', [UserOrderController::class, 'getAvailableServices']);
        Route::get('/orders', [UserOrderController::class, 'index']);
        Route::post('/orders/make', [UserOrderController::class, 'store']);
        Route::post('/orders/view', [UserOrderController::class, 'show']);
        Route::post('/orders/cancel', [UserOrderController::class, 'cancel']);

        Route::post('update-profile', [UserSettingsController::class, 'updateProfile']);
        Route::post('update-password', [UserSettingsController::class, 'updatePassword']);

        Route::get('/points', [UsersPointsController::class, 'index']);
        Route::get('/points/history', [UsersPointsController::class, 'getHistory']);
        Route::get('/points/leaderboards', [UsersPointsController::class, 'leaderboards']);
        Route::get('/points/voucher-shop', [UsersPointsController::class, 'getVoucherShop']);
        Route::get('/points/reward', [UsersPointsController::class, 'reward']);
        Route::post('/points/redeem-voucher', [UsersPointsController::class, 'reedemVoucher']);

        //user missions
        Route::get('/mission/index', [App\Http\Controllers\Api\Users\MissionController::class, 'index']);
        Route::get('/missions/claim/{id}', [App\Http\Controllers\Api\Users\MissionController::class, 'claim']);
    });

    // Admin only routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        //home
        Route::get('/dashboard', [App\Http\Controllers\Api\Admin\HomeController::class, 'index']);

        //user management
        Route::get('/users', [App\Http\Controllers\Api\Admin\UsersController::class, 'index']);
        Route::post('/users/detail', [App\Http\Controllers\Api\Admin\UsersController::class, 'detail']);
        Route::get('/users/points', [App\Http\Controllers\Api\Admin\UsersController::class, 'points']);

        // Route::apiResource('spa-services', AdminSpaServiceController::class);
        // Route::post('spa-services/{id}/toggle-status', [AdminSpaServiceController::class, 'toggleStatus']);
        Route::get('/spa-services', [AdminSpaServiceController::class, 'index']);
        Route::get('/spa-service/detail/{id}', [AdminSpaServiceController::class, 'show']);
        Route::post('/spa-services/create', [AdminSpaServiceController::class, 'store']);
        Route::post('/spa-services/{id}/update', [AdminSpaServiceController::class, 'update']);
        Route::post('/spa-services/{id}/toggle-status', [AdminSpaServiceController::class, 'toggleStatus']);
        Route::delete('/spa-services/{id}/delete', [AdminSpaServiceController::class, 'destroy']);

        //admin order routes
        Route::get('/orders/{status}', [AdminOrderController::class, 'index']);
        Route::post('/orders/view', [AdminOrderController::class, 'show']);
        Route::post('/orders/changeStatus', [AdminOrderController::class, 'changeStatus']);
        // Route::get('/orders/statistics', [AdminOrderController::class, 'statistics']);

        Route::post('update-profile', [AdminSettingsController::class, 'updateProfile']);
        Route::post('update-password', [AdminSettingsController::class, 'updatePassword']);

        //admin mission routes
        Route::get('/missions', [App\Http\Controllers\Api\Admin\MissionAdminController::class, 'index']);
        Route::post('/missions/create', [App\Http\Controllers\Api\Admin\MissionAdminController::class, 'create']);
        Route::post('/missions/detail', [App\Http\Controllers\Api\Admin\MissionAdminController::class, 'detail']);
        Route::post('/missions/edit', [App\Http\Controllers\Api\Admin\MissionAdminController::class, 'edit']);

        //admin voucher routes
        Route::get('/vouchers', [App\Http\Controllers\Api\Admin\VoucherAdminController::class, 'index']);
        Route::post('/vouchers/create', [App\Http\Controllers\Api\Admin\VoucherAdminController::class, 'create']);
        Route::post('/vouchers/detail', [App\Http\Controllers\Api\Admin\VoucherAdminController::class, 'detail']);
        Route::post('/vouchers/edit', [App\Http\Controllers\Api\Admin\VoucherAdminController::class, 'edit']);

        //admin user points
        Route::get('points/user-points', [App\Http\Controllers\Api\Admin\AdminUserPointController::class, 'index']);
        Route::post('points/add-points', [App\Http\Controllers\Api\Admin\AdminUserPointController::class, 'addPoints']);
    });
});
