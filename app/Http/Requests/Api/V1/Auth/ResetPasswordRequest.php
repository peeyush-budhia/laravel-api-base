<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Policies\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $passwordRule = app(PasswordPolicy::class)->validationRule();

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
