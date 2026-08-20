<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Role;

use App\Models\Permission;
use Tests\Feature\Api\V1\ApiTestCase;

final class PermissionIndexTest extends ApiTestCase
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

    public function test_all_permissions_can_be_listed(): void
    {
        Permission::findOrCreate('users.view', 'sanctum');
        Permission::findOrCreate('users.create', 'sanctum');
        Permission::findOrCreate('roles.view', 'sanctum');

        $response = $this->apiGet('/roles/permissions');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200);

        $response->assertJsonFragment([
            'name' => 'users.view',
            'guard_name' => 'sanctum',
        ]);

        $response->assertJsonFragment([
            'name' => 'users.create',
            'guard_name' => 'sanctum',
        ]);

        $response->assertJsonFragment([
            'name' => 'roles.view',
            'guard_name' => 'sanctum',
        ]);
    }
}
