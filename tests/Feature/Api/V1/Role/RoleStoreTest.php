<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Role;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Feature\Api\V1\ApiTestCase;

final class RoleStoreTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::findOrCreate(
            'roles.create',
            'sanctum',
        );

        $this->user->givePermissionTo($permission);
    }

    public function test_role_can_be_created(): void
    {
        $response = $this->apiPost('/roles', [
            'name' => 'manager',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 201)
            ->assertJsonPath(
                'message',
                __('responses.created'),
            )
            ->assertJsonPath('data.name', 'manager')
            ->assertJsonPath('data.guard_name', 'sanctum');

        $this->assertDatabaseHas('roles', [
            'name' => 'manager',
            'guard_name' => 'sanctum',
        ]);
    }

    public function test_role_name_must_be_unique(): void
    {
        Role::create([
            'name' => 'manager',
            'guard_name' => 'sanctum',
        ]);

        $response = $this->apiPost('/roles', [
            'name' => 'manager',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
            ]);
    }

    public function test_role_requires_name(): void
    {
        $response = $this->apiPost('/roles', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
            ]);
    }
}
