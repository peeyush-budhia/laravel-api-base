<?php

declare(strict_types=1);

namespace App\Query;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class QueryExecutor
{
    /**
     * Paginate the query.
     */
    public function paginate(
        Builder $query,
        QueryParameters $parameters,
    ): LengthAwarePaginator {
        return $query->paginate(
            perPage: $parameters->perPage,
            page: $parameters->page,
        );
    }
}
