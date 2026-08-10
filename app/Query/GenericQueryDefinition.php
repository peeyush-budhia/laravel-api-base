<?php

declare(strict_types=1);

namespace App\Query;

use App\Query\Contracts\QueryDefinition;

final readonly class GenericQueryDefinition implements QueryDefinition
{
    /**
     * @param  array<int, string>  $searchable
     * @param  array<int, string>  $sortable
     * @param  array<int, string>  $filterable
     */
    public function __construct(
        private array $searchable = [],
        private array $sortable = [],
        private array $filterable = [],
    ) {}

    /**
     * @return array<int, string>
     */
    public function searchable(): array
    {
        return $this->searchable;
    }

    /**
     * @return array<int, string>
     */
    public function sortable(): array
    {
        return $this->sortable;
    }

    /**
     * @return array<int, string>
     */
    public function filterable(): array
    {
        return $this->filterable;
    }
}
