<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $superAdminUser = User::query()->updateOrCreate(
            [
                'email' => 'admin@example.com',
            ],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',

                'avatar' => null,
                'status' => UserStatus::ACTIVE,

                'email_verified_at' => now(),

                'password' => 'password',
            ]
        );

        $adminUser = User::query()->updateOrCreate(
            [
                'email' => 'user@example.com',
            ],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',

                'avatar' => null,
                'status' => UserStatus::ACTIVE,

                'email_verified_at' => now(),

                'password' => 'password',

                'must_change_password' => true,
            ]
        );

        $user = User::query()->updateOrCreate(
            [
                'email' => 'user@example.com',
            ],
            [
                'first_name' => 'Normal',
                'last_name' => 'User',

                'avatar' => null,
                'status' => UserStatus::ACTIVE,

                'email_verified_at' => now(),

                'password' => 'password',

                'must_change_password' => true,
            ]
        );

        $this->call([
            RolePermissionSeeder::class,
        ]);

        $superAdminUser->assignRole(Role::SUPER_ADMIN->value);
        $adminUser->assignRole(Role::ADMIN->value);
        $user->assignRole(Role::USER->value);

        User::factory()->count(20)->create();
    }
}
