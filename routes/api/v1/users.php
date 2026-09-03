<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\User\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'password.changed'])->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | Authenticated User Profile
    |--------------------------------------------------------------------------
    */

    Route::put('profile', [UserController::class, 'updateProfile'])
        ->name('api.v1.profile.update');

    Route::post('profile/avatar', [UserController::class, 'updateAvatar'])
        ->name('api.v1.profile.avatar.update');

    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */

    Route::get('users', [UserController::class, 'index'])
        ->middleware('permission:users.view')
        ->name('api.v1.users.index');

    Route::get('users/{user}', [UserController::class, 'show'])
        ->middleware('permission:users.view')
        ->name('api.v1.users.show');

    Route::post('users', [UserController::class, 'store'])
        ->middleware('permission:users.create')
        ->name('api.v1.users.store');

    Route::put('users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.update')
        ->name('api.v1.users.update');

    Route::patch('users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.update')
        ->name('api.v1.users.update.patch');

    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users.delete')
        ->name('api.v1.users.destroy');

    Route::patch('users/{user}/restore', [UserController::class, 'restore'])
        ->middleware('permission:users.restore')
        ->name('api.v1.users.restore');

    Route::delete('/users/{user}/force', [UserController::class, 'forceDelete'])
        ->middleware('permission:users.delete')
        ->name('api.v1.users.force-delete');
});
