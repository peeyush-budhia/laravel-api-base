<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

final class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        /*
         * --------------------------------------------------------------------------
         * Permissions
         * --------------------------------------------------------------------------
         */

        $permissions = collect(PermissionEnum::cases())
            ->mapWithKeys(
                fn (PermissionEnum $permission): array => [
                    $permission->value => Permission::findOrCreate(
                        $permission->value,
                        'sanctum',
                    ),
                ],
            );

        /*
         * --------------------------------------------------------------------------
         * Roles
         * --------------------------------------------------------------------------
         */

        $superAdmin = Role::findOrCreate(
            RoleEnum::SUPER_ADMIN->value,
            'sanctum',
        );

        $admin = Role::findOrCreate(
            RoleEnum::ADMIN->value,
            'sanctum',
        );

        $user = Role::findOrCreate(
            RoleEnum::USER->value,
            'sanctum',
        );

        /*
         * --------------------------------------------------------------------------
         * Super Admin
         * --------------------------------------------------------------------------
         */

        $superAdmin->syncPermissions(
            $permissions->values(),
        );

        /*
         * --------------------------------------------------------------------------
         * Admin
         * --------------------------------------------------------------------------
         */

        $admin->syncPermissions(
            $permissions->values(),
        );

        /*
         * --------------------------------------------------------------------------
         * User
         * --------------------------------------------------------------------------
         */

        $user->syncPermissions([
            $permissions[PermissionEnum::USERS_VIEW->value],
        ]);

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
