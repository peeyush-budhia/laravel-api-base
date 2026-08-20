<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Role;

use App\Enums\Permission as EnumsPermission;
use App\Enums\Role as EnumsRole;
use App\Models\Permission;
use App\Models\Role;
use Tests\Feature\Api\V1\ApiTestCase;

final class RolePermissionTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $viewPermission = Permission::findOrCreate(
            'roles.view',
            'sanctum',
        );

        $managePermission = Permission::findOrCreate(
            'roles.manage-permissions',
            'sanctum',
        );

        $this->user->givePermissionTo([
            $viewPermission,
            $managePermission,
        ]);
    }

    public function test_role_permissions_can_be_listed(): void
    {
        $role = Role::create([
            'name' => 'manager',
            'guard_name' => 'sanctum',
        ]);

        $view = Permission::findOrCreate(
            'users.view',
            'sanctum',
        );

        $create = Permission::findOrCreate(
            'users.create',
            'sanctum',
        );

        $role->syncPermissions([
            $view,
            $create,
        ]);

        $response = $this->apiGet(
            "/roles/{$role->id}/permissions",
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath(
                'message',
                __('responses.success'),
            )
            ->assertJsonCount(2, 'data');

        $response->assertJsonFragment([
            'name' => 'users.view',
            'guard_name' => 'sanctum',
        ]);

        $response->assertJsonFragment([
            'name' => 'users.create',
            'guard_name' => 'sanctum',
        ]);
    }

    public function test_role_permissions_can_be_synchronized(): void
    {
        $role = Role::create([
            'name' => 'manager',
            'guard_name' => 'sanctum',
        ]);

        $view = Permission::findOrCreate(
            'users.view',
            'sanctum',
        );

        $create = Permission::findOrCreate(
            'users.create',
            'sanctum',
        );

        $role->syncPermissions([
            $view,
        ]);

        $response = $this->apiPut(
            "/roles/{$role->id}/permissions",
            [
                'permissions' => [
                    'users.view',
                    'users.create',
                ],
            ],
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath(
                'message',
                __('responses.updated'),
            );

        $this->assertTrue(
            $role->fresh()->hasPermissionTo('users.view'),
        );

        $this->assertTrue(
            $role->fresh()->hasPermissionTo('users.create'),
        );
    }

    public function test_permission_synchronization_removes_omitted_permissions(): void
    {
        $role = Role::create([
            'name' => 'manager',
            'guard_name' => 'sanctum',
        ]);

        $view = Permission::findOrCreate(
            'users.view',
            'sanctum',
        );

        $create = Permission::findOrCreate(
            'users.create',
            'sanctum',
        );

        $update = Permission::findOrCreate(
            'users.update',
            'sanctum',
        );

        $role->syncPermissions([
            $view,
            $create,
            $update,
        ]);

        $response = $this->apiPut(
            "/roles/{$role->id}/permissions",
            [
                'permissions' => [
                    'users.view',
                ],
            ],
        );

        $response->assertOk();

        $freshRole = $role->fresh();

        $this->assertTrue(
            $freshRole->hasPermissionTo('users.view'),
        );

        $this->assertFalse(
            $freshRole->hasPermissionTo('users.create'),
        );

        $this->assertFalse(
            $freshRole->hasPermissionTo('users.update'),
        );
    }

    public function test_empty_permission_list_removes_all_permissions(): void
    {
        $view = Permission::findOrCreate(
            'users.view',
            'sanctum',
        );

        $create = Permission::findOrCreate(
            'users.create',
            'sanctum',
        );

        $role = Role::create([
            'name' => 'manager',
            'guard_name' => 'sanctum',
        ]);

        $role->syncPermissions([
            $view,
            $create,
        ]);
        $response = $this->apiPut(
            "/roles/{$role->id}/permissions",
            [
                'permissions' => [],
            ],
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200);

        $this->assertCount(
            0,
            $role->fresh()->permissions,
        );
    }

    public function test_permission_must_exist(): void
    {
        $role = Role::create([
            'name' => 'manager',
            'guard_name' => 'sanctum',
        ]);

        $response = $this->apiPut(
            "/roles/{$role->id}/permissions",
            [
                'permissions' => [
                    'users.this-does-not-exist',
                ],
            ],
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'permissions.0',
            ]);
    }

    public function test_permissions_field_is_required(): void
    {
        $role = Role::create([
            'name' => 'manager',
            'guard_name' => 'sanctum',
        ]);

        $response = $this->apiPut(
            "/roles/{$role->id}/permissions",
            [],
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'permissions',
            ]);
    }

    public function test_non_existing_role_permissions_cannot_be_listed(): void
    {
        $response = $this->apiGet(
            '/roles/999999/permissions',
        );

        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 404);
    }

    public function test_non_existing_role_permissions_cannot_be_synchronized(): void
    {
        $response = $this->apiPut(
            '/roles/999999/permissions',
            [
                'permissions' => [
                    'users.view',
                ],
            ],
        );

        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 404);
    }

    public function test_super_admin_permissions_cannot_be_modified(): void
    {
        $role = Role::create([
            'name' => EnumsRole::SUPER_ADMIN->value,
            'guard_name' => 'sanctum',
        ]);

        $permission = Permission::findOrCreate(
            EnumsPermission::USERS_VIEW->value,
            'sanctum',
        );

        $role->givePermissionTo($permission);

        $response = $this->apiPut(
            "/roles/{$role->id}/permissions",
            [
                'permissions' => [],
            ],
        );

        $response
            ->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 409)
            ->assertJsonPath(
                'message',
                __('roles.cannot_modify_super_admin_permissions'),
            );

        $this->assertTrue(
            $role->fresh()->hasPermissionTo($permission),
        );
    }

    public function test_user_without_manage_permissions_cannot_synchronize_role_permissions(): void
    {
        $this->user->revokePermissionTo(
            'roles.manage-permissions',
        );

        $role = Role::create([
            'name' => 'manager',
            'guard_name' => 'sanctum',
        ]);

        $permission = Permission::findOrCreate(
            'users.view',
            'sanctum',
        );

        $response = $this->apiPut(
            "/roles/{$role->id}/permissions",
            [
                'permissions' => [
                    $permission->name,
                ],
            ],
        );

        $response->assertForbidden();

        $this->assertFalse(
            $role->fresh()->hasPermissionTo('users.view'),
        );
    }
}
