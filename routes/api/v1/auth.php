<?php

// declare(strict_types=1);

// use App\Support\ApiResponse;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;

// /*
// |--------------------------------------------------------------------------
// | Authentication Routes
// |--------------------------------------------------------------------------
// */

// Route::prefix('auth')->group(function () {

//     Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
//         return ApiResponse::ok(
//             data: $request->user(),
//         );
//     });

// });


declare(strict_types=1);

use App\Http\Resources\Api\V1\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', function (Request $request) {
        return ApiResponse::ok(
            data: new UserResource($request->user()),
            message: __('responses.success'),
        );
    });
});