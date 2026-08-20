<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Role;

use App\Models\Permission;
use App\Models\Role;
use Tests\Feature\Api\V1\ApiTestCase;

final class RoleShowTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::findOrCreate(
            'roles.view',
            'sanctum',
        );

        $this->user->givePermissionTo($permission);
    }

    public function test_role_can_be_shown(): void
    {
        $role = Role::findOrCreate(
            'manager',
            'sanctum',
        );

        $response = $this->apiGet(
            "/roles/{$role->id}",
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath('data.id', $role->id)
            ->assertJsonPath('data.name', 'manager')
            ->assertJsonPath('data.guard_name', 'sanctum');
    }

    public function test_non_existing_role_returns_not_found(): void
    {
        $response = $this->apiGet(
            '/roles/999999',
        );

        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 404);
    }
}
