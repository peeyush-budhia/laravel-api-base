<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Audit;

use App\Http\Controllers\BaseApiController;
use App\Http\Resources\Api\V1\AuditLogResource;
use App\Query\QueryParameters;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuditLogController extends BaseApiController
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Display audit logs.
     */
    public function index(Request $request): JsonResponse
    {
        $parameters = QueryParameters::fromRequest($request);

        $paginator = $this->auditLogService->index($parameters);

        return $this->paginated(
            AuditLogResource::collection($paginator),
            $paginator, __('responses.success'),
        );
    }

    /**
     * Display an audit log.
     */
    public function show(string $id): JsonResponse
    {

        return $this->success(
            new AuditLogResource($this->auditLogService->find($id)),
            __('responses.success')
        );
    }
}
