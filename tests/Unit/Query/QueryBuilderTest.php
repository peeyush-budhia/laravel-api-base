<?php

declare(strict_types=1);

namespace Tests\Unit\Query;

use App\Models\User;
use App\Query\GenericQueryDefinition;
use App\Query\QueryBuilder;
use App\Query\QueryParameters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class QueryBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_applies_search_across_searchable_fields(): void
    {
        User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
        ]);

        $definition = new GenericQueryDefinition(
            searchable: [
                'first_name',
                'last_name',
                'email',
            ],
        );

        $query = app(QueryBuilder::class)->apply(
            User::query(),
            new QueryParameters(search: 'john'),
            $definition,
        );

        $this->assertCount(1, $query->get());
    }

    public function test_it_applies_allowed_filters(): void
    {
        User::factory()->count(2)->create([
            'status' => 'active',
        ]);

        User::factory()->create([
            'status' => 'inactive',
        ]);

        $definition = new GenericQueryDefinition(
            filterable: ['status'],
        );

        $query = app(QueryBuilder::class)->apply(
            User::query(),
            new QueryParameters(
                filters: ['status' => 'active'],
            ),
            $definition,
        );

        $this->assertCount(2, $query->get());
    }

    public function test_it_ignores_unsupported_filters(): void
    {
        User::factory()->count(3)->create();

        $definition = new GenericQueryDefinition(
            filterable: ['status'],
        );

        $query = app(QueryBuilder::class)->apply(
            User::query(),
            new QueryParameters(
                filters: ['email' => 'unknown@example.com'],
            ),
            $definition,
        );

        $this->assertCount(3, $query->get());
    }

    public function test_it_applies_allowed_sorting(): void
    {
        User::factory()->create([
            'first_name' => 'Zack',
        ]);

        User::factory()->create([
            'first_name' => 'Alice',
        ]);

        $definition = new GenericQueryDefinition(
            sortable: ['first_name'],
        );

        $query = app(QueryBuilder::class)->apply(
            User::query(),
            new QueryParameters(
                sort: 'first_name',
                direction: 'asc',
            ),
            $definition,
        );

        $users = $query->get();

        $this->assertSame('Alice', $users->first()->first_name);
        $this->assertSame('Zack', $users->last()->first_name);
    }

    public function test_it_applies_descending_sorting(): void
    {
        User::factory()->create([
            'first_name' => 'Alice',
        ]);

        User::factory()->create([
            'first_name' => 'Zack',
        ]);

        $definition = new GenericQueryDefinition(
            sortable: ['first_name'],
        );

        $query = app(QueryBuilder::class)->apply(
            User::query(),
            new QueryParameters(
                sort: 'first_name',
                direction: 'desc',
            ),
            $definition,
        );

        $users = $query->get();

        $this->assertSame('Zack', $users->first()->first_name);
        $this->assertSame('Alice', $users->last()->first_name);
    }

    public function test_it_ignores_unsupported_sorting(): void
    {
        User::factory()->create([
            'first_name' => 'Zack',
        ]);

        User::factory()->create([
            'first_name' => 'Alice',
        ]);

        $definition = new GenericQueryDefinition(
            sortable: ['first_name'],
        );

        $query = app(QueryBuilder::class)->apply(
            User::query(),
            new QueryParameters(
                sort: 'email',
                direction: 'desc',
            ),
            $definition,
        );

        $this->assertCount(2, $query->get());
    }

    public function test_it_can_apply_search_filter_and_sort_together(): void
    {
        User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'status' => 'active',
        ]);

        User::factory()->create([
            'first_name' => 'Johnny',
            'last_name' => 'Doe',
            'email' => 'johnny@example.com',
            'status' => 'active',
        ]);

        User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'status' => 'inactive',
        ]);

        $definition = new GenericQueryDefinition(
            searchable: [
                'first_name',
                'last_name',
                'email',
            ],
            sortable: ['first_name'],
            filterable: ['status'],
        );

        $query = app(QueryBuilder::class)->apply(
            User::query(),
            new QueryParameters(
                search: 'john',
                sort: 'first_name',
                direction: 'asc',
                filters: [
                    'status' => 'active',
                ],
            ),
            $definition,
        );

        $users = $query->get();

        $this->assertCount(2, $users);
        $this->assertSame('John', $users->first()->first_name);
        $this->assertSame('Johnny', $users->last()->first_name);
    }
}
