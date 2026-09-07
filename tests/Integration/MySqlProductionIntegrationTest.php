<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Enums\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class MySqlProductionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (
            ! filter_var(env('RUN_MYSQL_INTEGRATION_TESTS', false), FILTER_VALIDATE_BOOL)
            || $this->app->get('db')->getDriverName() !== 'mysql'
        ) {
            $this->markTestSkipped('Set RUN_MYSQL_INTEGRATION_TESTS=true with a MySQL test database to run integration tests.');
        }

        Notification::fake();
    }

    public function test_onboarding_flow_works_end_to_end_on_mysql(): void
    {
        $admin = User::factory()->create([
            'email' => 'mysql-admin@example.com',
            'password' => Hash::make('AdminPassword123!'),
        ]);
        $admin->assignRole(Role::query()->where('name', RoleEnum::SUPER_ADMIN->value)->firstOrFail());

        $token = $this->postJson('/api/v1/auth/login', [
            'login' => $admin->email,
            'password' => 'AdminPassword123!',
        ])->assertOk()->json('data.token');

        $this->withToken($token)->postJson('/api/v1/users', [
            'first_name' => 'MySQL', 'last_name' => 'Integration',
            'email' => 'mysql-user@example.com', 'role' => RoleEnum::ADMIN->value,
        ])->assertCreated();

        $this->assertDatabaseHas('users', ['email' => 'mysql-user@example.com']);
        Notification::assertSentTo(User::query()->where('email', 'mysql-user@example.com')->firstOrFail());
    }

    public function test_concurrent_super_admin_creation_leaves_one_super_admin(): void
    {
        if (! function_exists('pcntl_fork')) {
            $this->markTestSkipped('The pcntl extension is required for the concurrency test.');
        }

        Role::query()->where('name', RoleEnum::SUPER_ADMIN->value)->delete();
        Role::create(['name' => RoleEnum::SUPER_ADMIN->value, 'guard_name' => 'sanctum']);
        $barrier = tempnam(sys_get_temp_dir(), 'laravel-mysql-race-');
        $children = [];

        for ($index = 0; $index < 2; $index++) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                $this->fail('Unable to fork a concurrency test process.');
            }
            if ($pid === 0) {
                file_put_contents($barrier, '1', FILE_APPEND | LOCK_EX);
                while (filesize($barrier) < 2) {
                    usleep(10_000);
                }
                try {
                    app('db')->purge();
                    app(UserService::class)->store([
                        'first_name' => 'Race', 'last_name' => 'User',
                        'email' => sprintf('race-%d@example.com', getmypid()),
                        'role' => RoleEnum::SUPER_ADMIN->value,
                    ]);
                    exit(0);
                } catch (\Throwable) {
                    exit(1);
                }
            }
            $children[] = $pid;
        }

        foreach ($children as $child) {
            pcntl_waitpid($child, $status);
        }
        @unlink($barrier);
        $this->assertSame(1, User::role(RoleEnum::SUPER_ADMIN->value, 'sanctum')->count());
    }
}
