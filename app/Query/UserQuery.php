<?php

declare(strict_types=1);

namespace App\Query;

use App\Models\User;
use App\Query\Contracts\QueryContract;
use App\Query\Contracts\QueryDefinition;
use Illuminate\Database\Eloquent\Builder;

final class UserQuery implements QueryContract
{
    /**
     * Get the query definition.
     */
    public function definition(): QueryDefinition
    {
        return new GenericQueryDefinition(
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
     * Build user query.
     */
    public function build(
        QueryParameters $parameters,
    ): Builder {
        $query = User::query()->select([
            'id',
            'first_name',
            'last_name',
            'email',
            'avatar',
            'status',
            'email_verified_at',
            'last_login_at',
            'must_change_password',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $query->with([
            'roles',
            'permissions',
        ]);

        return match ($parameters->trashed) {
            'only' => $query->onlyTrashed(),
            'with' => $query->withTrashed(),
            default => $query->withoutTrashed(),
        };
    }
}
