<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ApiResponse
{
    private function __construct()
    {
        //
    }

    public static function ok(
        mixed $data = null,
        string $message = '',
        array $meta = []
    ): JsonResponse {
        return self::respond(
            success: true,
            status: Response::HTTP_OK,
            message: $message ?: __('responses.success'),
            data: $data,
            errors: null,
            meta: $meta
        );
    }

    public static function created(
        mixed $data = null,
        string $message = '',
        array $meta = []
    ): JsonResponse {
        return self::respond(
            success: true,
            status: Response::HTTP_CREATED,
            message: $message ?: __('responses.created'),
            data: $data,
            errors: null,
            meta: $meta
        );
    }

    public static function updated(
        mixed $data = null,
        string $message = '',
        array $meta = []
    ): JsonResponse {
        return self::respond(
            success: true,
            status: Response::HTTP_OK,
            message: $message ?: __('responses.updated'),
            data: $data,
            errors: null,
            meta: $meta
        );
    }

    public static function deleted(
        string $message = '',
        array $meta = []
    ): JsonResponse {
        return self::respond(
            success: true,
            status: Response::HTTP_OK,
            message: $message ?: __('responses.deleted'),
            data: null,
            errors: null,
            meta: $meta
        );
    }

    public static function badRequest(
        string $message = '',
        ?array $errors = null,
        array $meta = []
    ): JsonResponse {
        return self::respond(
            success: false,
            status: Response::HTTP_BAD_REQUEST,
            message: $message ?: __('responses.bad_request'),
            data: null,
            errors: $errors,
            meta: $meta
        );
    }

    public static function unauthorized(
        string $message = '',
        ?array $errors = null,
        array $meta = []
    ): JsonResponse {
        return self::respond(
            success: false,
            status: Response::HTTP_UNAUTHORIZED,
            message: $message ?: __('responses.unauthorized'),
            data: null,
            errors: $errors,
            meta: $meta
        );
    }

    public static function forbidden(
        string $message = '',
        ?array $errors = null,
        array $meta = []
    ): JsonResponse {
        return self::respond(
            success: false,
            status: Response::HTTP_FORBIDDEN,
            message: $message ?: __('responses.forbidden'),
            data: null,
            errors: $errors,
            meta: $meta
        );
    }

    public static function notFound(
        string $message = '',
        ?array $errors = null,
        array $meta = []
    ): JsonResponse {
        return self::respond(
            success: false,
            status: Response::HTTP_NOT_FOUND,
            message: $message ?: __('responses.not_found'),
            data: null,
            errors: $errors,
            meta: $meta
        );
    }

    public static function validation(
        ?array $errors = null,
        string $message = '',
        array $meta = []
    ): JsonResponse {
        return self::respond(
            success: false,
            status: Response::HTTP_UNPROCESSABLE_ENTITY,
            message: $message ?: __('responses.validation_failed'),
            data: null,
            errors: $errors,
            meta: $meta
        );
    }

    public static function conflict(
        string $message = '',
        ?array $errors = null,
        array $meta = []
    ): JsonResponse {
        return self::respond(
            success: false,
            status: Response::HTTP_CONFLICT,
            message: $message ?: __('responses.conflict'),
            data: null,
            errors: $errors,
            meta: $meta
        );
    }

    public static function serverError(
        ?string $message = null,
        ?array $errors = null,
        array $meta = []
    ): JsonResponse {
        return self::respond(
            success: false,
            status: Response::HTTP_INTERNAL_SERVER_ERROR,
            message: $message ?? __('responses.server_error'),
            data: null,
            errors: $errors,
            meta: $meta
        );
    }

    private static function respond(
        bool $success,
        int $status,
        string $message,
        mixed $data,
        ?array $errors,
        array $meta
    ): JsonResponse {
        return response()->json([
            'success' => $success,
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
            'meta' => (object) $meta,
        ], $status);
    }
}
