<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\BaseApiController;
use App\Policies\PasswordPolicy;

class PasswordPolicyController extends BaseApiController
{
    public function show(PasswordPolicy $passwordPolicy)
    {
        return $this->success(
            $passwordPolicy->rules(),
            message: __('responses.password_policy'),
        );
    }
}
