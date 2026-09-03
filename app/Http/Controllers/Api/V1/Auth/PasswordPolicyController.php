<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\BaseApiController;
use App\Policies\PasswordPolicy;
use Illuminate\Http\JsonResponse;

class PasswordPolicyController extends BaseApiController
{
    /**
     * Get the password requirements used by password forms.
     */
    public function show(PasswordPolicy $passwordPolicy): JsonResponse
    {
        return $this->success(
            $passwordPolicy->configuration(),
            message: __('responses.password_policy'),
        );
    }
}
