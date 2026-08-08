<?php

declare(strict_types=1);

namespace App\Query;

use App\Query\Contracts\QueryDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class QueryExecutor
{
    public function __construct(
        private readonly QueryBuilder $queryBuilder,
    ) {}

    /**
     * Build and paginate a query.
     */
    public function paginate(
        Builder $query,
        QueryParameters $parameters,
        QueryDefinition $definition,
    ): LengthAwarePaginator {
        $query = $this->queryBuilder->apply(
            $query,
            $parameters,
            $definition,
        );

        return $query->paginate(
            perPage: $parameters->perPage,
            page: $parameters->page,
        );
    }
}
