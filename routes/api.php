<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware('api')
    ->group(base_path('routes/api/v1.php'));