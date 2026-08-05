<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
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

            $e instanceof ValidationException =>
                ApiResponse::validation(
                    errors: $e->errors()
                ),

            $e instanceof AuthenticationException =>
                ApiResponse::unauthorized(),

            $e instanceof AuthorizationException =>
                ApiResponse::forbidden(),

            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException =>
                ApiResponse::notFound(),

            $e instanceof ThrottleRequestsException =>
                ApiResponse::badRequest(
                    message: 'Too many requests.'
                ),

            $e instanceof QueryException =>
                ApiResponse::serverError(
                    message: config('app.debug')
                        ? $e->getMessage()
                        : null
                ),

            $e instanceof HttpExceptionInterface =>
                ApiResponse::badRequest(
                    message: $e->getMessage() ?: Response::$statusTexts[$e->getStatusCode()]
                ),

            default =>
                ApiResponse::serverError(
                    message: config('app.debug')
                        ? $e->getMessage()
                        : null
                ),
        };
    }
}