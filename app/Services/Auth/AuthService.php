<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    /**
     * Authenticate a user and generate a Sanctum token.
     *
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $login = $credentials['login'];
        $password = $credentials['password'];

        $user = $this->findUser($login);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => [__('auth.failed')],
            ]);
        }

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        return [
            'user' => $user,
            'token' => $this->createToken($user),
        ];
    }

    /**
     * Logout the authenticated user.
     */
    public function logout(User $user): void
    {
        /** @var PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();

        $token?->delete();
    }

    /**
     * Get the authenticated user.
     */
    public function me(User $user): User
    {
        return $user;
    }

    /**
     * Find a user by email or phone number.
     */
    private function findUser(string $login): ?User
    {
        return User::query()
            ->where('email', $login)
            ->first();
    }

    /**
     * Determine whether login is email or phone.
     */
    // private function getLoginField(string $login): string
    // {
    //     return filter_var($login, FILTER_VALIDATE_EMAIL)
    //         ? 'email'
    //         : 'phone';
    // }

    /**
     * Create a Sanctum access token.
     */
    private function createToken(User $user): string
    {
        return $user
            ->createToken(config('app.name'))
            ->plainTextToken;
    }

    /**
     * Send a password reset link.
     */
    public function forgotPassword(string $email): void
    {
        Password::sendResetLink([
            'email' => $email,
        ]);
    }

    /**
     * Reset the user's password.
     *
     * @throws ValidationException
     */
    public function resetPassword(
        array $credentials,
    ): void {
        $status = Password::reset(
            $credentials,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                    'must_change_password' => false,
                ])->save();

                // Invalidate all existing Sanctum sessions after a
                // successful password reset.
                $user->tokens()->delete();
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [
                    __($status),
                ],
            ]);
        }
    }

    /**
     * Change the authenticated user's password.
     *
     * @throws ValidationException
     */
    public function changePassword(
        User $user,
        array $credentials,
    ): void {
        if (! Hash::check(
            $credentials['current_password'],
            $user->password,
        )) {
            throw ValidationException::withMessages([
                'current_password' => [
                    __('auth.current_password'),
                ],
            ]);
        }

        $user->forceFill([
            'password' => $credentials['password'],
            'must_change_password' => false,
            'remember_token' => Str::random(60),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();
    }
}
