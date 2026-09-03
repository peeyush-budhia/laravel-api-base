<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\User;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use App\Policies\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('users.update') ?? false;

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
                'sometimes',
                'string',
                'max:100',
            ],

            'last_name' => [
                'sometimes',
                'string',
                'max:100',
            ],

            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')
                    ->ignore($this->route('user')),
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

            'password' => [
                'sometimes',
                'confirmed',
                app(PasswordPolicy::class)->validationRule(),
            ],
        ];
    }
}
