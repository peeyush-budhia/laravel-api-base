<?php

declare(strict_types=1);

namespace App\Query;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class UserQuery
{
    private readonly GenericQueryDefinition $definition;

    public function __construct(
        private readonly QueryBuilder $queryBuilder,
    ) {
        $this->definition = new GenericQueryDefinition(
            searchable: [
                'first_name',
                'last_name',
                'email',
            ],
            sortable: [
                'first_name',
                'last_name',
                'email',
                'created_at',
                'updated_at',
            ],
            filterable: [
                'status',
            ],
        );
    }

    public function build(QueryParameters $parameters): Builder
    {
        return $this->queryBuilder->apply(
            User::query(),
            $parameters,
            $this->definition,
        );
    }
}
