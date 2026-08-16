<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\User\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | Authenticated User Profile
    |--------------------------------------------------------------------------
    */

    Route::put('profile', [UserController::class, 'updateProfile'])
        ->name('profile.update');

    Route::post('profile/avatar', [UserController::class, 'updateAvatar'])
        ->name('profile.avatar.update');

    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */

    Route::get('users', [UserController::class, 'index'])
        ->middleware('permission:users.view')
        ->name('users.index');

    Route::get('users/{user}', [UserController::class, 'show'])
        ->middleware('permission:users.view')
        ->name('users.show');

    Route::post('users', [UserController::class, 'store'])
        ->middleware('permission:users.create')
        ->name('users.store');

    Route::put('users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.update')
        ->name('users.update');

    Route::patch('users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.update');

    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users.delete')
        ->name('users.destroy');

    Route::patch('users/{user}/restore', [UserController::class, 'restore'])
        ->middleware('permission:users.restore')
        ->name('users.restore');

    Route::delete('/users/{user}/force', [UserController::class, 'forceDelete'])
        ->middleware('permission:users.delete')
        ->name('users.force-delete');

    Route::patch('users/{user}/status', [UserController::class, 'changeStatus'])
        ->middleware('permission:users.change-status')
        ->name('users.change-status');
});
