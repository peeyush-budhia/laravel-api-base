<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::INACTIVE,
            'password' => Hash::make('password'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'login' => $user->email,
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('login');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_suspended_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::SUSPENDED,
            'password' => Hash::make('password'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'login' => $user->email,
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('login');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_timestamp_update_is_not_audited(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $auditLogCount = AuditLog::query()->count();

        $this->postJson('/api/v1/auth/login', [
            'login' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertSame($auditLogCount, AuditLog::query()->count());
    }

    public function test_first_successful_login_does_not_verify_an_unverified_user(): void
    {
        $user = User::factory()->create([
            'email' => 'first-login@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => null,
            'last_login_at' => null,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'login' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_later_login_does_not_reverify_an_account_with_no_first_login_marker(): void
    {
        $previousLogin = now()->subDay()->startOfSecond();
        $user = User::factory()->create([
            'email' => 'existing-login@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => null,
            'last_login_at' => $previousLogin,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'login' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $this->assertNull($user->fresh()->email_verified_at);
    }
}
