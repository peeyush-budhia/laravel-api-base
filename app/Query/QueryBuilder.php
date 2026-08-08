<?php

declare(strict_types=1);

namespace App\Query;

use App\Query\Contracts\QueryDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class QueryBuilder
{
    /**
     * Apply generic query parameters to an Eloquent query.
     */
    public function apply(
        Builder $query,
        QueryParameters $parameters,
        QueryDefinition $definition,
    ): Builder {
        $this->applySearch(
            $query,
            $parameters,
            $definition->searchable(),
        );

        $this->applyFilters(
            $query,
            $parameters,
            $definition->filterable(),
        );

        $this->applySort(
            $query,
            $parameters,
            $definition->sortable(),
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
