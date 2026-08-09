<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\User\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */

    Route::get('users', [UserController::class, 'index'])
        ->name('users.index');

    Route::get('users/{user}', [UserController::class, 'show'])
        ->name('users.show');

    Route::post('users', [UserController::class, 'store'])
        ->name('users.store');

    Route::put('users/{user}', [UserController::class, 'update'])
        ->name('users.update');

    Route::patch('users/{user}', [UserController::class, 'update']);

    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->name('users.destroy');

    Route::patch('users/{user}/restore', [UserController::class, 'restore'])
        ->name('users.restore');

    Route::patch('users/{user}/status', [UserController::class, 'changeStatus'])
        ->name('users.change-status');

});
