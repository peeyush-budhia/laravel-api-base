<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\AuditEvent;
use App\Enums\Permission as PermissionEnum;
use App\Enums\UserStatus;
use App\Http\Resources\Api\V1\AuditLogResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\DashboardCache;

final class DashboardService
{
    /**
     * Get dashboard statistics.
     *
     * @return array<string, mixed>
     */
    public function getDashboard(User $viewer): array
    {
        $includeUserDetails = $viewer->can(PermissionEnum::USERS_VIEW->value);
        $includeAuditDetails = $viewer->can(PermissionEnum::AUDIT_LOGS_VIEW->value);

        return DashboardCache::remember(
            $includeUserDetails,
            $includeAuditDetails,
            fn (): array => [
                'summary' => $this->summary(),
                'users' => $this->userStatistics($includeUserDetails),
                'audit' => $this->auditStatistics($includeAuditDetails),
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
    private function userStatistics(bool $includeDetails): array
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

        if (! $includeDetails) {
            return [
                'by_status' => $usersByStatus,
                'recent' => [],
                'recently_active' => [],
            ];
        }

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
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->with([
                'roles.permissions',
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
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->with([
                'roles.permissions',
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
    private function auditStatistics(bool $includeDetails): array
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

        if (! $includeDetails) {
            return [
                'by_event' => $events,
                'recent' => [],
            ];
        }

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
                'user.roles.permissions',
                'user.permissions',
            ])
            ->latest('created_at')
            ->limit(6)
            ->get();

        return [
            'by_event' => $events,
            'recent' => AuditLogResource::collection($recent)->resolve(),
        ];
    }
}
