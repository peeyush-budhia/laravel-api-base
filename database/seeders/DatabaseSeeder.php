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
        $user = User::query()->updateOrCreate(
            [
                'email' => 'peeyush@example.com',
            ],
            [
                'first_name' => 'Peeyush',
                'last_name' => 'Budhia',

                'avatar' => null,
                'status' => UserStatus::ACTIVE,

                'email_verified_at' => now(),

                'password' => 'password',
            ]
        );

        $this->call([
            RolePermissionSeeder::class,
        ]);

        $user->assignRole(Role::SUPER_ADMIN->value);

        User::factory()->count(25)->create();
    }
}
