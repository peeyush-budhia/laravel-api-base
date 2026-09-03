<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Role\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | Role Management
    |--------------------------------------------------------------------------
    */

    Route::get('roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view')
        ->name('roles.index');

    /*
    |--------------------------------------------------------------------------
    | Role Permissions
    |--------------------------------------------------------------------------
    */

    // IMPORTANT: Must be before roles/{role}
    Route::get('roles/permissions', [RoleController::class, 'allPermissions'])
        ->middleware('permission:roles.view')
        ->name('api.v1.roles.permissions.all');

    Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])
        ->middleware('permission:roles.view')
        ->name('api.v1.roles.permissions');

    Route::put('roles/{role}/permissions', [RoleController::class, 'syncPermissions'])
        ->middleware('permission:roles.manage-permissions')
        ->name('api.v1.roles.permissions.sync');

    /*
    |--------------------------------------------------------------------------
    | Individual Role
    |--------------------------------------------------------------------------
    */

    Route::get('roles/{role}', [RoleController::class, 'show'])
        ->middleware('permission:roles.view')
        ->name('api.v1.roles.show');

    Route::post('roles', [RoleController::class, 'store'])
        ->middleware('permission:roles.create')
        ->name('api.v1.roles.store');

    Route::put('roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.update')
        ->name('api.v1.roles.update');

    Route::patch('roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.update')
        ->name('api.v1.roles.update.patch');

    Route::delete('roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.delete')
        ->name('api.v1.roles.destroy');
});
