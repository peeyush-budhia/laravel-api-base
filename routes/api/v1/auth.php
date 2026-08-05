<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {

    Route::post('/login', [
        AuthController::class,
        'login',
    ]);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [
            AuthController::class,
            'logout',
        ]);

        Route::get('/me', [
            AuthController::class,
            'me',
        ]);

    });

});
