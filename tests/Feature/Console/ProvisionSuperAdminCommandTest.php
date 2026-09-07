<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class ProvisionSuperAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_securely_provisions_the_initial_super_admin(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->artisan('app:provision-super-admin', [
            'email' => 'owner@example.com',
            '--first-name' => 'Primary',
            '--last-name' => 'Owner',
        ])
            ->expectsQuestion('Password', 'SecurePassword123!')
            ->expectsQuestion('Confirm password', 'SecurePassword123!')
            ->assertSuccessful();

        $user = User::query()
            ->where('email', 'owner@example.com')
            ->firstOrFail();

        $this->assertSame('Primary', $user->first_name);
        $this->assertSame('Owner', $user->last_name);
        $this->assertSame(UserStatus::ACTIVE, $user->status);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('SecurePassword123!', $user->password));
        $this->assertTrue($user->hasRole(Role::SUPER_ADMIN->value));
    }

    public function test_it_refuses_to_create_a_second_super_admin(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $existing = User::factory()->create();
        $existing->assignRole(Role::SUPER_ADMIN->value);

        $this->artisan('app:provision-super-admin', [
            'email' => 'second@example.com',
            '--first-name' => 'Second',
            '--last-name' => 'Admin',
        ])->assertFailed();

        $this->assertDatabaseMissing('users', [
            'email' => 'second@example.com',
        ]);
    }

    public function test_it_rejects_a_password_that_does_not_meet_the_policy(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->artisan('app:provision-super-admin', [
            'email' => 'owner@example.com',
            '--first-name' => 'Primary',
            '--last-name' => 'Owner',
        ])
            ->expectsQuestion('Password', 'weak')
            ->expectsQuestion('Confirm password', 'weak')
            ->assertExitCode(2);

        $this->assertDatabaseMissing('users', [
            'email' => 'owner@example.com',
        ]);
    }

    public function test_it_requires_the_baseline_roles_to_be_seeded(): void
    {
        $this->artisan('app:provision-super-admin', [
            'email' => 'owner@example.com',
            '--first-name' => 'Primary',
            '--last-name' => 'Owner',
        ])->assertFailed();

        $this->assertDatabaseMissing('users', [
            'email' => 'owner@example.com',
        ]);
    }
}
