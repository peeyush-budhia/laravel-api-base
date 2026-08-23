<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Api\V1\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
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
     *
     * Returns a personal access token that can be used
     * to authenticate subsequent API requests.
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
     *
     * Revokes the current personal access token.
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
     * Get the authenticated user's profile.
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

    /**
     * Send a password reset link.
     */
    public function forgotPassword(
        ForgotPasswordRequest $request,
    ): JsonResponse {
        $this->authService->forgotPassword(
            $request->validated('email'),
        );

        return $this->success(
            null,
            __('responses.password_reset_link_sent'),
        );
    }

    /**
     * Reset a user's password using a valid reset token.
     */
    public function resetPassword(
        ResetPasswordRequest $request,
    ): JsonResponse {
        $this->authService->resetPassword(
            $request->validated(),
        );

        return $this->success(
            null,
            __('responses.password_reset_success'),
        );
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(
        ChangePasswordRequest $request,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $this->authService->changePassword(
            $user,
            $request->validated(),
        );

        return $this->success(
            message: __('responses.password_changed'),
        );
    }
}
