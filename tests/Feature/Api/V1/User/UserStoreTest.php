<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Models\User;
use Tests\Feature\Api\V1\ApiTestCase;

final class UserStoreTest extends ApiTestCase
{
    public function test_user_can_be_created(): void
    {
        $payload = $this->validUserData([
            'email' => 'john@example.com',
        ]);

        $response = $this->apiPost('/users', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', __('responses.created'))
            ->assertJsonPath('data.first_name', 'John')
            ->assertJsonPath('data.last_name', 'Doe')
            ->assertJsonPath('data.email', 'john@example.com')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_user_email_must_be_unique(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $response = $this->apiPost('/users', $this->validUserData([
            'email' => 'john@example.com',
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
