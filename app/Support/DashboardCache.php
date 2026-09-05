<?php

declare(strict_types=1);

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

final class DashboardCache
{
    public const CACHE_KEY = 'dashboard:v5';

    public const CACHE_TTL_SECONDS = 60;

    public static function remember(Closure $callback): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            $callback,
        );
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
