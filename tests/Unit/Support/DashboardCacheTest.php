<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\DashboardCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class DashboardCacheTest extends TestCase
{
    private const KEYS = [
        'dashboard:v6:users:0:audit:0',
        'dashboard:v6:users:0:audit:1',
        'dashboard:v6:users:1:audit:0',
        'dashboard:v6:users:1:audit:1',
    ];

    public function test_cache_invalidation_waits_for_commit(): void
    {
        $this->seedCacheVariants();

        DB::beginTransaction();
        DashboardCache::forgetAfterCommit();

        $this->assertCacheVariantsExist();

        DB::commit();

        $this->assertCacheVariantsDoNotExist();
    }

    public function test_cache_invalidation_is_discarded_on_rollback(): void
    {
        $this->seedCacheVariants();

        DB::beginTransaction();
        DashboardCache::forgetAfterCommit();
        DB::rollBack();

        $this->assertCacheVariantsExist();
    }

    private function seedCacheVariants(): void
    {
        foreach (self::KEYS as $key) {
            Cache::put($key, ['stale' => true], 60);
        }
    }

    private function assertCacheVariantsExist(): void
    {
        foreach (self::KEYS as $key) {
            $this->assertTrue(Cache::has($key));
        }
    }

    private function assertCacheVariantsDoNotExist(): void
    {
        foreach (self::KEYS as $key) {
            $this->assertFalse(Cache::has($key));
        }
    }
}
