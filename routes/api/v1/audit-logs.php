<?php

use App\Http\Controllers\Api\V1\Audit\AuditLogController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('audit-logs')
    ->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])
            ->middleware('permission:audit-logs.view');

        Route::get('/{id}', [AuditLogController::class, 'show'])
            ->middleware('permission:audit-logs.view');
    });
