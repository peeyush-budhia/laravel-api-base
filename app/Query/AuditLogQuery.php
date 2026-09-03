<?php

declare(strict_types=1);

namespace App\Query;

use App\Models\AuditLog;
use App\Query\Contracts\QueryContract;
use App\Query\Contracts\QueryDefinition;
use Illuminate\Database\Eloquent\Builder;

final class AuditLogQuery implements QueryContract
{
    /**
     * Get the query definition.
     */
    public function definition(): QueryDefinition
    {
        return new GenericQueryDefinition(
            searchable: [
                'auditable_type',
                'auditable_id',
            ],
            sortable: [
                'event',
                'auditable_type',
                'auditable_id',
                'created_at',
                'updated_at',
            ],
            filterable: [
                'event',
                'user_id',
                'auditable_type',
                'auditable_id',
            ],
        );
    }

    /**
     * Build audit log query.
     */
    public function build(
        QueryParameters $parameters,
    ): Builder {
        return AuditLog::query()->select([
            'id',
            'user_id',
            'event',
            'auditable_type',
            'auditable_id',
            'old_values',
            'new_values',
            'url',
            'ip_address',
            'user_agent',
            'created_at',
            'updated_at',
        ])
            ->with([
                'user.roles',
                'user.permissions',
            ]);
    }
}
