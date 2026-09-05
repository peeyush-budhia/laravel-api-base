<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware([
        'api',
        'api.performance',
    ])
    ->group(base_path('routes/api/v1.php'))->name('api.v1');
