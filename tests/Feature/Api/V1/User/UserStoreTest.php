<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Enums\Role as EnumsRole;
use App\Enums\UserStatus as EnumsUserStatus;
use App\Models\Role;
use App\Models\User;
use App\Notifications\User\UserCreatedNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Api\V1\ApiTestCase;
use Tests\Feature\Api\V1\Concerns\InteractsWithPermissions;

final class UserStoreTest extends ApiTestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create([
            'name' => EnumsRole::ADMIN,
            'guard_name' => 'sanctum',
        ]);

        $this->givePermission(
            $this->user,
            'users.create',
        );
    }

    public function test_user_can_be_created(): void
    {
        $newEmail = fake()->unique()->safeEmail();

        $payload = $this->validUserData([
            'email' => $newEmail,
            'role' => EnumsRole::ADMIN,
        ]);

        $response = $this->apiPost('/users', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', __('responses.created'))
            ->assertJsonPath('data.first_name', 'John')
            ->assertJsonPath('data.last_name', 'Doe')
            ->assertJsonPath('data.email', $newEmail)
            ->assertJsonPath('data.status', EnumsUserStatus::ACTIVE)
            ->assertJsonPath('data.role', EnumsRole::ADMIN);

        $this->assertDatabaseHas('users', [
            'email' => $newEmail,
        ]);

        $user = User::where('email', $newEmail)->firstOrFail();

        $this->assertTrue(
            $user->hasRole(EnumsRole::ADMIN),
        );

        $this->assertTrue(
            $user->must_change_password,
        );

        $this->assertNull(
            $user->email_verified_at,
        );
    }

    public function test_new_user_is_not_email_verified(): void
    {
        $newEmail = fake()->unique()->safeEmail();

        $payload = $this->validUserData([
            'email' => $newEmail,
            'role' => EnumsRole::ADMIN,
        ]);

        $response = $this->apiPost('/users', $payload);

        $response->assertCreated();

        $user = User::where('email', $newEmail)->firstOrFail();

        $this->assertNull(
            $user->email_verified_at,
        );

        $this->assertTrue(
            $user->must_change_password,
        );
    }

    public function test_user_role_must_exist(): void
    {
        $response = $this->apiPost('/users', $this->validUserData([
            'role' => 'non-existent-role',
        ]));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'role',
            ]);
    }

    public function test_user_email_must_be_unique(): void
    {
        $newEmail = fake()->unique()->safeEmail();
        User::factory()->create([
            'email' => $newEmail,
        ]);

        $response = $this->apiPost('/users', $this->validUserData([
            'email' => $newEmail,
        ]));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email',
            ]);
    }

    public function test_user_requires_required_fields(): void
    {
        $response = $this->apiPost('/users', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'email',
                'role',
            ]);
    }

    public function test_user_created_notification_is_sent(): void
    {
        Notification::fake();

        $newEmail = fake()->unique()->safeEmail();

        $response = $this->apiPost('/users', $this->validUserData([
            'email' => $newEmail,
            'role' => EnumsRole::ADMIN,
        ]));

        $response->assertCreated();

        $user = User::where('email', $newEmail)->firstOrFail();

        Notification::assertSentTo(
            $user,
            UserCreatedNotification::class,
        );
    }

    public function test_password_is_generated_for_new_user(): void
    {
        Notification::fake();

        $newEmail = fake()->unique()->safeEmail();

        $response = $this->apiPost('/users', $this->validUserData([
            'email' => $newEmail,
            'role' => EnumsRole::ADMIN,
        ]));

        $response->assertCreated();

        $user = User::where('email', $newEmail)->firstOrFail();

        $this->assertNotEmpty($user->password);

        $this->assertTrue(
            Hash::needsRehash($user->password) === false,
        );
    }

    public function test_generated_password_is_not_exposed_in_response(): void
    {
        Notification::fake();

        $response = $this->apiPost('/users', $this->validUserData([
            'role' => EnumsRole::ADMIN,
        ]));

        $response
            ->assertCreated()
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.password_confirmation');
    }

    public function test_first_super_admin_can_be_created(): void
    {
        Role::create([
            'name' => EnumsRole::SUPER_ADMIN->value,
            'guard_name' => 'sanctum',
        ]);

        $newEmail = fake()->unique()->safeEmail();

        $response = $this->apiPost('/users', $this->validUserData([
            'email' => $newEmail,
            'role' => EnumsRole::SUPER_ADMIN->value,
        ]));

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 201)
            ->assertJsonPath(
                'data.role',
                EnumsRole::SUPER_ADMIN->value,
            );

        $user = User::where('email', $newEmail)->firstOrFail();

        $this->assertTrue(
            $user->hasRole(EnumsRole::SUPER_ADMIN->value),
        );
    }

    public function test_second_super_admin_cannot_be_created(): void
    {
        $role = Role::create([
            'name' => EnumsRole::SUPER_ADMIN->value,
            'guard_name' => 'sanctum',
        ]);

        $existingSuperAdmin = User::factory()->create();

        $existingSuperAdmin->assignRole($role);

        $response = $this->apiPost('/users', $this->validUserData([
            'email' => fake()->unique()->safeEmail(),
            'role' => EnumsRole::SUPER_ADMIN->value,
        ]));

        $response
            ->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 409)
            ->assertJsonPath(
                'message',
                __('users.super_admin_already_assigned'),
            );

        $this->assertDatabaseCount('users', 2);

        $this->assertSame(
            1,
            User::role(EnumsRole::SUPER_ADMIN->value, 'sanctum')->count(),
        );
    }
}
