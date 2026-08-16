<?php

declare(strict_types=1);

namespace App\Query;

use App\Query\Contracts\QueryContract;
use App\Query\Contracts\QueryDefinition;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

final class RoleQuery implements QueryContract
{
    /**
     * Get the query definition.
     */
    public function definition(): QueryDefinition
    {
        return new GenericQueryDefinition(
            searchable: [
                'name',
            ],
            sortable: [
                'name',
                'created_at',
                'updated_at',
            ],
            filterable: [
                'guard_name',
            ],
        );
    }

    /**
     * Build role query.
     */
    public function build(
        QueryParameters $parameters,
    ): Builder {
        return Role::query();
    }
}
