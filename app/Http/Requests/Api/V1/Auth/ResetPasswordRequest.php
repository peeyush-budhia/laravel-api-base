<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Policies\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
            'token' => [
                'required',
                'string',
            ],

            'email' => [
                'required',
                'email',
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
