<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AuthenticationRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_limited_by_account_across_ip_addresses(): void
    {
        $user = User::factory()->create([
            'email' => 'target@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables([
                'REMOTE_ADDR' => "198.51.100.{$attempt}",
            ])->postJson('/api/v1/auth/login', [
                'login' => $user->email,
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $this->withServerVariables([
            'REMOTE_ADDR' => '198.51.100.6',
        ])->postJson('/api/v1/auth/login', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertTooManyRequests()
            ->assertJsonPath('status', 429);
    }

    public function test_login_is_limited_by_ip_across_account_identifiers(): void
    {
        for ($attempt = 1; $attempt <= 20; $attempt++) {
            $this->withServerVariables([
                'REMOTE_ADDR' => '203.0.113.10',
            ])->postJson('/api/v1/auth/login', [
                'login' => "unknown-{$attempt}@example.com",
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.10',
        ])->postJson('/api/v1/auth/login', [
            'login' => 'another-user@example.com',
            'password' => 'wrong-password',
        ])
            ->assertTooManyRequests()
            ->assertJsonPath('status', 429);
    }

    public function test_forgot_password_is_limited_by_account(): void
    {
        Notification::fake();

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->withServerVariables([
                'REMOTE_ADDR' => "192.0.2.{$attempt}",
            ])->postJson('/api/v1/auth/forgot-password', [
                'email' => 'target@example.com',
            ])->assertOk();
        }

        $this->withServerVariables([
            'REMOTE_ADDR' => '192.0.2.4',
        ])->postJson('/api/v1/auth/forgot-password', [
            'email' => 'target@example.com',
        ])
            ->assertTooManyRequests()
            ->assertJsonPath('status', 429);
    }

    public function test_reset_password_is_limited_by_account(): void
    {
        User::factory()->create([
            'email' => 'target@example.com',
        ]);

        $payload = [
            'token' => Str::random(64),
            'email' => 'target@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ];

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables([
                'REMOTE_ADDR' => "203.0.113.{$attempt}",
            ])->postJson('/api/v1/auth/reset-password', $payload)
                ->assertUnprocessable();
        }

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.6',
        ])->postJson('/api/v1/auth/reset-password', $payload)
            ->assertTooManyRequests()
            ->assertJsonPath('status', 429);
    }

    public function test_malformed_account_identifier_is_validated_without_a_server_error(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'login' => ['target@example.com'],
            'password' => 'wrong-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('login');
    }
}
