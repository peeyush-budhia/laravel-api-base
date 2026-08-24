<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Query\AuditLogQuery;
use App\Query\QueryExecutor;
use App\Query\QueryParameters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class AuditLogService
{
    public function __construct(
        private readonly QueryExecutor $queryExecutor,
        private readonly AuditLogQuery $auditLogQuery,
    ) {}

    /**
     * Paginate audit logs.
     */
    public function paginate(
        QueryParameters $parameters,
    ): LengthAwarePaginator {
        return $this->queryExecutor->paginate(
            $this->auditLogQuery,
            $parameters,
        );
    }

    /**
     * Find an audit log.
     */
    public function find(string $id): AuditLog
    {
        return AuditLog::query()
            ->with('user')
            ->findOrFail($id);
    }
}
