<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Feature\Api\V1\ApiTestCase;
use Tests\Feature\Api\V1\Concerns\InteractsWithPermissions;

final class UserAuthorizationTest extends ApiTestCase
{
    use InteractsWithPermissions;

    public function test_guest_cannot_list_users(): void
    {
        Auth::forgetGuards();

        $response = $this->getJson('/api/v1/users');

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_without_permission_cannot_list_users(): void
    {
        $response = $this->apiGet('/users');

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403);
    }

    public function test_authenticated_user_with_view_permission_can_list_users(): void
    {
        $this->givePermission(
            $this->user,
            'users.view',
        );

        User::factory()->count(3)->create();

        $response = $this->apiGet('/users');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200);
    }

    public function test_removing_role_removes_inherited_permission(): void
    {
        $role = Role::findOrCreate(
            'temporary-viewer',
            'sanctum',
        );

        $permission = Permission::findOrCreate(
            'users.view',
            'sanctum',
        );

        $role->givePermissionTo($permission);

        $this->user->assignRole($role);

        $this->apiGet('/users')
            ->assertOk();

        $this->user->removeRole($role);

        $this->apiGet('/users')
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403);
    }

    public function test_authenticated_user_with_role_permission_can_list_users(): void
    {
        $role = Role::findOrCreate(
            'user-manager',
            'sanctum',
        );

        $permission = Permission::findOrCreate(
            'users.view',
            'sanctum',
        );

        $role->givePermissionTo($permission);

        $this->user->assignRole($role);

        User::factory()->count(3)->create();

        $response = $this->apiGet('/users');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200);
    }

    public function test_authenticated_user_with_role_without_permission_cannot_create_user(): void
    {
        $role = Role::findOrCreate(
            'user-viewer',
            'sanctum',
        );

        $permission = Permission::findOrCreate(
            'users.view',
            'sanctum',
        );

        $role->givePermissionTo($permission);

        $this->user->assignRole($role);

        $response = $this->apiPost('/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'role-test@example.com',
            'password' => 'password',
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403);
    }

    public function test_authenticated_user_without_delete_permission_cannot_delete_role(): void
    {
        $role = Role::create([
            'name' => 'manager',
            'guard_name' => 'sanctum',
        ]);

        $response = $this->apiDelete(
            "/roles/{$role->id}",
        );

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403);
    }

    public function test_authenticated_user_without_manage_permissions_permission_cannot_sync_role_permissions(): void
    {
        $role = Role::create([
            'name' => 'manager',
            'guard_name' => 'sanctum',
        ]);

        $response = $this->apiPut(
            "/roles/{$role->id}/permissions",
            [
                'permissions' => [
                    'users.view',
                ],
            ],
        );

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403);
    }
}
