<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\AuditEvent;
use App\Enums\Permission as PermissionEnum;
use App\Enums\UserStatus;
use App\Http\Resources\Api\V1\AuditLogResource;
use App\Http\Resources\Api\V1\UserSummaryResource;
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
            fn (): array => $this->buildDashboard(
                $includeUserDetails,
                $includeAuditDetails,
            ),
        );
    }

    /**
     * Build the dashboard from one grouped query per status/event dimension.
     *
     * @return array<string, mixed>
     */
    private function buildDashboard(bool $includeUserDetails, bool $includeAuditDetails): array
    {
        $userCounts = User::query()
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($item): array => [$item->status->value => (int) $item->total])
            ->all();

        $auditCounts = AuditLog::query()
            ->select('event')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('event')
            ->get()
            ->mapWithKeys(fn ($item): array => [
                $item->event instanceof AuditEvent ? $item->event->value : (string) $item->event => (int) $item->total,
            ])
            ->all();

        return [
            'summary' => $this->summary($userCounts, $auditCounts),
            'users' => $this->userStatistics($includeUserDetails, $userCounts),
            'audit' => $this->auditStatistics($includeAuditDetails, $auditCounts),
        ];
    }

    /**
     * Get dashboard summary statistics.
     *
     * @return array<string, mixed>
     */
    private function summary(array $userCounts, array $auditCounts): array
    {
        return [
            'users' => [
                'total' => array_sum($userCounts),
                'active' => $userCounts[UserStatus::ACTIVE->value] ?? 0,
                'inactive' => $userCounts[UserStatus::INACTIVE->value] ?? 0,
                'suspended' => $userCounts[UserStatus::SUSPENDED->value] ?? 0,
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
                'total' => array_sum($auditCounts),
            ],
        ];
    }

    /**
     * Get user statistics.
     *
     * @return array<string, mixed>
     */
    private function userStatistics(bool $includeDetails, array $usersByStatus): array
    {
        if (! $includeDetails) {
            return [
                'by_status' => $usersByStatus,
                'status_labels' => $this->statusLabels(),
                'status_tones' => $this->statusTones(),
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
            ->whereNotNull('last_login_at')
            ->latest('last_login_at')
            ->limit(5)
            ->get();

        return [
            'by_status' => $usersByStatus,
            'status_labels' => $this->statusLabels(),
            'status_tones' => $this->statusTones(),
            'recent' => UserSummaryResource::collection($recentUsers)->resolve(),
            'recently_active' => UserSummaryResource::collection($recentlyActiveUsers)->resolve(),
        ];
    }

    /**
     * Get audit statistics.
     *
     * @return array<string, mixed>
     */
    private function auditStatistics(bool $includeDetails, array $events): array
    {
        if (! $includeDetails) {
            return [
                'by_event' => $events,
                'event_labels' => $this->eventLabels(),
                'event_tones' => $this->eventTones(),
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
                'user:id,first_name,last_name,email,avatar',
            ])
            ->latest('created_at')
            ->limit(6)
            ->get();

        return [
            'by_event' => $events,
            'event_labels' => $this->eventLabels(),
            'event_tones' => $this->eventTones(),
            'recent' => AuditLogResource::collection($recent)->resolve(),
        ];
    }

    /** @return array<string, string> */
    private function statusLabels(): array
    {
        return collect(UserStatus::cases())
            ->mapWithKeys(fn (UserStatus $status): array => [
                $status->value => $status->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    private function eventLabels(): array
    {
        return collect(AuditEvent::cases())
            ->mapWithKeys(fn (AuditEvent $event): array => [
                $event->value => $event->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    private function statusTones(): array
    {
        return collect(UserStatus::cases())
            ->mapWithKeys(fn (UserStatus $status): array => [$status->value => $status->tone()])
            ->all();
    }

    /** @return array<string, string> */
    private function eventTones(): array
    {
        return collect(AuditEvent::cases())
            ->mapWithKeys(fn (AuditEvent $event): array => [$event->value => $event->tone()])
            ->all();
    }
}
