<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class ApiResponse
{
    private function __construct()
    {
        //
    }

    /**
     * Return ok response.
     */
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

    /**
     * Return created response.
     */
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

    /**
     * Return updated response.
     */
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

    /**
     * Return status changed response.
     */
    public static function statusChanged(
        mixed $data = null,
        string $message = '',
        array $meta = []
    ): JsonResponse {
        return self::respond(
            success: true,
            status: Response::HTTP_OK,
            message: $message ?: __('responses.status_changed'),
            data: $data,
            errors: null,
            meta: $meta,
        );
    }

    /**
     * Return deleted response.
     */
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

    /**
     * Return restored response.
     */
    public static function restored(
        mixed $data = null,
        string $message = '',
        array $meta = []
    ): JsonResponse {
        return self::respond(
            success: true,
            status: Response::HTTP_OK,
            message: $message ?: __('responses.restored'),
            data: $data,
            errors: null,
            meta: $meta,
        );
    }

    /**
     * Return bad request response.
     */
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

    /**
     * Return unauthorized response.
     */
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

    /**
     * Return forbidden response.
     */
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

    /**
     * Return not found response.
     */
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

    /**
     * Return validation response.
     */
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

    /**
     * Return conflict response.
     */
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

    /**
     * Return server error response.
     */
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

    /**
     * Return paginated response.
     */
    public static function paginated(
        ResourceCollection $resource,
        LengthAwarePaginator $paginator,
        string $message = '',
    ): JsonResponse {
        return self::respond(
            success: true,
            status: Response::HTTP_OK,
            message: $message ?: __('responses.success'),
            data: $resource->collection,
            errors: null,
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'path' => $paginator->path(),
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ],
        );
    }

    /**
     * Return error response.
     */
    public static function error(
        int $status,
        string $message,
        ?array $errors = null,
        array $meta = []
    ): JsonResponse {
        return self::respond(
            success: false,
            status: $status,
            message: $message,
            data: null,
            errors: $errors,
            meta: $meta
        );
    }
}
