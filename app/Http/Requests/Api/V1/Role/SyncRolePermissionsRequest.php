<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Role;

use App\Models\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'permissions' => [
                'present',
                'array',
            ],

            'permissions.*' => [
                'required',
                'string',
                Rule::exists(Permission::class, 'name')
                    ->where('guard_name', 'sanctum'),
            ],
        ];
    }
}
