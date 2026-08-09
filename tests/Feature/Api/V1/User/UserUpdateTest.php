<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Enums\UserStatus;
use App\Models\User;
use Tests\Feature\Api\V1\ApiTestCase;
use Tests\Feature\Api\V1\Concerns\InteractsWithPermissions;

final class UserUpdateTest extends ApiTestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();

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

        $newEmail = fake()->unique()->safeEmail();

        $payload = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => $newEmail,
            'status' => UserStatus::INACTIVE->value,
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
            );

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => $newEmail,
            'status' => UserStatus::INACTIVE->value,
        ]);
    }

    public function test_password_is_not_updated_when_password_is_not_provided(): void
    {
        $user = User::factory()->create();

        $oldPassword = $user->password;

        $response = $this->apiPut("/users/{$user->id}", [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
        ]);

        $response->assertOk();

        $this->assertSame(
            $oldPassword,
            $user->fresh()->password,
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
}
