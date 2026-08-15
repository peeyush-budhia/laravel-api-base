<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\User;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('users.create') ?? false;
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'avatar' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'sometimes',
                Rule::enum(UserStatus::class),
            ],

            'role' => [
                'required',
                Rule::exists(Role::class, 'name')
                    ->where('guard_name', 'sanctum'),
            ],
        ];
    }
}
