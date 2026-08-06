<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\ApiTestCase;

class AuthenticationTest extends ApiTestCase
{
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson(
            '/api/v1/auth/login',
            [
                'login' => 'admin@example.com',
                'password' => 'password',
            ],
            $this->jsonHeaders(),
        );

        $response->assertOk();

        $this->assertApiSuccess($response);

        $response->assertJsonPath(
            'data.user.email',
            $user->email,
        );

        $response->assertJsonStructure([
            'success',
            'status',
            'message',
            'data' => [
                'user',
                'token',
            ],
            'errors',
            'meta',
        ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson(
            '/api/v1/auth/login',
            [
                'login' => 'admin@example.com',
                'password' => 'wrong-password',
            ],
            $this->jsonHeaders(),
        );

        $this->assertApiValidation($response);

        $response->assertJsonValidationErrors([
            'login',
        ]);
    }

    public function test_authenticated_user_can_fetch_profile(): void
    {
        $user = $this->authenticate();

        $response = $this->getJson(
            '/api/v1/auth/me',
            $this->jsonHeaders(),
        );

        $response->assertOk();

        $this->assertApiSuccess($response);

        $response->assertJsonPath(
            'data.id',
            $user->id,
        );

        $response->assertJsonPath(
            'data.email',
            $user->email,
        );
    }

    public function test_user_can_logout(): void
    {
        $this->authenticate();

        $response = $this->postJson(
            '/api/v1/auth/logout',
            [],
            $this->jsonHeaders(),
        );

        $response->assertOk();

        $this->assertApiSuccess($response);

    }

    public function test_guest_cannot_access_profile(): void
    {
        $response = $this->getJson(
            '/api/v1/auth/me',
            $this->jsonHeaders(),
        );

        $this->assertApiUnauthorized($response);
    }
}
