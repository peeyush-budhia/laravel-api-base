<?php

declare(strict_types=1);

namespace App\Services\Role;

use App\Enums\Role as RoleEnum;
use App\Exceptions\RoleDeletionException;
use App\Exceptions\RoleProtectionException;
use App\Models\Permission;
use App\Models\Role;
use App\Query\QueryExecutor;
use App\Query\QueryParameters;
use App\Query\RoleQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RoleService
{
    public function __construct(
        private readonly RoleQuery $roleQuery,
        private readonly QueryExecutor $queryExecutor,
    ) {}

    public function index(
        QueryParameters $parameters
    ): LengthAwarePaginator {
        return $this->queryExecutor->paginate(
            $this->roleQuery,
            $parameters,
        );
    }

    public function show(Role $role): Role
    {
        return $role;
    }

    /**
     * Create a role.
     */
    public function store(array $data): Role
    {
        return DB::transaction(function () use ($data): Role {
            /** @var Role $role */
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => 'sanctum',
            ]);

            return $role->fresh();
        });
    }

    /**
     * Update a role.
     */
    public function update(
        Role $role,
        array $data,
    ): Role {
        $this->ensureNotSuperAdmin(
            $role,
            __('roles.cannot_modify_super_admin'),
        );

        return DB::transaction(function () use (
            $role,
            $data,
        ): Role {
            $role->update([
                'name' => $data['name'],
            ]);

            return $role->fresh();
        });
    }

    public function destroy(Role $role): void
    {
        if ($role->name === RoleEnum::SUPER_ADMIN->value) {
            throw new RoleDeletionException(
                __('roles.cannot_delete_super_admin'),
            );
        }

        $table = config(
            'permission.table_names.model_has_roles',
        );

        $roleColumn = config(
            'permission.column_names.role_pivot_key',
        ) ?? 'role_id';

        $isAssigned = DB::table($table)
            ->where($roleColumn, $role->id)
            ->exists();

        if ($isAssigned) {
            throw new RoleDeletionException(
                __('roles.cannot_delete_assigned'),
            );
        }

        $role->delete();
    }

    /**
     * Get permissions assigned to a role.
     *
     * @return Collection<int, Permission>
     */
    public function permissions(Role $role)
    {
        return $role->permissions()->orderBy('name')->get();
    }

    /**
     * Synchronize permissions assigned to a role.
     */
    public function syncPermissions(
        Role $role,
        array $permissions,
    ): Role {
        $this->ensureNotSuperAdmin(
            $role,
            __('roles.cannot_modify_super_admin_permissions'),
        );

        $role->syncPermissions($permissions);

        return $role->fresh('permissions');
    }

    /**
     * Determine whether the role is the protected super-admin role.
     */
    private function isSuperAdmin(Role $role): bool
    {
        return $role->name === RoleEnum::SUPER_ADMIN->value;
    }

    /**
     * Prevent modification of the protected super-admin role.
     */
    private function ensureNotSuperAdmin(
        Role $role,
        string $message,
    ): void {
        if ($this->isSuperAdmin($role)) {
            throw new RoleProtectionException($message);
        }
    }

    /**
     * Get all available permissions.
     *
     * @return Collection<int, Permission>
     */
    public function allPermissions(): Collection
    {
        return Permission::query()
            ->where('guard_name', 'sanctum')
            ->orderBy('guard_name')
            ->orderBy('name')
            ->get();
    }
}
