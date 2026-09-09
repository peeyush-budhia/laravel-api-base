<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

final class DemoRolesSeeder extends Seeder
{
    public const string ADMIN_ROLE = 'admin';

    /**
     * Seed roles intended only for local development and testing.
     */
    public function run(): void
    {
        if (! $this->container->environment('local', 'testing')) {
            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = Role::findOrCreate(
            self::ADMIN_ROLE,
            'sanctum',
        );

        $admin->syncPermissions(
            collect([
                PermissionEnum::DASHBOARD_VIEW,
                PermissionEnum::AUDIT_LOGS_VIEW,
                PermissionEnum::USERS_VIEW,
                PermissionEnum::USERS_CREATE,
                PermissionEnum::USERS_UPDATE,
                PermissionEnum::ROLES_VIEW,
            ])->map(
                fn (PermissionEnum $permission): Permission => Permission::findOrCreate(
                    $permission->value,
                    'sanctum',
                ),
            ),
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
