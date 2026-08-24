<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Audit;

use App\Query\QueryParameters;
use Illuminate\Foundation\Http\FormRequest;

final class AuditLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('audit-logs.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'page' => [
                'sometimes',
                'integer',
                'min:1',
            ],

            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
            ],

            'search' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'sort' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'direction' => [
                'sometimes',
                'nullable',
                'in:asc,desc',
            ],

            'event' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'user_id' => [
                'sometimes',
                'nullable',
                'uuid',
            ],

            'auditable_type' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'auditable_id' => [
                'sometimes',
                'nullable',
                'uuid',
            ],
        ];
    }

    public function queryParameters(): QueryParameters
    {
        return QueryParameters::fromRequest($this);
    }
}
