<?php

declare(strict_types=1);

namespace Tests\Unit\Query;

use App\Query\QueryParameters;
use App\Query\UserQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_eager_loads_roles_and_permissions(): void
    {
        $query = app(UserQuery::class)->build(
            new QueryParameters,
        );

        $this->assertArrayHasKey('roles', $query->getEagerLoads());
        $this->assertArrayHasKey('permissions', $query->getEagerLoads());
        $this->assertSame([
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
        ], $query->getQuery()->columns);
    }
}
