<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;
use Modules\Auth\Http\Controllers\UserManagementController;
use Modules\Auth\Http\Controllers\RoleController;
use Modules\Auth\Http\Controllers\PermissionController;
use Modules\Auth\Http\Middleware\EnsureTokenHasAbility;
use Modules\Auth\Http\Middleware\CheckPermission;

Route::prefix('auth')->name('auth.')->group(function () {

    // ==================== Public Routes ====================
    Route::post('/check-user', [AuthController::class, 'checkUser'])
        ->middleware('throttle:login');

    Route::post('/login-password', [AuthController::class, 'loginWithPassword'])
        ->middleware('throttle:login');

    Route::post('/send-otp', [AuthController::class, 'sendOtp'])
        ->middleware('throttle:otp-send');

    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])
        ->middleware('throttle:otp-verify');

    // ==================== Protected Routes ====================
    Route::middleware('auth:sanctum')->group(function () {

        // User Profile Routes - بهبود یافته
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);

        // Profile management routes
        Route::post('/profile/update', [AuthController::class, 'updateProfile']);
        Route::post('/password/set', [AuthController::class, 'setPassword']);
        Route::post('/password/update', [AuthController::class, 'updatePassword']);

        // Email and phone verification routes - اضافه شده
        Route::post('/email/send-verification', [AuthController::class, 'sendEmailVerification']);
        Route::post('/email/verify', [AuthController::class, 'verifyEmail']);
        Route::post('/phone/send-verification', [AuthController::class, 'sendPhoneVerification']);
        Route::post('/phone/verify', [AuthController::class, 'verifyPhone']);

        // Token Refresh
        Route::post('/refresh', [AuthController::class, 'refresh'])
            ->middleware([EnsureTokenHasAbility::class . ':token:refresh', 'throttle:refresh']);
    });

    // ==================== Admin Routes ====================
    Route::middleware(['auth:sanctum', CheckPermission::class . ':users.view'])->group(function () {

        // User Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserManagementController::class, 'index'])->name('index');
            Route::get('/statistics', [UserManagementController::class, 'statistics'])->name('statistics');
            Route::get('/{id}', [UserManagementController::class, 'show'])->name('show');

            Route::middleware(CheckPermission::class . ':users.edit')->group(function () {
                Route::put('/{id}', [UserManagementController::class, 'update'])->name('update');
                Route::post('/{id}/toggle-lock', [UserManagementController::class, 'toggleLock'])->name('toggle-lock');
                Route::post('/{id}/reset-password', [UserManagementController::class, 'resetPassword'])->name('reset-password');
                Route::post('/{id}/verify-email', [UserManagementController::class, 'verifyEmail'])->name('verify-email');
                Route::post('/{id}/verify-phone', [UserManagementController::class, 'verifyPhone'])->name('verify-phone');
            });

            Route::delete('/{id}', [UserManagementController::class, 'destroy'])
                ->middleware(CheckPermission::class . ':users.delete')
                ->name('destroy');
        });

        // Role Management
        Route::prefix('roles')->name('roles.')->middleware(CheckPermission::class . ':roles.view')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/{id}', [RoleController::class, 'show'])->name('show');
            Route::get('/{id}/users', [RoleController::class, 'getUsers'])->name('users');

            Route::middleware(CheckPermission::class . ':roles.create')->group(function () {
                Route::post('/', [RoleController::class, 'store'])->name('store');
            });

            Route::middleware(CheckPermission::class . ':roles.edit')->group(function () {
                Route::put('/{id}', [RoleController::class, 'update'])->name('update');
            });

            Route::middleware(CheckPermission::class . ':roles.delete')->group(function () {
                Route::delete('/{id}', [RoleController::class, 'destroy'])->name('destroy');
            });

            Route::middleware(CheckPermission::class . ':users.manage_roles')->group(function () {
                Route::post('/user/{userId}/assign', [RoleController::class, 'assignRole'])->name('assign');
                Route::post('/user/{userId}/remove', [RoleController::class, 'removeRole'])->name('remove');
            });
        });

        // Permission Management
        Route::prefix('permissions')->name('permissions.')->middleware(CheckPermission::class . ':roles.view')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::get('/role/{roleId}', [PermissionController::class, 'getRolePermissions'])->name('role');

            Route::middleware(CheckPermission::class . ':roles.edit')->group(function () {
                Route::post('/', [PermissionController::class, 'store'])->name('store');
                Route::put('/{id}', [PermissionController::class, 'update'])->name('update');
                Route::put('/role/{roleId}', [PermissionController::class, 'updateRolePermissions'])->name('update-role');
                Route::delete('/{id}', [PermissionController::class, 'destroy'])->name('destroy');
            });
        });
    });
});

// Health check
Route::get('/auth/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'Auth Module',
        'timestamp' => now()->toISOString()
    ]);
});
