<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

final class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect(Permission::cases())
            ->mapWithKeys(
                fn (Permission $permission): array => [
                    $permission->value => SpatiePermission::findOrCreate(
                        $permission->value,
                        'sanctum',
                    ),
                ]
            );

        $superAdmin = SpatieRole::findOrCreate(
            Role::SUPER_ADMIN->value,
            'sanctum',
        );

        $admin = SpatieRole::findOrCreate(
            Role::ADMIN->value,
            'sanctum',
        );

        $user = SpatieRole::findOrCreate(
            Role::USER->value,
            'sanctum',
        );

        $superAdmin->syncPermissions($permissions->values());

        $admin->syncPermissions($permissions->values());

        $user->syncPermissions([
            $permissions[Permission::USERS_VIEW->value],
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
