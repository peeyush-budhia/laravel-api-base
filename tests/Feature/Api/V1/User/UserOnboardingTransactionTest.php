<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Models\Role;
use App\Services\User\UserService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class UserOnboardingTransactionTest extends TestCase
{
    use DatabaseMigrations;

    private const string MEMBER_ROLE = 'member';

    protected function setUp(): void
    {
        parent::setUp();

        config(['queue.default' => 'database']);

        Role::create([
            'name' => self::MEMBER_ROLE,
            'guard_name' => 'sanctum',
        ]);
    }

    public function test_onboarding_notification_is_queued_after_commit(): void
    {
        DB::beginTransaction();

        $user = app(UserService::class)->store($this->userData());

        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);

        DB::commit();

        $this->assertDatabaseCount('jobs', 1);

        $payload = DB::table('jobs')->value('payload');

        $this->assertIsString($payload);
        $this->assertStringContainsString(
            'UserCreatedNotification',
            $payload,
        );
        $this->assertStringNotContainsString(
            'temporaryPassword',
            $payload,
        );
    }

    public function test_rollback_discards_activation_and_notification(): void
    {
        DB::beginTransaction();

        $user = app(UserService::class)->store($this->userData());

        DB::rollBack();

        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function userData(): array
    {
        return [
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => fake()->unique()->safeEmail(),
            'role' => self::MEMBER_ROLE,
        ];
    }
}
