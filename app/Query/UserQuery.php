<?php

declare(strict_types=1);

namespace App\Query;

use App\Models\User;
use App\Query\Contracts\QueryDefinition;
use Illuminate\Database\Eloquent\Builder;

final class UserQuery
{
    private readonly GenericQueryDefinition $definition;

    public function __construct()
    {
        $this->definition = new GenericQueryDefinition(
            searchable: [
                'first_name',
                'last_name',
                'email',
            ],
            sortable: [
                'first_name',
                'last_name',
                'email',
                'created_at',
                'updated_at',
            ],
            filterable: [
                'status',
            ],
        );
    }

    /**
     * Get the query definition.
     */
    public function definition(): QueryDefinition
    {
        return $this->definition;
    }

    /**
     * Get the base user query.
     */
    public function query(): Builder
    {
        return User::query();
    }
}
