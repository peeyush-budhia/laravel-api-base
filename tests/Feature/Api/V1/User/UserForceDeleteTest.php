<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Enums\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;
use Tests\Feature\Api\V1\ApiTestCase;
use Tests\Feature\Api\V1\Concerns\InteractsWithPermissions;

final class UserForceDeleteTest extends ApiTestCase
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

    public function test_soft_deleted_user_can_be_permanently_deleted(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Deleted',
        ]);

        $user->delete();

        $response = $this->apiDelete(
            "/users/{$user->id}/force",
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                __('responses.deleted'),
            );

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_active_user_cannot_be_force_deleted_through_deleted_user_flow(): void
    {
        $user = User::factory()->create();

        $response = $this->apiDelete(
            "/users/{$user->id}/force",
        );

        $response->assertNotFound();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_force_delete_returns_not_found_for_unknown_user(): void
    {
        $response = $this->apiDelete(
            '/users/non-existent-id/force',
        );

        $response->assertNotFound();
    }

    public function test_normal_user_cannot_permanently_delete_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
        ]);

        $superAdmin->assignRole(
            RoleEnum::SUPER_ADMIN->value,
        );

        $superAdmin->delete();

        $response = $this->apiDelete(
            "/users/{$superAdmin->id}/force",
        );

        $response->assertForbidden();

        $this->assertSoftDeleted('users', [
            'id' => $superAdmin->id,
        ]);
    }

    public function test_super_admin_cannot_permanently_delete_another_super_admin(): void
    {
        $this->user->assignRole(
            RoleEnum::SUPER_ADMIN->value,
        );

        $superAdmin = User::factory()->create();

        $superAdmin->assignRole(
            RoleEnum::SUPER_ADMIN->value,
        );

        $superAdmin->delete();

        $response = $this->apiDelete(
            "/users/{$superAdmin->id}/force",
        );

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403)
            ->assertJsonPath(
                'message',
                __('responses.super_admin_manage_forbidden'),
            );

        $this->assertSoftDeleted('users', [
            'id' => $superAdmin->id,
        ]);
    }

    public function test_super_admin_cannot_permanently_delete_themselves(): void
    {
        $this->user->assignRole(
            RoleEnum::SUPER_ADMIN->value,
        );

        /*
         * Force delete only operates on soft-deleted users.
         * Soft-delete the authenticated user directly so that we
         * can exercise the self-delete protection.
         */
        $this->user->delete();

        $response = $this->apiDelete(
            "/users/{$this->user->id}/force",
        );

        $response->assertForbidden();

        $this->assertSoftDeleted('users', [
            'id' => $this->user->id,
        ]);
    }
}
