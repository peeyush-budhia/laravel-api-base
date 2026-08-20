<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Role;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Tests\Feature\Api\V1\ApiTestCase;

final class RoleAuthorizationTest extends ApiTestCase
{
    public function test_guest_cannot_list_roles(): void
    {
        Auth::forgetGuards();

        $response = $this->getJson('/api/v1/roles');

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_without_permission_cannot_list_roles(): void
    {
        $response = $this->apiGet('/roles');

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403);
    }

    public function test_authenticated_user_with_view_permission_can_list_roles(): void
    {
        $permission = Permission::findOrCreate(
            'roles.view',
            'sanctum',
        );

        $this->user->givePermissionTo($permission);

        Role::findOrCreate('manager', 'sanctum');

        $response = $this->apiGet('/roles');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200);
    }

    public function test_authenticated_user_with_role_permission_can_list_roles(): void
    {
        $permission = Permission::findOrCreate(
            'roles.view',
            'sanctum',
        );

        $role = Role::findOrCreate(
            'role-manager',
            'sanctum',
        );

        $role->givePermissionTo($permission);

        $this->user->assignRole($role);

        $response = $this->apiGet('/roles');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200);
    }
}
