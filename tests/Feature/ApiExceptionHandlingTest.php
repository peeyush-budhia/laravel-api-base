<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class ApiExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_authentication_errors_are_json_without_accept_header(): void
    {
        $response = $this->get('/api/v1/users');

        $response
            ->assertUnauthorized()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 401)
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'data',
                'errors',
                'meta',
            ]);
    }

    public function test_api_method_not_allowed_errors_are_json_and_preserve_allow_header(): void
    {
        $response = $this->post('/api/v1/health');

        $response
            ->assertStatus(405)
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeader('Allow', 'GET, HEAD')
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 405);
    }

    public function test_api_throttle_errors_are_json_and_preserve_retry_after_header(): void
    {
        $user = User::factory()->create([
            'email' => 'exception-throttle@example.com',
            'password' => Hash::make('password'),
        ]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables([
                'REMOTE_ADDR' => "198.18.0.{$attempt}",
            ])->post('/api/v1/auth/login', [
                'login' => $user->email,
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '198.18.0.6',
        ])->post('/api/v1/auth/login', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response
            ->assertTooManyRequests()
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeader('Retry-After')
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 429);
    }
}
