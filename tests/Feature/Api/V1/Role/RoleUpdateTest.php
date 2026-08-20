<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Role;

use App\Enums\Role as EnumsRole;
use App\Models\Permission;
use App\Models\Role;
use Tests\Feature\Api\V1\ApiTestCase;

final class RoleUpdateTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::findOrCreate(
            'roles.update',
            'sanctum',
        );

        $this->user->givePermissionTo($permission);
    }

    public function test_role_can_be_updated(): void
    {
        $role = Role::create([
            'name' => 'sales-manager',
            'guard_name' => 'sanctum',
        ]);

        $response = $this->apiPut(
            "/roles/{$role->id}",
            [
                'name' => 'Senior Manager',
            ],
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath('data.name', 'senior-manager');

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'senior-manager',
            'guard_name' => 'sanctum',
        ]);
    }

    public function test_role_name_must_be_unique_when_updating(): void
    {
        $manager = Role::create([
            'name' => 'manager',
            'guard_name' => 'sanctum',
        ]);

        Role::create([
            'name' => 'accountant',
            'guard_name' => 'sanctum',
        ]);

        $response = $this->apiPut(
            "/roles/{$manager->id}",
            [
                'name' => 'accountant',
            ],
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
            ]);
    }

    public function test_non_existing_role_cannot_be_updated(): void
    {
        $response = $this->apiPut(
            '/roles/999999',
            [
                'name' => 'manager',
            ],
        );

        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 404)
            ->assertJsonPath(
                'message',
                __('responses.not_found'),
            );
    }

    public function test_super_admin_cannot_be_updated(): void
    {
        $role = Role::create([
            'name' => EnumsRole::SUPER_ADMIN->value,
            'guard_name' => 'sanctum',
        ]);

        $response = $this->apiPut(
            "/roles/{$role->id}",
            [
                'name' => 'renamed-super-admin',
            ],
        );

        $response
            ->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 409)
            ->assertJsonPath(
                'message',
                __('roles.cannot_modify_super_admin'),
            );

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => EnumsRole::SUPER_ADMIN->value,
        ]);
    }

    public function test_role_permissions_cannot_be_updated_through_role_update(): void
    {
        $role = Role::create([
            'name' => 'sales-manager',
            'guard_name' => 'sanctum',
        ]);

        $viewPermission = Permission::create([
            'name' => 'roles.view',
            'guard_name' => 'sanctum',
        ]);

        $response = $this->apiPut(
            "/roles/{$role->id}",
            [
                'name' => 'Sales Manager',
                'permissions' => [
                    $viewPermission->name,
                ],
            ],
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath('data.name', 'sales-manager');

        $this->assertFalse(
            $role->fresh()->hasPermissionTo('roles.view'),
        );
    }

    public function test_user_without_roles_update_cannot_update_role(): void
    {
        $this->user->revokePermissionTo('roles.update');

        $managePermission = Permission::findOrCreate(
            'roles.manage-permissions',
            'sanctum',
        );

        $this->user->givePermissionTo($managePermission);

        $role = Role::create([
            'name' => 'sales-manager',
            'guard_name' => 'sanctum',
        ]);

        $response = $this->apiPut(
            "/roles/{$role->id}",
            [
                'name' => 'Senior Manager',
            ],
        );

        $response->assertForbidden();
    }
}
