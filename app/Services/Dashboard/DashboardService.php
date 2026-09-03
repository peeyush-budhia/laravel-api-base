<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\AuditEvent;
use App\Enums\UserStatus;
use App\Http\Resources\Api\V1\AuditLogResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

final class DashboardService
{
    private const CACHE_KEY = 'dashboard:v3';

    private const CACHE_TTL_SECONDS = 60;

    /**
     * Get dashboard statistics.
     *
     * @return array<string, mixed>
     */
    public function getDashboard(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            fn (): array => [
                'summary' => $this->summary(),
                'users' => $this->userStatistics(),
                'audit' => $this->auditStatistics(),
            ],
        );
    }

    /**
     * Get dashboard summary statistics.
     *
     * @return array<string, mixed>
     */
    private function summary(): array
    {
        return [
            'users' => [
                'total' => User::query()->count(),
                'active' => User::query()
                    ->where('status', UserStatus::ACTIVE->value)
                    ->count(),
                'inactive' => User::query()
                    ->where('status', UserStatus::INACTIVE->value)
                    ->count(),
                'suspended' => User::query()
                    ->where('status', UserStatus::SUSPENDED->value)
                    ->count(),
            ],

            'roles' => [
                'total' => Role::query()->count(),
            ],

            'permissions' => [
                'total' => Permission::query()
                    ->where('guard_name', 'sanctum')
                    ->count(),
            ],

            'audit_logs' => [
                'total' => AuditLog::query()->count(),
            ],
        ];
    }

    /**
     * Get user statistics.
     *
     * @return array<string, mixed>
     */
    private function userStatistics(): array
    {
        $usersByStatus = User::query()
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(
                fn ($item): array => [
                    $item->status->value => (int) $item->total,
                ],
            )
            ->all();

        $recentUsers = User::query()
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'avatar',
                'status',
                'email_verified_at',
                'last_login_at',
                'must_change_password',
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->with([
                'roles',
                'permissions',
            ])
            ->latest('created_at')
            ->limit(5)
            ->get();

        $recentlyActiveUsers = User::query()
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'avatar',
                'status',
                'email_verified_at',
                'last_login_at',
                'must_change_password',
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->with([
                'roles',
                'permissions',
            ])
            ->whereNotNull('last_login_at')
            ->latest('last_login_at')
            ->limit(5)
            ->get();

        return [
            'by_status' => $usersByStatus,
            'recent' => UserResource::collection($recentUsers)->resolve(),
            'recently_active' => UserResource::collection($recentlyActiveUsers)->resolve(),
        ];
    }

    /**
     * Get audit statistics.
     *
     * @return array<string, mixed>
     */
    private function auditStatistics(): array
    {
        $events = AuditLog::query()
            ->select('event')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('event')
            ->get()
            ->mapWithKeys(
                fn ($item): array => [
                    $item->event instanceof AuditEvent
                        ? $item->event->value
                        : (string) $item->event => (int) $item->total,
                ],
            )
            ->all();

        $recent = AuditLog::query()
            ->select([
                'id',
                'user_id',
                'event',
                'auditable_type',
                'auditable_id',
                'old_values',
                'new_values',
                'url',
                'ip_address',
                'user_agent',
                'created_at',
                'updated_at',
            ])
            ->with([
                'user.roles',
                'user.permissions',
            ])
            ->latest('created_at')
            ->limit(5)
            ->get();

        return [
            'by_event' => $events,
            'recent' => AuditLogResource::collection($recent)->resolve(),
        ];
    }
}
