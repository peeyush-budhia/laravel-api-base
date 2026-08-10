<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ExceptionHandler
{
    public static function render(Request $request, Throwable $e)
    {
        if (! $request->expectsJson()) {
            throw $e;
        }

        return match (true) {

            /*
            |--------------------------------------------------------------------------
            | Validation
            |--------------------------------------------------------------------------
            */

            $e instanceof ValidationException => ApiResponse::validation(
                errors: $e->errors(),
            ),

            /*
            |--------------------------------------------------------------------------
            | Authentication
            |--------------------------------------------------------------------------
            */

            $e instanceof AuthenticationException => ApiResponse::unauthorized(),

            /*
            |--------------------------------------------------------------------------
            | Authorization
            |--------------------------------------------------------------------------
            */

            $e instanceof AuthorizationException => ApiResponse::forbidden(),

            /*
            |--------------------------------------------------------------------------
            | Model Not Found
            |--------------------------------------------------------------------------
            */

            $e instanceof ModelNotFoundException => ApiResponse::notFound(),

            /*
            |--------------------------------------------------------------------------
            | Route Not Found
            |--------------------------------------------------------------------------
            */

            $e instanceof NotFoundHttpException => ApiResponse::notFound(),

            /*
            |--------------------------------------------------------------------------
            | Method Not Allowed
            |--------------------------------------------------------------------------
            */

            $e instanceof MethodNotAllowedHttpException => ApiResponse::error(
                status: Response::HTTP_METHOD_NOT_ALLOWED,
                message: Response::$statusTexts[Response::HTTP_METHOD_NOT_ALLOWED],
            ),

            /*
            |--------------------------------------------------------------------------
            | Too Many Requests
            |--------------------------------------------------------------------------
            */

            $e instanceof ThrottleRequestsException => ApiResponse::error(
                status: Response::HTTP_TOO_MANY_REQUESTS,
                message: __('responses.too_many_requests'),
            ),

            /*
            |--------------------------------------------------------------------------
            | Database Errors
            |--------------------------------------------------------------------------
            */

            $e instanceof QueryException => ApiResponse::serverError(
                message: config('app.debug')
                    ? $e->getMessage()
                    : null,
            ),

            /*
            |--------------------------------------------------------------------------
            | Role Deletion
            |--------------------------------------------------------------------------
            */

            $e instanceof RoleDeletionException => ApiResponse::error(
                status: Response::HTTP_CONFLICT,
                message: $e->getMessage(),
            ),

            /*
            |--------------------------------------------------------------------------
            | Other HTTP Exceptions
            |--------------------------------------------------------------------------
            */

            $e instanceof HttpExceptionInterface => ApiResponse::error(
                status: $e->getStatusCode(),
                message: $e->getMessage() !== ''
                    ? $e->getMessage()
                    : (Response::$statusTexts[$e->getStatusCode()] ?? 'HTTP Error'),
            ),

            /*
            |--------------------------------------------------------------------------
            | Fallback
            |--------------------------------------------------------------------------
            */

            default => ApiResponse::serverError(
                message: config('app.debug')
                    ? $e->getMessage()
                    : null,
            ),
        };
    }
}
