<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use App\Policies\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $passwordPolicy = app(PasswordPolicy::class);

        $passwordRule = Password::min($passwordPolicy->minLength());

        if ($passwordPolicy->requiresMixedCase()) {
            $passwordRule->mixedCase();
        }

        if ($passwordPolicy->requiresNumbers()) {
            $passwordRule->numbers();
        }

        if ($passwordPolicy->requiresSymbols()) {
            $passwordRule->symbols();
        }

        return [
            'current_password' => [
                'required',
                'current_password',
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
                $passwordRule,
            ],

            'password_confirmation' => [
                'required',
                'string',
            ],
        ];
    }
}
