<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

final class DemoUserSeeder extends Seeder
{
    /**
     * Seed accounts intended only for local development and testing.
     */
    public function run(): void
    {
        if (! $this->container->environment('local', 'testing')) {
            return;
        }

        $superAdmin = $this->createDemoUser(
            email: 'super-admin@example.com',
            firstName: 'Super',
            lastName: 'Admin',
        );

        $admin = $this->createDemoUser(
            email: 'admin@example.com',
            firstName: 'Admin',
            lastName: 'User',
        );

        $user = $this->createDemoUser(
            email: 'user@example.com',
            firstName: 'Normal',
            lastName: 'User',
        );

        $superAdmin->assignRole(Role::SUPER_ADMIN->value);
        $admin->assignRole(Role::ADMIN->value);
        $user->assignRole(Role::USER->value);

        if (User::query()->count() === 3) {
            User::factory()->count(7)->create();
        }
    }

    private function createDemoUser(
        string $email,
        string $firstName,
        string $lastName,
    ): User {
        $user = User::query()->firstOrNew([
            'email' => $email,
        ]);

        if (! $user->exists) {
            $user->forceFill([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'avatar' => null,
                'status' => UserStatus::ACTIVE,
                'email_verified_at' => null,
                'password' => 'password',
            ])->save();
        }

        return $user;
    }
}
