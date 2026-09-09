<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Enums\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;
use App\Notifications\User\UserCreatedNotification;
use App\Services\User\UserService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class MySqlProductionIntegrationTest extends TestCase
{
    use DatabaseMigrations;

    private const int BARRIER_TIMEOUT_SECONDS = 10;

    private const int CHILDREN_TIMEOUT_SECONDS = 30;

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
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_onboarding_flow_works_end_to_end_on_mysql(): void
    {
        $targetRole = Role::create([
            'name' => 'manager',
            'guard_name' => 'sanctum',
        ]);
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
            'email' => 'mysql-user@example.com', 'role' => $targetRole->name,
        ])->assertCreated();

        $this->assertDatabaseHas('users', ['email' => 'mysql-user@example.com']);
        Notification::assertSentTo(
            User::query()->where('email', 'mysql-user@example.com')->firstOrFail(),
            UserCreatedNotification::class,
        );
    }

    public function test_concurrent_super_admin_creation_leaves_one_super_admin(): void
    {
        if (! function_exists('pcntl_fork') || ! function_exists('posix_kill')) {
            $this->markTestSkipped('The pcntl and POSIX extensions are required for the concurrency test.');
        }

        Role::query()->where('name', RoleEnum::SUPER_ADMIN->value)->delete();
        Role::create(['name' => RoleEnum::SUPER_ADMIN->value, 'guard_name' => 'sanctum']);
        $barrier = tempnam(sys_get_temp_dir(), 'laravel-mysql-race-');
        if ($barrier === false) {
            $this->fail('Unable to create the concurrency test barrier.');
        }
        $children = [];

        for ($index = 0; $index < 2; $index++) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                $this->fail('Unable to fork a concurrency test process.');
            }
            if ($pid === 0) {
                file_put_contents($barrier, '1', FILE_APPEND | LOCK_EX);
                $barrierDeadline = microtime(true) + self::BARRIER_TIMEOUT_SECONDS;
                while (strlen((string) file_get_contents($barrier)) < 2) {
                    if (microtime(true) >= $barrierDeadline) {
                        exit(2);
                    }

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

        $remainingChildren = $children;
        $deadline = microtime(true) + self::CHILDREN_TIMEOUT_SECONDS;
        $timedOut = false;

        while ($remainingChildren !== []) {
            foreach ($remainingChildren as $index => $child) {
                $result = pcntl_waitpid($child, $status, WNOHANG);
                if ($result === $child || $result === -1) {
                    unset($remainingChildren[$index]);
                }
            }

            if ($remainingChildren === []) {
                break;
            }

            if (microtime(true) >= $deadline) {
                $timedOut = true;
                foreach ($remainingChildren as $child) {
                    posix_kill($child, SIGKILL);
                    pcntl_waitpid($child, $status);
                }
                break;
            }

            usleep(10_000);
        }

        @unlink($barrier);

        $this->assertFalse($timedOut, 'Concurrent super-admin creation exceeded the 30-second deadline.');
        $this->assertSame(1, User::role(RoleEnum::SUPER_ADMIN->value, 'sanctum')->count());
    }
}
