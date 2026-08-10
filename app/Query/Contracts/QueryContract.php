<?php

declare(strict_types=1);

namespace App\Query\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface QueryContract
{
    /**
     * Build the base query.
     */
    public function build(): Builder;

    /**
     * Get the query definition.
     */
    public function definition(): QueryDefinition;
}
