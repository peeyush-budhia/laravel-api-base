<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Role;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Feature\Api\V1\ApiTestCase;

final class RoleIndexTest extends ApiTestCase
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

    public function test_roles_can_be_listed(): void
    {
        Role::findOrCreate('manager', 'sanctum');
        Role::findOrCreate('accountant', 'sanctum');

        $response = $this->apiGet('/roles');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', __('responses.success'))
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'guard_name',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta',
            ]);
    }

    public function test_roles_are_paginated(): void
    {
        foreach (range(1, 30) as $number) {
            Role::findOrCreate(
                "role-{$number}",
                'sanctum',
            );
        }

        $response = $this->apiGet('/roles');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);
    }

    public function test_roles_can_be_searched(): void
    {
        Role::findOrCreate('sales-manager', 'sanctum');
        Role::findOrCreate('accountant', 'sanctum');

        $response = $this->apiGet('/roles?search=sales');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'sales-manager');
    }

    public function test_roles_can_be_sorted_by_name(): void
    {
        Role::findOrCreate('z-role', 'sanctum');
        Role::findOrCreate('a-role', 'sanctum');

        $response = $this->apiGet(
            '/roles?sort=name&direction=asc',
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.0.name', 'a-role')
            ->assertJsonPath('data.1.name', 'z-role');
    }

    public function test_unsupported_sort_is_ignored(): void
    {
        Role::findOrCreate('z-role', 'sanctum');
        Role::findOrCreate('a-role', 'sanctum');

        $response = $this->apiGet(
            '/roles?sort=invalid&direction=desc',
        );

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
