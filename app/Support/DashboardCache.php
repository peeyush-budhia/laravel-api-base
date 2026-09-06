<?php

declare(strict_types=1);

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class DashboardCache
{
    public const CACHE_KEY_PREFIX = 'dashboard:v6';

    public const CACHE_TTL_SECONDS = 60;

    public static function remember(
        bool $includeUserDetails,
        bool $includeAuditDetails,
        Closure $callback,
    ): array {
        return Cache::remember(
            self::key($includeUserDetails, $includeAuditDetails),
            self::CACHE_TTL_SECONDS,
            $callback,
        );
    }

    public static function forget(): void
    {
        foreach ([false, true] as $includeUserDetails) {
            foreach ([false, true] as $includeAuditDetails) {
                Cache::forget(
                    self::key($includeUserDetails, $includeAuditDetails),
                );
            }
        }
    }

    public static function forgetAfterCommit(
        ?string $connectionName = null,
    ): void {
        DB::connection($connectionName)->afterCommit(
            static function (): void {
                self::forget();
            },
        );
    }

    private static function key(
        bool $includeUserDetails,
        bool $includeAuditDetails,
    ): string {
        return sprintf(
            '%s:users:%d:audit:%d',
            self::CACHE_KEY_PREFIX,
            $includeUserDetails,
            $includeAuditDetails,
        );
    }
}
