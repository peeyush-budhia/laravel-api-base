<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Enums\Role as EnumsRole;
use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Tests\Feature\Api\V1\ApiTestCase;
use Tests\Feature\Api\V1\Concerns\InteractsWithPermissions;

final class UserUpdateTest extends ApiTestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create([
            'name' => EnumsRole::SUPER_ADMIN->value,
            'guard_name' => 'sanctum',
        ]);

        Role::create([
            'name' => EnumsRole::ADMIN->value,
            'guard_name' => 'sanctum',
        ]);

        Role::create([
            'name' => EnumsRole::USER->value,
            'guard_name' => 'sanctum',
        ]);

        $this->givePermission(
            $this->user,
            'users.update',
        );
    }

    public function test_user_can_be_updated(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'status' => UserStatus::ACTIVE,
        ]);

        $user->assignRole(EnumsRole::USER->value);

        $newEmail = fake()->unique()->safeEmail();

        $payload = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => $newEmail,
            'status' => UserStatus::INACTIVE->value,
            'role' => EnumsRole::ADMIN->value,
        ];

        $response = $this->apiPut(
            "/users/{$user->id}",
            $payload,
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath(
                'message',
                __('responses.updated'),
            )
            ->assertJsonPath('data.first_name', 'Jane')
            ->assertJsonPath('data.last_name', 'Smith')
            ->assertJsonPath('data.email', $newEmail)
            ->assertJsonPath(
                'data.status',
                UserStatus::INACTIVE->value,
            )
            ->assertJsonPath(
                'data.role',
                EnumsRole::ADMIN->value,
            );

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => $newEmail,
            'status' => UserStatus::INACTIVE->value,
        ]);

        $updatedUser = $user->fresh();

        $this->assertTrue(
            $updatedUser->hasRole(
                EnumsRole::ADMIN->value,
            ),
        );

        $this->assertFalse(
            $updatedUser->hasRole(
                EnumsRole::USER->value,
            ),
        );
    }

    public function test_password_is_not_updated_when_password_is_not_provided(): void
    {
        $user = User::factory()->create();

        $oldPassword = $user->password;

        $response = $this->apiPut(
            "/users/{$user->id}",
            [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => EnumsRole::USER->value,
            ],
        );

        $response->assertOk();

        $this->assertSame(
            $oldPassword,
            $user->fresh()->password,
        );
    }

    public function test_user_role_must_exist_when_updating(): void
    {
        $user = User::factory()->create();

        $response = $this->apiPut(
            "/users/{$user->id}",
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => $user->email,
                'role' => 'non-existent-role',
            ],
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'role',
            ]);
    }

    public function test_user_cannot_modify_their_own_account_through_user_management(): void
    {
        $this->user->assignRole(
            EnumsRole::USER->value,
        );

        $response = $this->apiPut(
            "/users/{$this->user->id}",
            [
                'first_name' => 'Changed',
                'last_name' => $this->user->last_name,
                'email' => $this->user->email,
                'role' => EnumsRole::ADMIN->value,
            ],
        );

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403)
            ->assertJsonPath(
                'message',
                __('responses.user_cannot_manage_self'),
            );

        $updatedUser = $this->user->fresh();

        $this->assertNotSame(
            'Changed',
            $updatedUser->first_name,
        );

        $this->assertTrue(
            $updatedUser->hasRole(
                EnumsRole::USER->value,
            ),
        );
    }

    public function test_non_super_admin_cannot_modify_super_admin(): void
    {
        $superAdmin = User::factory()->create();

        $superAdmin->assignRole(
            EnumsRole::SUPER_ADMIN->value,
        );

        $response = $this->apiPut(
            "/users/{$superAdmin->id}",
            [
                'first_name' => $superAdmin->first_name,
                'last_name' => $superAdmin->last_name,
                'email' => $superAdmin->email,
                'role' => EnumsRole::ADMIN->value,
            ],
        );

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403)
            ->assertJsonPath(
                'message',
                __('responses.super_admin_manage_forbidden'),
            );

        $this->assertTrue(
            $superAdmin->fresh()->hasRole(
                EnumsRole::SUPER_ADMIN->value,
            ),
        );

        $this->assertFalse(
            $superAdmin->fresh()->hasRole(
                EnumsRole::ADMIN->value,
            ),
        );
    }

    public function test_super_admin_cannot_modify_another_super_admin(): void
    {
        $this->user->assignRole(
            EnumsRole::SUPER_ADMIN->value,
        );

        $target = User::factory()->create();

        $target->assignRole(
            EnumsRole::SUPER_ADMIN->value,
        );

        $response = $this->apiPut(
            "/users/{$target->id}",
            [
                'first_name' => 'Changed',
                'last_name' => $target->last_name,
                'email' => $target->email,
                'role' => EnumsRole::ADMIN->value,
            ],
        );

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403)
            ->assertJsonPath(
                'message',
                __('responses.super_admin_manage_forbidden'),
            );

        $updatedTarget = $target->fresh();

        $this->assertSame(
            $target->first_name,
            $updatedTarget->first_name,
        );

        $this->assertTrue(
            $updatedTarget->hasRole(
                EnumsRole::SUPER_ADMIN->value,
            ),
        );

        $this->assertFalse(
            $updatedTarget->hasRole(
                EnumsRole::ADMIN->value,
            ),
        );
    }

    public function test_super_admin_can_change_normal_user_role(): void
    {
        $this->user->assignRole(
            EnumsRole::SUPER_ADMIN->value,
        );

        $target = User::factory()->create();

        $target->assignRole(
            EnumsRole::USER->value,
        );

        $response = $this->apiPut(
            "/users/{$target->id}",
            [
                'first_name' => $target->first_name,
                'last_name' => $target->last_name,
                'email' => $target->email,
                'role' => EnumsRole::ADMIN->value,
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
                'data.role',
                EnumsRole::ADMIN->value,
            );

        $updatedTarget = $target->fresh();

        $this->assertTrue(
            $updatedTarget->hasRole(
                EnumsRole::ADMIN->value,
            ),
        );

        $this->assertFalse(
            $updatedTarget->hasRole(
                EnumsRole::USER->value,
            ),
        );
    }

    public function test_non_existing_user_cannot_be_updated(): void
    {
        $response = $this->apiPut(
            '/users/01999999-9999-9999-9999-999999999999',
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
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

    public function test_user_can_be_promoted_to_super_admin_when_none_exists(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $user->assignRole(EnumsRole::USER->value);

        $response = $this->apiPut("/users/{$user->id}", [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => EnumsRole::SUPER_ADMIN->value,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath(
                'data.role',
                EnumsRole::SUPER_ADMIN->value,
            );

        $this->assertTrue(
            $user->fresh()->hasRole(EnumsRole::SUPER_ADMIN->value),
        );
    }

    public function test_second_user_cannot_be_promoted_to_super_admin(): void
    {
        $existingSuperAdmin = User::factory()->create();

        $existingSuperAdmin->assignRole(
            EnumsRole::SUPER_ADMIN->value,
        );

        $user = User::factory()->create();

        $user->assignRole(
            EnumsRole::USER->value,
        );

        $response = $this->apiPut("/users/{$user->id}", [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => EnumsRole::SUPER_ADMIN->value,
        ]);

        $response
            ->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 409)
            ->assertJsonPath(
                'message',
                __('users.super_admin_already_assigned'),
            );

        $this->assertTrue(
            $existingSuperAdmin->fresh()->hasRole(
                EnumsRole::SUPER_ADMIN->value,
            ),
        );

        $this->assertFalse(
            $user->fresh()->hasRole(
                EnumsRole::SUPER_ADMIN->value,
            ),
        );

        $this->assertSame(
            1,
            User::role(EnumsRole::SUPER_ADMIN->value, 'sanctum')->count(),
        );
    }

    public function test_super_admin_cannot_be_modified_even_when_retaining_role(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $user->assignRole(
            EnumsRole::SUPER_ADMIN->value,
        );

        $response = $this->apiPut("/users/{$user->id}", [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => $user->email,
            'role' => EnumsRole::SUPER_ADMIN->value,
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403);

        $this->assertTrue(
            $user->fresh()->hasRole(
                EnumsRole::SUPER_ADMIN->value,
            ),
        );
    }

    public function test_super_admin_cannot_be_demoted_through_user_management(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
        ]);

        $user->assignRole(
            EnumsRole::SUPER_ADMIN->value,
        );

        $response = $this->apiPut("/users/{$user->id}", [
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => $user->email,
            'role' => EnumsRole::ADMIN->value,
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403);

        $this->assertTrue(
            $user->fresh()->hasRole(
                EnumsRole::SUPER_ADMIN->value,
            ),
        );

        $this->assertFalse(
            $user->fresh()->hasRole(
                EnumsRole::ADMIN->value,
            ),
        );
    }

    public function test_only_one_super_admin_exists_after_role_assignment(): void
    {
        $firstUser = User::factory()->create();

        $firstUser->assignRole(
            EnumsRole::SUPER_ADMIN->value,
        );

        $secondUser = User::factory()->create();

        $secondUser->assignRole(
            EnumsRole::USER->value,
        );

        $response = $this->apiPut("/users/{$secondUser->id}", [
            'first_name' => $secondUser->first_name,
            'last_name' => $secondUser->last_name,
            'email' => $secondUser->email,
            'role' => EnumsRole::SUPER_ADMIN->value,
        ]);

        $response
            ->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 409)
            ->assertJsonPath(
                'message',
                __('users.super_admin_already_assigned'),
            );

        $this->assertSame(
            1,
            User::role(EnumsRole::SUPER_ADMIN->value, 'sanctum')->count(),
        );
    }
}
