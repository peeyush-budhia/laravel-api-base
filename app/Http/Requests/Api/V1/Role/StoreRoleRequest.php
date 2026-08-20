<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Role;

use App\Enums\Role as EnumsRole;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('roles.create') ?? false;

    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('name')) {
            $this->merge([
                'name' => Str::slug((string) $this->input('name')),
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'not_in:'.EnumsRole::SUPER_ADMIN->value,
                Rule::unique(Role::class, 'name')
                    ->where('guard_name', 'sanctum'),
            ],
        ];
    }
}
