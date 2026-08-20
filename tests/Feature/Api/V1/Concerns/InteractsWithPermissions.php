<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Concerns;

use App\Models\Permission;
use App\Models\User;

trait InteractsWithPermissions
{
    protected function givePermission(
        User $user,
        string $permission,
    ): void {
        $permission = Permission::findOrCreate(
            $permission,
            'sanctum',
        );

        $user->givePermissionTo($permission);
    }

    protected function removePermission(
        User $user,
        string $permission,
    ): void {
        $permission = Permission::where('name', $permission)
            ->where('guard_name', 'sanctum')
            ->first();

        if ($permission) {
            $user->revokePermissionTo($permission);
        }
    }
}
