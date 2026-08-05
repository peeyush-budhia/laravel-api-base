<?php

declare(strict_types=1);

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {

    Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
        return ApiResponse::ok(
            data: $request->user(),
        );
    });

});