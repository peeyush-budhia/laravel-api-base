<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Permission;
use App\Services\Role\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

final class RoleServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_caches_the_all_permissions_lookup(): void
    {
        Permission::findOrCreate('users.view', 'sanctum');
        Permission::findOrCreate('users.create', 'sanctum');

        Cache::shouldReceive('remember')
            ->once()
            ->with(
                'permissions:all:sanctum',
                300,
                Mockery::on(static fn ($callback): bool => is_callable($callback)),
            )
            ->andReturnUsing(
                static fn (string $key, int $ttl, callable $callback) => $callback(),
            );

        $permissions = app(RoleService::class)->allPermissions();

        $this->assertSame(
            ['users.create', 'users.view'],
            $permissions->pluck('name')->sort()->values()->all(),
        );
    }
}
