<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\V1\ApiTestCase;

final class UserIndexTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user->delete();
    }

    public function test_users_can_be_listed(): void
    {
        User::factory()->count(10)->create();

        $response = $this->apiGet('/users');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', __('responses.success'))
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'full_name',
                        'email',
                        'avatar',
                        'status',
                        'email_verified_at',
                        'last_login_at',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                    ],
                ],
                'meta',
            ]);
    }

    public function test_users_are_paginated(): void
    {
        User::factory()->count(30)->create();

        $response = $this->apiGet('/users');

        //         dd($response->json());

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);
    }

    public function test_users_can_be_paginated_with_custom_per_page(): void
    {
        User::factory()->count(30)->create();

        $response = $this->apiGet('/users?per_page=5');

        $response
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5);
    }

    public function test_users_can_be_searched(): void
    {
        User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        User::factory()->create([
            'first_name' => 'Peeyush',
            'last_name' => 'Budhia',
            'email' => 'peeyush@example.com',
        ]);

        User::factory()->create([
            'first_name' => 'Michael',
            'last_name' => 'Smith',
            'email' => 'michael@example.com',
        ]);

        $response = $this->apiGet('/users?search=peeyush');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.first_name', 'Peeyush')
            ->assertJsonPath('data.0.last_name', 'Budhia');
    }

    public function test_users_can_be_searched_by_last_name(): void
    {
        User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Budhia',
            'email' => 'john@example.com',
        ]);

        User::factory()->create([
            'first_name' => 'Peeyush',
            'last_name' => 'Sharma',
            'email' => 'peeyush@example.com',
        ]);

        $response = $this->apiGet('/users?search=budhia');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.last_name', 'Budhia');
    }

    public function test_users_can_be_searched_by_email(): void
    {
        User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'unique@example.com',
        ]);

        User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'other@example.com',
        ]);

        $response = $this->apiGet('/users?search=unique@example.com');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'unique@example.com');
    }

    public function test_users_can_be_filtered_by_status(): void
    {
        User::factory()->create([
            'first_name' => 'Active',
            'status' => UserStatus::ACTIVE,
        ]);

        User::factory()->create([
            'first_name' => 'Inactive',
            'status' => UserStatus::INACTIVE,
        ]);

        $response = $this->apiGet('/users?status=active');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.first_name', 'Active')
            ->assertJsonPath(
                'data.0.status',
                UserStatus::ACTIVE->value,
            );
    }

    public function test_users_can_be_filtered_by_inactive_status(): void
    {
        User::factory()->create([
            'first_name' => 'Active',
            'status' => UserStatus::ACTIVE,
        ]);

        User::factory()->create([
            'first_name' => 'Inactive',
            'status' => UserStatus::INACTIVE,
        ]);

        $response = $this->apiGet('/users?status=inactive');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.first_name', 'Inactive')
            ->assertJsonPath(
                'data.0.status',
                UserStatus::INACTIVE->value,
            );
    }

    public function test_unsupported_filters_are_ignored(): void
    {
        User::factory()->create([
            'first_name' => 'John',
            'email' => 'john@example.com',
        ]);

        User::factory()->create([
            'first_name' => 'Jane',
            'email' => 'jane@example.com',
        ]);

        $response = $this->apiGet(
            '/users?unsupported=value'
        );

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
