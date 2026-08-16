<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Enums\UserStatus;
use App\Models\User;
use Tests\Feature\Api\V1\ApiTestCase;
use Tests\Feature\Api\V1\Concerns\InteractsWithPermissions;

final class UserIndexTest extends ApiTestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user->update([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test-user@example.com',
        ]);

        $this->givePermission(
            $this->user,
            'users.view',
        );

        // $this->user->delete();
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
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'first_name' => 'Active',
                'status' => UserStatus::ACTIVE->value,
            ]);
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

        $response = $this->apiGet('/users?unsupported=value');

        $response
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_page_is_normalized_to_one(): void
    {
        User::factory()->count(5)->create();

        $response = $this->apiGet('/users?page=-5');

        $response
            ->assertOk()
            ->assertJsonPath('meta.current_page', 1);
    }

    public function test_per_page_is_clamped_to_minimum(): void
    {
        User::factory()->count(5)->create();

        $response = $this->apiGet('/users?per_page=-10');

        $response
            ->assertOk()
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonCount(1, 'data');
    }

    public function test_per_page_is_clamped_to_maximum(): void
    {
        User::factory()->count(105)->create();

        $response = $this->apiGet('/users?per_page=1000');

        $response
            ->assertOk()
            ->assertJsonPath('meta.per_page', 100)
            ->assertJsonCount(100, 'data');
    }

    public function test_invalid_direction_defaults_to_ascending(): void
    {
        User::factory()->create([
            'first_name' => 'Zack',
        ]);

        User::factory()->create([
            'first_name' => 'Alice',
        ]);

        $response = $this->apiGet(
            '/users?sort=first_name&direction=invalid',
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.0.first_name', 'Alice')
            ->assertJsonFragment([
                'first_name' => 'Zack',
            ]);
    }

    public function test_unsupported_sort_is_ignored(): void
    {
        User::factory()->create([
            'first_name' => 'Zack',
        ]);

        User::factory()->create([
            'first_name' => 'Alice',
        ]);

        $response = $this->apiGet(
            '/users?sort=email&direction=desc',
        );

        $response
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_users_can_be_sorted_by_allowed_field(): void
    {
        User::factory()->create([
            'first_name' => 'Zack',
        ]);

        User::factory()->create([
            'first_name' => 'Alice',
        ]);

        $response = $this->apiGet(
            '/users?sort=first_name&direction=asc',
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.0.first_name', 'Alice')
            ->assertJsonFragment([
                'first_name' => 'Zack',
            ]);
    }

    public function test_search_returns_empty_data_when_no_users_match(): void
    {
        User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->apiGet('/users?search=does-not-exist');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', __('responses.success'))
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);
    }

    public function test_deleted_users_are_excluded_by_default(): void
    {
        User::factory()->create([
            'first_name' => 'Active',
        ]);

        $deletedUser = User::factory()->create([
            'first_name' => 'Deleted',
        ]);

        $deletedUser->delete();

        $response = $this->apiGet('/users');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'first_name' => 'Active',
            ])
            ->assertJsonMissing([
                'first_name' => 'Deleted',
            ]);
    }

    public function test_users_can_list_only_deleted_users(): void
    {
        User::factory()->create([
            'first_name' => 'Active',
        ]);

        $deletedUser = User::factory()->create([
            'first_name' => 'Deleted',
        ]);

        $deletedUser->delete();

        $response = $this->apiGet('/users?trashed=only');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.first_name', 'Deleted');

        $this->assertNotNull(
            $response->json('data.0.deleted_at'),
        );
    }

    public function test_users_can_list_with_deleted_users(): void
    {
        User::factory()->create([
            'first_name' => 'Active',
        ]);

        $deletedUser = User::factory()->create([
            'first_name' => 'Deleted',
        ]);

        $deletedUser->delete();

        $response = $this->apiGet('/users?trashed=with');

        $response
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_invalid_trashed_value_defaults_to_without(): void
    {
        User::factory()->create([
            'first_name' => 'Active',
        ]);

        $deletedUser = User::factory()->create([
            'first_name' => 'Deleted',
        ]);

        $deletedUser->delete();

        $response = $this->apiGet('/users?trashed=invalid');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
