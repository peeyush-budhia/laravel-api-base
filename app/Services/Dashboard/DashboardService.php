<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\AuditEvent;
use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

final class DashboardService
{
    /**
     * Get dashboard statistics.
     *
     * @return array<string, mixed>
     */
    public function getDashboard(): array
    {
        return [
            'summary' => $this->summary(),
            'users' => $this->userStatistics(),
            'audit' => $this->auditStatistics(),
        ];
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
            );

        $recentUsers = User::query()
            ->latest('created_at')
            ->limit(5)
            ->get();

        $recentlyActiveUsers = User::query()
            ->whereNotNull('last_login_at')
            ->latest('last_login_at')
            ->limit(5)
            ->get();

        return [
            'by_status' => $usersByStatus,
            'recent' => $recentUsers,
            'recently_active' => $recentlyActiveUsers,
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
            );

        $recent = AuditLog::query()
            ->with('user')
            ->latest('created_at')
            ->limit(10)
            ->get();

        return [
            'by_event' => $events,
            'recent' => $recent,
        ];
    }
}
