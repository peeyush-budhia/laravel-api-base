<?php

declare(strict_types=1);

namespace App\Query;

use App\Constants\PaginationConstants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
        $input = $request->query();
        $controlParameters = [
            'page',
            'per_page',
            'search',
            'sort',
            'direction',
            'trashed',
        ];

        $rules = [
            'page' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', 'max:100'],
            'direction' => [
                'nullable',
                'string',
                Rule::in(['asc', 'desc', 'ASC', 'DESC']),
            ],
            'trashed' => [
                'nullable',
                'string',
                Rule::in([
                    'without',
                    'only',
                    'with',
                    'WITHOUT',
                    'ONLY',
                    'WITH',
                ]),
            ],
        ];

        foreach (array_diff_key($input, array_flip($controlParameters)) as $key => $value) {
            $rules[$key] = ['nullable', 'string', 'max:255'];
        }

        Validator::make($input, $rules)->validate();

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
