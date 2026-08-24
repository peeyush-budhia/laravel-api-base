<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Audit;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Api\V1\Audit\AuditLogIndexRequest;
use App\Http\Resources\Api\V1\AuditLogResource;
use App\Services\Audit\AuditLogService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class AuditLogController extends BaseApiController
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Display audit logs.
     */
    public function index(
        AuditLogIndexRequest $request,
    ): JsonResponse {
        $paginator = $this->auditLogService->paginate(
            $request->queryParameters(),
        );

        return ApiResponse::paginated(
            AuditLogResource::collection($paginator),
            $paginator,
        );
    }
}
