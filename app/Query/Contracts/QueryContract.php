<?php

declare(strict_types=1);

namespace App\Query\Contracts;

use App\Query\QueryParameters;
use Illuminate\Database\Eloquent\Builder;

interface QueryContract
{
    /**
     * Build the query using the supplied parameters.
     */
    public function build(QueryParameters $parameters): Builder;
}
