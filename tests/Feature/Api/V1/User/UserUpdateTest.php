<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Enums\Role as EnumsRole;
use App\Enums\UserStatus;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Feature\Api\V1\ApiTestCase;
use Tests\Feature\Api\V1\Concerns\InteractsWithPermissions;

final class UserUpdateTest extends ApiTestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();

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

        $response = $this->apiPut("/users/{$user->id}", $payload);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath('message', __('responses.updated'))
            ->assertJsonPath('data.first_name', 'Jane')
            ->assertJsonPath('data.last_name', 'Smith')
            ->assertJsonPath('data.email', $newEmail)
            ->assertJsonPath(
                'data.status',
                UserStatus::INACTIVE->value,
            )
            ->assertJsonPath('data.role', EnumsRole::ADMIN->value);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => $newEmail,
            'status' => UserStatus::INACTIVE->value,
        ]);

        $updatedUser = $user->fresh();

        $this->assertTrue(
            $updatedUser->hasRole(EnumsRole::ADMIN->value),
        );

        $this->assertFalse(
            $updatedUser->hasRole(EnumsRole::USER->value),
        );
    }

    public function test_password_is_not_updated_when_password_is_not_provided(): void
    {
        $user = User::factory()->create();

        $oldPassword = $user->password;

        $response = $this->apiPut("/users/{$user->id}", [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => EnumsRole::USER->value,

        ]);

        $response->assertOk();

        $this->assertSame(
            $oldPassword,
            $user->fresh()->password,
        );
    }

    public function test_user_role_must_exist_when_updating(): void
    {
        $user = User::factory()->create();

        $response = $this->apiPut("/users/{$user->id}", [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => $user->email,
            'role' => 'non-existent-role',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'role',
            ]);
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
}
