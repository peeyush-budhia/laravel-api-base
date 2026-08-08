<?php

declare(strict_types=1);

namespace App\Query\Contracts;

interface QueryDefinition
{
    /**
     * Fields available for searching.
     *
     * @return array<int, string>
     */
    public function searchable(): array;

    /**
     * Fields available for sorting.
     *
     * @return array<int, string>
     */
    public function sortable(): array;

    /**
     * Fields available for filtering.
     *
     * @return array<int, string>
     */
    public function filterable(): array;
}
