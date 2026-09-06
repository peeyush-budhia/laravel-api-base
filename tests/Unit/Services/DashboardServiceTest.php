<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use App\Services\Dashboard\DashboardService;
use App\Support\DashboardCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

final class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_caches_dashboard_payload(): void
    {
        $user = User::factory()->create();
        $user->can(PermissionEnum::USERS_VIEW->value);
        $user->can(PermissionEnum::AUDIT_LOGS_VIEW->value);

        Cache::shouldReceive('remember')
            ->once()
            ->with(
                'dashboard:v6:users:0:audit:0',
                60,
                Mockery::on(static fn ($callback): bool => is_callable($callback)),
            )
            ->andReturnUsing(
                static fn (string $key, int $ttl, callable $callback): array => $callback(),
            );

        $result = app(DashboardService::class)->getDashboard(
            $user,
        );

        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('users', $result);
        $this->assertArrayHasKey('audit', $result);
        $this->assertIsArray($result['users']['by_status']);
        $this->assertIsArray($result['audit']['by_event']);
    }

    public function test_it_forgets_every_permission_scoped_dashboard_payload(): void
    {
        foreach ([
            'dashboard:v6:users:0:audit:0',
            'dashboard:v6:users:0:audit:1',
            'dashboard:v6:users:1:audit:0',
            'dashboard:v6:users:1:audit:1',
        ] as $key) {
            Cache::shouldReceive('forget')
                ->once()
                ->with($key);
        }

        DashboardCache::forget();
    }
}
