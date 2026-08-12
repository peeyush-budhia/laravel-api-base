<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Models\User;
use Tests\Feature\Api\V1\ApiTestCase;

final class UserProfileTest extends ApiTestCase
{
    public function test_authenticated_user_can_update_own_profile(): void
    {
        $newEmail = fake()->unique()->safeEmail();

        $response = $this->apiPut('/profile', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => $newEmail,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath(
                'message',
                __('responses.updated'),
            )
            ->assertJsonPath(
                'data.id',
                $this->user->id,
            )
            ->assertJsonPath(
                'data.first_name',
                'Jane',
            )
            ->assertJsonPath(
                'data.last_name',
                'Smith',
            )
            ->assertJsonPath(
                'data.email',
                $newEmail,
            );

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => $newEmail,
        ]);
    }

    public function test_profile_update_does_not_change_password(): void
    {
        $user = $this->user->fresh();

        $oldPassword = $user->password;

        $response = $this->apiPut('/profile', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => $user->email,
        ]);

        $response->assertOk();

        $this->assertSame(
            $oldPassword,
            $this->user->fresh()->password,
        );
    }

    public function test_authenticated_user_can_keep_existing_email(): void
    {
        $response = $this->apiPut('/profile', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => $this->user->email,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.email',
                $this->user->email,
            );
    }

    public function test_profile_update_requires_first_name(): void
    {
        $response = $this->apiPut('/profile', [
            'last_name' => 'Smith',
            'email' => $this->user->email,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 422)
            ->assertJsonStructure([
                'errors' => [
                    'first_name',
                ],
            ]);
    }

    public function test_profile_update_requires_last_name(): void
    {
        $response = $this->apiPut('/profile', [
            'first_name' => 'Jane',
            'email' => $this->user->email,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 422)
            ->assertJsonStructure([
                'errors' => [
                    'last_name',
                ],
            ]);
    }

    public function test_profile_update_requires_email(): void
    {
        $response = $this->apiPut('/profile', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 422)
            ->assertJsonStructure([
                'errors' => [
                    'email',
                ],
            ]);
    }

    public function test_profile_update_rejects_invalid_email(): void
    {
        $response = $this->apiPut('/profile', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'not-an-email',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 422)
            ->assertJsonStructure([
                'errors' => [
                    'email',
                ],
            ]);
    }

    public function test_profile_update_rejects_duplicate_email(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->apiPut('/profile', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => $otherUser->email,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 422)
            ->assertJsonStructure([
                'errors' => [
                    'email',
                ],
            ]);
    }

    public function test_guest_cannot_update_profile(): void
    {
        $this->app['auth']->forgetGuards();

        $response = $this->putJson(
            '/api/v1/profile',
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane@example.com',
            ],
        );

        $response
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 401);
    }

    public function test_profile_update_rejects_email_without_domain_suffix(): void
    {
        $response = $this->apiPut('/profile', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 422)
            ->assertJsonStructure([
                'errors' => [
                    'email',
                ],
            ]);
    }

    public function test_profile_update_rejects_email_without_at_symbol(): void
    {
        $response = $this->apiPut('/profile', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.example.com',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.email', fn ($errors) => count($errors) > 0);
    }

    public function test_profile_update_rejects_email_with_missing_local_part(): void
    {
        $response = $this->apiPut('/profile', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => '@example.com',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.email', fn ($errors) => count($errors) > 0);
    }
}
