<?php

declare(strict_types=1);

use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        /*
        |--------------------------------------------------------------------------
        | API Requests Should Return JSON
        |--------------------------------------------------------------------------
        */

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*'),
        );

        /*
        |--------------------------------------------------------------------------
        | Validation Error - 422
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (ValidationException $e) {

            return ApiResponse::validation(
                errors: $e->errors(),
            );

        });

        /*
        |--------------------------------------------------------------------------
        | Authentication Error - 401
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (AuthenticationException $e) {

            return ApiResponse::unauthorized();

        });

        /*
        |--------------------------------------------------------------------------
        | Authorization Error - 403
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (AuthorizationException $e) {

            return ApiResponse::forbidden();

        });

        /*
        |--------------------------------------------------------------------------
        | Model Not Found - 404
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (ModelNotFoundException $e) {

            return ApiResponse::notFound();

        });

        /*
        |--------------------------------------------------------------------------
        | Route Not Found - 404
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (NotFoundHttpException $e) {

            return ApiResponse::notFound();

        });

    })
    ->create();
