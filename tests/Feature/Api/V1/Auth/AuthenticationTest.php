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

    public function test_login_accepts_remember_me_true(): void
    {
        $user = User::factory()->create([
            'email' => 'remember@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson(
            '/api/v1/auth/login',
            [
                'login' => $user->email,
                'password' => 'password',
                'remember_me' => true,
            ],
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'token',
                ],
            ]);
    }

    public function test_login_accepts_remember_me_false(): void
    {
        $user = User::factory()->create([
            'email' => 'no-remember@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson(
            '/api/v1/auth/login',
            [
                'login' => $user->email,
                'password' => 'password',
                'remember_me' => false,
            ],
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200);
    }

    public function test_login_accepts_remember_me_as_optional(): void
    {
        $user = User::factory()->create([
            'email' => 'optional@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson(
            '/api/v1/auth/login',
            [
                'login' => $user->email,
                'password' => 'password',
            ],
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200);
    }

    public function test_login_rejects_invalid_remember_me_value(): void
    {
        $user = User::factory()->create([
            'email' => 'invalid@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson(
            '/api/v1/auth/login',
            [
                'login' => $user->email,
                'password' => 'password',
                'remember_me' => 'invalid',
            ],
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 422)
            ->assertJsonValidationErrors([
                'remember_me',
            ]);
    }
}
