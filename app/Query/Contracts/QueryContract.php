<?php

declare(strict_types=1);

namespace App\Query\Contracts;

use App\Query\QueryParameters;
use Illuminate\Database\Eloquent\Builder;

interface QueryContract
{
    /**
     * Build the base query.
     */
    public function build(QueryParameters $parameters): Builder;

    /**
     * Get the query definition.
     */
    public function definition(): QueryDefinition;
}
