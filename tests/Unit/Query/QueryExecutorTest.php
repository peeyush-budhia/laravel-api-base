<?php

declare(strict_types=1);

namespace Tests\Unit\Query;

use App\Models\User;
use App\Query\QueryExecutor;
use App\Query\QueryParameters;
use App\Query\UserQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class QueryExecutorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_executes_a_query_contract(): void
    {
        User::factory()->count(3)->create();

        $query = app(UserQuery::class);

        $result = app(QueryExecutor::class)->paginate(
            $query,
            new QueryParameters(perPage: 2),
        );

        $this->assertCount(2, $result->items());
        $this->assertSame(3, $result->total());
        $this->assertSame(2, $result->perPage());
    }
}
