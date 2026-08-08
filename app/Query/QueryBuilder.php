<?php

declare(strict_types=1);

namespace App\Query;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class QueryBuilder
{
    /**
     * Apply generic query parameters to an Eloquent query.
     *
     * @param  array<int, string>  $searchable
     * @param  array<int, string>  $sortable
     * @param  array<int, string>  $filterable
     */
    public function apply(
        Builder $query,
        QueryParameters $parameters,
        array $searchable = [],
        array $sortable = [],
        array $filterable = [],
    ): Builder {
        $this->applySearch(
            $query,
            $parameters,
            $searchable,
        );

        $this->applyFilters(
            $query,
            $parameters,
            $filterable,
        );

        $this->applySort(
            $query,
            $parameters,
            $sortable,
        );

        return $query;
    }

    /**
     * Apply search conditions.
     *
     * @param  array<int, string>  $searchable
     */
    private function applySearch(
        Builder $query,
        QueryParameters $parameters,
        array $searchable,
    ): void {
        if (
            $parameters->search === null ||
            $searchable === []
        ) {
            return;
        }

        $search = $parameters->search;

        $query->where(function (Builder $query) use (
            $search,
            $searchable,
        ): void {
            foreach ($searchable as $column) {
                $query->orWhere(
                    $column,
                    'LIKE',
                    '%'.$search.'%',
                );
            }
        });
    }

    /**
     * Apply allowed filters.
     *
     * @param  array<int, string>  $filterable
     */
    private function applyFilters(
        Builder $query,
        QueryParameters $parameters,
        array $filterable,
    ): void {
        if (
            $parameters->filters === [] ||
            $filterable === []
        ) {
            return;
        }

        foreach ($parameters->filters as $column => $value) {
            if (! in_array($column, $filterable, true)) {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $query->where($column, $value);
        }
    }

    /**
     * Apply sorting.
     *
     * @param  array<int, string>  $sortable
     */
    private function applySort(
        Builder $query,
        QueryParameters $parameters,
        array $sortable,
    ): void {
        if (
            $parameters->sort === null ||
            $sortable === []
        ) {
            return;
        }

        if (! in_array($parameters->sort, $sortable, true)) {
            return;
        }

        $query->orderBy(
            $parameters->sort,
            $parameters->direction,
        );
    }

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
