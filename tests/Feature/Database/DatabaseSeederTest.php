<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_seeder_does_not_create_demo_users_in_production(): void
    {
        $this->app['env'] = 'production';

        new DatabaseSeeder()
            ->setContainer($this->app)
            ->run();

        $this->assertSame(1, Role::query()->count());
        $this->assertTrue(
            Role::query()->where('name', RoleEnum::SUPER_ADMIN->value)->exists(),
        );
        $this->assertSame(
            count(PermissionEnum::cases()),
            Role::query()
                ->where('name', RoleEnum::SUPER_ADMIN->value)
                ->firstOrFail()
                ->permissions()
                ->count(),
        );
        $this->assertFalse(
            Role::query()->where('name', DemoRolesSeeder::ADMIN_ROLE)->exists(),
        );
        $this->assertFalse(
            Role::query()->where('name', 'user')->exists(),
        );
        $this->assertSame(count(PermissionEnum::cases()), Permission::query()->count());
        $this->assertSame(0, User::query()->count());
    }

    public function test_local_seeder_creates_limited_demo_admin_role(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(2, Role::query()->count());

        $admin = Role::query()
            ->where('name', DemoRolesSeeder::ADMIN_ROLE)
            ->firstOrFail();

        $this->assertSame(
            [
                PermissionEnum::AUDIT_LOGS_VIEW->value,
                PermissionEnum::DASHBOARD_VIEW->value,
                PermissionEnum::ROLES_VIEW->value,
                PermissionEnum::USERS_CREATE->value,
                PermissionEnum::USERS_UPDATE->value,
                PermissionEnum::USERS_VIEW->value,
            ],
            $admin->permissions()->pluck('name')->sort()->values()->all(),
        );
        $this->assertFalse(
            Role::query()->where('name', 'user')->exists(),
        );
        $this->assertFalse(
            User::query()
                ->where('email', 'user@example.com')
                ->firstOrFail()
                ->roles()
                ->exists(),
        );
    }

    public function test_demo_seeding_does_not_reset_existing_passwords_or_add_users_twice(): void
    {
        $this->seed(DatabaseSeeder::class);

        $superAdmin = User::query()
            ->where('email', 'super-admin@example.com')
            ->firstOrFail();

        $superAdmin->update([
            'password' => 'LocallyChangedPassword123!',
        ]);

        $initialUserCount = User::query()->count();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($initialUserCount, User::query()->count());
        $this->assertTrue(
            Hash::check(
                'LocallyChangedPassword123!',
                $superAdmin->fresh()->password,
            ),
        );
    }
}
