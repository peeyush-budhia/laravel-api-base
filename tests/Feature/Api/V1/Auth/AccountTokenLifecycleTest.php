<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AccountTokenLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_change_revokes_other_bearer_tokens(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        $currentToken = $user->createToken('current-session');
        $otherToken = $user->createToken('other-session');

        $this->withToken($currentToken->plainTextToken)
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => 'OldPassword123!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ])
            ->assertOk();

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $currentToken->accessToken->getKey(),
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $otherToken->accessToken->getKey(),
        ]);

        Auth::forgetGuards();

        $this->withToken($currentToken->plainTextToken)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);

        Auth::forgetGuards();

        $this->withToken($otherToken->plainTextToken)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }

    public function test_soft_deletion_revokes_tokens_permanently_after_restoration(): void
    {
        $actor = User::factory()->create([
        ]);
        $actor->givePermissionTo(
            Permission::findOrCreate('users.delete', 'sanctum'),
            Permission::findOrCreate('users.restore', 'sanctum'),
        );

        $target = User::factory()->create([
        ]);

        $actorToken = $actor->createToken('administrator');
        $targetToken = $target->createToken('target-session');
        $target->createToken('target-other-session');

        $this->withToken($actorToken->plainTextToken)
            ->deleteJson("/api/v1/users/{$target->id}")
            ->assertOk();

        $this->assertSoftDeleted('users', [
            'id' => $target->id,
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $target->id,
        ]);

        Auth::forgetGuards();

        $this->withToken($targetToken->plainTextToken)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();

        Auth::forgetGuards();

        $this->withToken($actorToken->plainTextToken)
            ->patchJson("/api/v1/users/{$target->id}/restore")
            ->assertOk();

        Auth::forgetGuards();

        $this->withToken($targetToken->plainTextToken)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $target->id,
        ]);
    }
}
