<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
    'permission:dashboard.view',
])->group(function (): void {
    Route::get('/dashboard', DashboardController::class);
});
