<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use App\Policies\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;

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
        $passwordRule = app(PasswordPolicy::class)->validationRule();

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
