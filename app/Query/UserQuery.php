<?php

declare(strict_types=1);

namespace App\Query;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class UserQuery
{
    /**
     * Fields available for user search.
     *
     * @var array<int, string>
     */
    private const SEARCHABLE = [
        'first_name',
        'last_name',
        'email',
    ];

    /**
     * Fields available for user sorting.
     *
     * @var array<int, string>
     */
    private const SORTABLE = [
        'first_name',
        'last_name',
        'email',
        'created_at',
        'updated_at',
    ];

    /**
     * Fields available for user filter.
     *
     * @var array<int, string>
     */
    private const FILTERABLE = [
        'status',
    ];

    public function __construct(
        private readonly QueryBuilder $queryBuilder,
    ) {}

    /**
     * Build the user query.
     */
    public function build(QueryParameters $parameters): Builder
    {
        return $this->queryBuilder->apply(
            User::query(),
            $parameters,
            self::SEARCHABLE,
            self::SORTABLE,
            self::FILTERABLE,
        );
    }
}
