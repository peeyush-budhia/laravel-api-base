<?php

declare(strict_types=1);

namespace Tests\Unit\Query;

use App\Models\User;
use App\Query\Contracts\QueryContract;
use App\Query\Contracts\QueryDefinition;
use App\Query\GenericQueryDefinition;
use App\Query\QueryExecutor;
use App\Query\QueryParameters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class QueryExecutorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_executes_a_query_contract(): void
    {
        User::factory()->count(3)->create();

        $query = app(TestQuery::class);

        $result = app(QueryExecutor::class)->paginate(
            $query,
            new QueryParameters(perPage: 2),
        );

        $this->assertCount(2, $result->items());
        $this->assertSame(3, $result->total());
        $this->assertSame(2, $result->perPage());
    }

    public function test_it_accepts_any_query_contract_implementation(): void
    {
        User::factory()->count(4)->create();

        $query = new TestQuery;

        $result = app(QueryExecutor::class)->paginate(
            $query,
            new QueryParameters(perPage: 3),
        );

        $this->assertCount(3, $result->items());
        $this->assertSame(4, $result->total());
    }

    public function test_it_applies_the_query_definition(): void
    {
        User::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice@example.com',
        ]);

        User::factory()->create([
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'email' => 'bob@example.com',
        ]);

        $query = new TestQuery(
            new GenericQueryDefinition(
                searchable: ['first_name'],
            ),
        );

        $result = app(QueryExecutor::class)->paginate(
            $query,
            new QueryParameters(
                search: 'Alice',
                perPage: 10,
            ),
        );

        $this->assertSame(1, $result->total());
        $this->assertSame('Alice', $result->first()->first_name);
    }
}

/**
 * Test-only implementation proving that QueryExecutor
 * depends on QueryContract rather than UserQuery.
 */
final class TestQuery implements QueryContract
{
    public function __construct(
        private readonly QueryDefinition $definition = new GenericQueryDefinition,
    ) {}

    public function build(): Builder
    {
        return User::query();
    }

    public function definition(): QueryDefinition
    {
        return $this->definition;
    }
}
