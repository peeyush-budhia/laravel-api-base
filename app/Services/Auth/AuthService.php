<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Services\BaseService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService extends BaseService
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

        // Single active session
        $user->tokens()->delete();

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
            ->where(
                $this->getLoginField($login),
                $login
            )
            ->first();
    }

    /**
     * Determine whether login is email or phone.
     */
    private function getLoginField(string $login): string
    {
        return filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'phone';
    }

    /**
     * Create a Sanctum access token.
     */
    private function createToken(User $user): string
    {
        return $user
            ->createToken(config('app.name'))
            ->plainTextToken;
    }
}
