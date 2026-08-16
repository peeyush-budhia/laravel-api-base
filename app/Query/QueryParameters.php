<?php

declare(strict_types=1);

namespace App\Query;

use App\Constants\PaginationConstants;
use Illuminate\Http\Request;

final readonly class QueryParameters
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public int $page = 1,
        public int $perPage = PaginationConstants::DEFAULT_PER_PAGE,
        public ?string $search = null,
        public ?string $sort = null,
        public string $direction = 'asc',
        public ?string $trashed = 'without',
        public array $filters = [],
    ) {}

    public static function fromRequest(Request $request): self
    {
        $page = max(
            1,
            $request->integer('page', 1),
        );

        $perPage = max(
            PaginationConstants::MIN_PER_PAGE,
            min(
                $request->integer(
                    'per_page',
                    PaginationConstants::DEFAULT_PER_PAGE,
                ),
                PaginationConstants::MAX_PER_PAGE,
            ),
        );

        $search = $request->filled('search')
            ? trim((string) $request->input('search'))
            : null;

        $sort = $request->filled('sort')
            ? trim((string) $request->input('sort'))
            : null;

        $direction = strtolower(
            (string) $request->input('direction', 'asc'),
        );

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $trashed = strtolower(
            trim((string) $request->input('trashed', 'without')),
        );

        if (! in_array($trashed, ['without', 'only', 'with'], true)) {
            $trashed = 'without';
        }

        $filters = $request->except([
            'page',
            'per_page',
            'search',
            'sort',
            'direction',
            'trashed',
        ]);

        return new self(
            page: $page,
            perPage: $perPage,
            search: $search !== '' ? $search : null,
            sort: $sort !== '' ? $sort : null,
            direction: $direction,
            trashed: $trashed,
            filters: $filters,
        );
    }
}
