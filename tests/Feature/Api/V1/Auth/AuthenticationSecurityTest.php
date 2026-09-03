<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
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
        ]);
        $auditLogCount = AuditLog::query()->count();

        $this->postJson('/api/v1/auth/login', [
            'login' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertSame($auditLogCount, AuditLog::query()->count());
    }

    public function test_user_must_change_password_before_accessing_protected_modules(): void
    {
        $user = User::factory()->mustChangePassword()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/users')
            ->assertForbidden()
            ->assertJsonPath(
                'message',
                __('responses.password_change_required'),
            );
    }

    public function test_user_required_to_change_password_can_access_me(): void
    {
        $user = User::factory()->mustChangePassword()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_changing_password_removes_forced_password_restriction(): void
    {
        $user = User::factory()->mustChangePassword()->create([
            'password' => Hash::make('old-password'),
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'old-password',
            'password' => 'New-password-123!',
            'password_confirmation' => 'New-password-123!',
        ])->assertOk();

        $this->assertFalse($user->fresh()->must_change_password);
    }
}
