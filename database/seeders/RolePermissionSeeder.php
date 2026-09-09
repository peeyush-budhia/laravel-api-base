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

        /*
         * --------------------------------------------------------------------------
         * Super Admin
         * --------------------------------------------------------------------------
         */

        $superAdmin->syncPermissions(
            $permissions->values(),
        );

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
