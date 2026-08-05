<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends BaseApiController
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Authenticate a user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return $this->success(
            data: [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
            message: __('responses.login_success'),
        );
    }

    /**
     * Logout the authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->authService->logout($user);

        return $this->success(
            message: __('responses.logout_success'),
        );
    }

    /**
     * Return the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->success(
            data: new UserResource(
                $this->authService->me($user)
            ),
        );
    }
}
