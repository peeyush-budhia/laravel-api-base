<?php

declare(strict_types=1);

namespace App\Query;

use App\Models\User;
use App\Query\Contracts\QueryContract;
use App\Query\Contracts\QueryDefinition;
use Illuminate\Database\Eloquent\Builder;

final class UserQuery implements QueryContract, QueryDefinition
{
    public function __construct(
        private readonly QueryBuilder $queryBuilder,
    ) {}

    /**
     * Fields available for user search.
     *
     * @return array<int, string>
     */
    public function searchable(): array
    {
        return [
            'first_name',
            'last_name',
            'email',
        ];
    }

    /**
     * Fields available for user sorting.
     *
     * @return array<int, string>
     */
    public function sortable(): array
    {
        return [
            'first_name',
            'last_name',
            'email',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Fields available for user filtering.
     *
     * @return array<int, string>
     */
    public function filterable(): array
    {
        return [
            'status',
        ];
    }

    /**
     * Build the user query.
     */
    public function build(QueryParameters $parameters): Builder
    {
        return $this->queryBuilder->apply(
            User::query(),
            $parameters,
            $this,
        );

    }
}
