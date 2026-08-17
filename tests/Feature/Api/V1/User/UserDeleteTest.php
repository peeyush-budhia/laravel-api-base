<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Feature\Api\V1\ApiTestCase;
use Tests\Feature\Api\V1\Concerns\InteractsWithPermissions;

final class UserDeleteTest extends ApiTestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(
            RoleEnum::SUPER_ADMIN->value,
            'sanctum',
        );

        $this->givePermission(
            $this->user,
            'users.delete',
        );
    }

    public function test_user_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $response = $this->apiDelete(
            "/users/{$user->id}",
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath(
                'message',
                __('responses.deleted'),
            );

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $response = $this->apiDelete(
            "/users/{$this->user->id}",
        );

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_normal_user_cannot_delete_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
        ]);

        $superAdmin->assignRole(
            RoleEnum::SUPER_ADMIN->value,
        );

        $response = $this->apiDelete(
            "/users/{$superAdmin->id}",
        );

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403);

        $this->assertDatabaseHas('users', [
            'id' => $superAdmin->id,
            'deleted_at' => null,
        ]);
    }

    public function test_super_admin_cannot_delete_another_super_admin(): void
    {
        $this->user->assignRole(
            RoleEnum::SUPER_ADMIN->value,
        );

        $superAdmin = User::factory()->create();

        $superAdmin->assignRole(
            RoleEnum::SUPER_ADMIN->value,
        );

        $response = $this->apiDelete(
            "/users/{$superAdmin->id}",
        );

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403)
            ->assertJsonPath(
                'message',
                __('responses.super_admin_manage_forbidden'),
            );

        $this->assertDatabaseHas('users', [
            'id' => $superAdmin->id,
            'deleted_at' => null,
        ]);
    }

    public function test_super_admin_cannot_delete_themselves(): void
    {
        $this->user->assignRole(
            RoleEnum::SUPER_ADMIN->value,
        );

        $response = $this->apiDelete(
            "/users/{$this->user->id}",
        );

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_non_existing_user_cannot_be_deleted(): void
    {
        $response = $this->apiDelete(
            '/users/01999999-9999-9999-9999-999999999999',
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
