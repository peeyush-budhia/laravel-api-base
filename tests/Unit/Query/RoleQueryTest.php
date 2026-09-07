<?php

declare(strict_types=1);

namespace Tests\Unit\Query;

use App\Query\QueryParameters;
use App\Query\RoleQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RoleQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_selects_only_the_columns_used_by_the_api(): void
    {
        $query = app(RoleQuery::class)->build(
            new QueryParameters,
        );

        $this->assertSame([
            'id',
            'name',
            'guard_name',
            'created_at',
            'updated_at',
        ], $query->getQuery()->columns);
    }
}
