<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ApiResponse
{
    private function __construct()
    {
        // Prevent instantiation.
    }

    public static function success(
        mixed $data = null,
        string $message = 'Request successful.',
        array $meta = [],
        int $status = Response::HTTP_OK,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
            'errors'  => null,
            'meta'    => (object) $meta,
        ], $status);
    }

    public static function error(
        string $message = 'Request failed.',
        array|string|null $errors = null,
        int $status = Response::HTTP_BAD_REQUEST,
        array $meta = [],
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'status'  => $status,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
            'meta'    => (object) $meta,
        ], $status);
    }
}