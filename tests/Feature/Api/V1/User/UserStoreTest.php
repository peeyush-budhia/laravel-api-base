<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Enums\Role as RoleEnum;
use App\Enums\UserStatus as UserStatusEnum;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Feature\Api\V1\ApiTestCase;
use Tests\Feature\Api\V1\Concerns\InteractsWithPermissions;

final class UserStoreTest extends ApiTestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create([
            'name' => RoleEnum::ADMIN,
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
            'role' => RoleEnum::ADMIN,
        ]);

        $response = $this->apiPost('/users', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', __('responses.created'))
            ->assertJsonPath('data.first_name', 'John')
            ->assertJsonPath('data.last_name', 'Doe')
            ->assertJsonPath('data.email', $newEmail)
            ->assertJsonPath('data.status', UserStatusEnum::ACTIVE)
            ->assertJsonPath('data.role', RoleEnum::ADMIN);

        $this->assertDatabaseHas('users', [
            'email' => $newEmail,
        ]);

        $user = User::where('email', $newEmail)->firstOrFail();

        $this->assertTrue(
            $user->hasRole(RoleEnum::ADMIN),
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
                'password',
            ]);
    }
}
