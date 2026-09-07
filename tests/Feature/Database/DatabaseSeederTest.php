<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
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

        $this->assertSame(3, Role::query()->count());
        $this->assertSame(0, User::query()->count());
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
