<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Dashboard\DashboardService;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_caches_dashboard_payload(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->with(
                'dashboard:v5',
                60,
                Mockery::on(static fn ($callback): bool => is_callable($callback)),
            )
            ->andReturnUsing(
                static fn (string $key, int $ttl, callable $callback): array => $callback(),
            );

        $result = app(DashboardService::class)->getDashboard();

        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('users', $result);
        $this->assertArrayHasKey('audit', $result);
        $this->assertIsArray($result['users']['by_status']);
        $this->assertIsArray($result['audit']['by_event']);
    }
}
