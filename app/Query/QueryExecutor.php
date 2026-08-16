<?php

declare(strict_types=1);

namespace App\Query;

use App\Query\Contracts\QueryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class QueryExecutor
{
    public function __construct(
        private readonly QueryBuilder $queryBuilder,
    ) {}

    /**
     * Build and paginate a query.
     */
    public function paginate(
        QueryContract $query,
        QueryParameters $parameters,
    ): LengthAwarePaginator {
        $builder = $query->build($parameters);

        return $this->queryBuilder
            ->apply(
                $builder,
                $parameters,
                $query->definition(),
            )
            ->paginate(
                perPage: $parameters->perPage,
                page: $parameters->page,
            );
    }
}
