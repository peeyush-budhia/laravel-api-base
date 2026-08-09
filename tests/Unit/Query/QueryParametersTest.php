<?php

declare(strict_types=1);

namespace Tests\Unit\Query;

use App\Query\QueryParameters;
use Illuminate\Http\Request;
use Tests\TestCase;

final class QueryParametersTest extends TestCase
{
    public function test_it_uses_default_pagination_values(): void
    {
        $parameters = QueryParameters::fromRequest(
            Request::create('/users', 'GET'),
        );

        $this->assertSame(1, $parameters->page);
        $this->assertSame(15, $parameters->perPage);
    }

    public function test_it_normalizes_pagination_values(): void
    {
        $request = Request::create('/users', 'GET', [
            'page' => -5,
            'per_page' => -10,
        ]);

        $parameters = QueryParameters::fromRequest($request);

        $this->assertSame(1, $parameters->page);
        $this->assertSame(1, $parameters->perPage);
    }

    public function test_it_trims_search_and_sort_values(): void
    {
        $request = Request::create('/users', 'GET', [
            'search' => '  john  ',
            'sort' => '  first_name  ',
        ]);

        $parameters = QueryParameters::fromRequest($request);

        $this->assertSame('john', $parameters->search);
        $this->assertSame('first_name', $parameters->sort);
    }

    public function test_it_converts_empty_search_and_sort_to_null(): void
    {
        $request = Request::create('/users', 'GET', [
            'search' => '   ',
            'sort' => '   ',
        ]);

        $parameters = QueryParameters::fromRequest($request);

        $this->assertNull($parameters->search);
        $this->assertNull($parameters->sort);
    }

    public function test_it_normalizes_direction(): void
    {
        $request = Request::create('/users', 'GET', [
            'direction' => 'DESC',
        ]);

        $parameters = QueryParameters::fromRequest($request);

        $this->assertSame('desc', $parameters->direction);
    }

    public function test_it_defaults_invalid_direction_to_ascending(): void
    {
        $request = Request::create('/users', 'GET', [
            'direction' => 'invalid',
        ]);

        $parameters = QueryParameters::fromRequest($request);

        $this->assertSame('asc', $parameters->direction);
    }

    public function test_it_extracts_filters(): void
    {
        $request = Request::create('/users', 'GET', [
            'status' => 'active',
            'role' => 'admin',
            'page' => 2,
            'per_page' => 25,
            'search' => 'john',
            'sort' => 'first_name',
            'direction' => 'desc',
        ]);

        $parameters = QueryParameters::fromRequest($request);

        $this->assertSame([
            'status' => 'active',
            'role' => 'admin',
        ], $parameters->filters);
    }

    public function test_it_excludes_query_control_parameters_from_filters(): void
    {
        $request = Request::create('/users', 'GET', [
            'page' => 2,
            'per_page' => 25,
            'search' => 'john',
            'sort' => 'first_name',
            'direction' => 'desc',
        ]);

        $parameters = QueryParameters::fromRequest($request);

        $this->assertSame([], $parameters->filters);
    }
}
