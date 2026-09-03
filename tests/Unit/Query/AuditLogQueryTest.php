<?php

declare(strict_types=1);

namespace Tests\Unit\Query;

use App\Query\AuditLogQuery;
use App\Query\QueryParameters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuditLogQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_eager_loads_the_actor_user_and_user_permissions(): void
    {
        $query = app(AuditLogQuery::class)->build(
            new QueryParameters,
        );

        $this->assertArrayHasKey('user.roles', $query->getEagerLoads());
        $this->assertArrayHasKey('user.permissions', $query->getEagerLoads());
        $this->assertSame([
            'id',
            'user_id',
            'event',
            'auditable_type',
            'auditable_id',
            'old_values',
            'new_values',
            'url',
            'ip_address',
            'user_agent',
            'created_at',
            'updated_at',
        ], $query->getQuery()->columns);
    }
}
