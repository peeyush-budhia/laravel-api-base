<?php

declare(strict_types=1);

use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return ApiResponse::ok(
        data: [
            'application' => config('app.name'),
            'version'     => 'v1',
            'environment' => app()->environment(),
            'timestamp'   => now()->toIso8601String(),
        ],
        message: 'API is healthy.',
    );
});