<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Role;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Feature\Api\V1\ApiTestCase;

final class RoleDeleteTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::findOrCreate(
            'roles.delete',
            'sanctum',
        );

        $this->user->givePermissionTo($permission);
    }

    public function test_role_can_be_deleted(): void
    {
        $role = Role::create([
            'name' => 'manager',
            'guard_name' => 'sanctum',
        ]);

        $response = $this->apiDelete(
            "/roles/{$role->id}",
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath(
                'message',
                __('responses.deleted'),
            );

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_super_admin_role_cannot_be_deleted(): void
    {
        $role = Role::create([
            'name' => RoleEnum::SUPER_ADMIN->value,
            'guard_name' => 'sanctum',
        ]);

        $response = $this->apiDelete(
            "/roles/{$role->id}",
        );

        $response
            ->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 409)
            ->assertJsonPath(
                'message',
                __('roles.cannot_delete_super_admin'),
            );

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => RoleEnum::SUPER_ADMIN->value,
        ]);
    }

    public function test_assigned_role_cannot_be_deleted(): void
    {
        $role = Role::create([
            'name' => 'manager',
            'guard_name' => 'sanctum',
        ]);

        $assignedUser = User::factory()->create();

        $assignedUser->assignRole($role);

        $response = $this->apiDelete(
            "/roles/{$role->id}",
        );

        $response
            ->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 409)
            ->assertJsonPath(
                'message',
                __('roles.cannot_delete_assigned'),
            );

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_non_existing_role_cannot_be_deleted(): void
    {
        $response = $this->apiDelete('/roles/999999');

        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 404)
            ->assertJsonPath(
                'message',
                __('responses.not_found'),
            );
    }
}
