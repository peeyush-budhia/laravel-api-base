<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    /**
     * Return successful response.
     */
    protected function success(
        mixed $data = null,
        string $message = '',
        array $meta = []
    ): JsonResponse {
        return ApiResponse::ok(
            data: $data,
            message: $message,
            meta: $meta,
        );
    }

    /**
     * Return created response.
     */
    protected function created(
        mixed $data = null,
        string $message = '',
        array $meta = []
    ): JsonResponse {
        return ApiResponse::created(
            data: $data,
            message: $message,
            meta: $meta,
        );
    }

    /**
     * Return updated response.
     */
    protected function updated(
        mixed $data = null,
        string $message = '',
        array $meta = []
    ): JsonResponse {
        return ApiResponse::updated(
            data: $data,
            message: $message,
            meta: $meta,
        );
    }

    /**
     * Return deleted response.
     */
    protected function deleted(
        string $message = '',
        array $meta = []
    ): JsonResponse {
        return ApiResponse::deleted(
            message: $message,
            meta: $meta,
        );
    }

    /**
     * Return error response.
     */
    protected function error(
        string $message = '',
        ?array $errors = null,
        array $meta = []
    ): JsonResponse {
        return ApiResponse::badRequest(
            message: $message,
            errors: $errors,
            meta: $meta,
        );
    }
}
