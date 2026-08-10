<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Role;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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
            'name' => 'manager',
            'guard_name' => 'sanctum',
        ]);

        $response = $this->apiPut(
            "/roles/{$role->id}",
            [
                'name' => 'senior-manager',
            ],
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath(
                'message',
                __('responses.updated'),
            )
            ->assertJsonPath(
                'data.id',
                $role->id,
            )
            ->assertJsonPath(
                'data.name',
                'senior-manager',
            )
            ->assertJsonPath(
                'data.guard_name',
                'sanctum',
            );

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
}
