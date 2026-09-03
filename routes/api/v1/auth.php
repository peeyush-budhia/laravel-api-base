<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordPolicyController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {

    Route::post('/login', [
        AuthController::class,
        'login',
    ])->name('api.v1.auth.login');

    Route::post('/forgot-password', [
        AuthController::class,
        'forgotPassword',
    ])->name('api.v1.auth.forgot-password');

    Route::post('/reset-password', [
        AuthController::class,
        'resetPassword',
    ])->name('api.v1.auth.reset-password');

    Route::get('/password-policy', [
        PasswordPolicyController::class,
        'show',
    ])->name('api.v1.auth.password-policy');

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [
            AuthController::class,
            'logout',
        ])->name('api.v1.auth.logout');

        Route::get('/me', [
            AuthController::class,
            'me',
        ])->name('api.v1.auth.me');

        Route::post('/change-password', [
            AuthController::class,
            'changePassword',
        ])->name('api.v1.auth.change-password');

    });

});
