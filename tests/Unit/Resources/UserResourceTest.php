<?php

declare(strict_types=1);

namespace Tests\Unit\Resources;

use App\Http\Resources\Api\V1\UserResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_always_serializes_effective_permissions(): void
    {
        $user = User::factory()->create();
        $role = Role::findOrCreate('editor', 'sanctum');
        $sharedPermission = Permission::findOrCreate('users.view', 'sanctum');
        $rolePermission = Permission::findOrCreate('users.update', 'sanctum');
        $directPermission = Permission::findOrCreate('profile.update', 'sanctum');

        $role->givePermissionTo([$sharedPermission, $rolePermission]);
        $user->assignRole($role);
        $user->givePermissionTo([$sharedPermission, $directPermission]);

        $withoutRelations = UserResource::make($user->fresh())->resolve();
        $withRelations = UserResource::make(
            $user->fresh()->load(['roles.permissions', 'permissions']),
        )->resolve();

        $expectedPermissions = [
            'profile.update',
            'users.update',
            'users.view',
        ];

        $this->assertSame($expectedPermissions, $withoutRelations['permissions']);
        $this->assertSame($expectedPermissions, $withRelations['permissions']);
        $this->assertSame('editor', $withoutRelations['role']);
        $this->assertSame('editor', $withRelations['role']);
    }
}
