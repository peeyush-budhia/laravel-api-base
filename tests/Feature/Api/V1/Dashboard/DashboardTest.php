<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Dashboard;

use App\Enums\AuditEvent;
use App\Enums\Permission as PermissionEnum;
use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function createUserWithDashboardPermission(
        array $additionalPermissions = [],
    ): User {
        $user = User::factory()->create([
        ]);

        $user->givePermissionTo([
            PermissionEnum::DASHBOARD_VIEW->value,
            ...$additionalPermissions,
        ]);

        return $user;
    }

    private function createAuditLog(
        ?User $user = null,
        array $attributes = [],
    ): AuditLog {
        $auditable = $user ?? User::factory()->create();

        return AuditLog::create(array_merge([
            'user_id' => $user?->id,
            'event' => AuditEvent::Created,
            'auditable_type' => User::class,
            'auditable_id' => $auditable->id,
            'old_values' => null,
            'new_values' => [
                'name' => 'Test User',
            ],
            'url' => 'http://example.test/api/v1/users',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    public function test_unauthenticated_user_cannot_view_dashboard(): void
    {
        $response = $this->getJson('/api/v1/dashboard');

        $response->assertUnauthorized();
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    public function test_user_without_dashboard_permission_cannot_view_dashboard(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response->assertForbidden();
    }

    public function test_user_with_dashboard_permission_can_view_dashboard(): void
    {
        $user = $this->createUserWithDashboardPermission();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'data',
                'errors',
            ]);
    }

    public function test_dashboard_permission_does_not_expose_user_or_audit_details(): void
    {
        $user = $this->createUserWithDashboardPermission();

        User::factory()->create();
        $this->createAuditLog($user);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonCount(0, 'data.users.recent')
            ->assertJsonCount(0, 'data.users.recently_active')
            ->assertJsonCount(0, 'data.audit.recent');
    }

    public function test_dashboard_cache_keeps_permission_scopes_separate(): void
    {
        $limitedUser = $this->createUserWithDashboardPermission();
        $privilegedUser = $this->createUserWithDashboardPermission([
            PermissionEnum::USERS_VIEW->value,
            PermissionEnum::AUDIT_LOGS_VIEW->value,
        ]);

        $auditLog = $this->createAuditLog($privilegedUser, [
            'url' => 'http://example.test/api/v1/sensitive-audit-entry',
            'ip_address' => '203.0.113.50',
        ]);

        Sanctum::actingAs($limitedUser);

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonCount(0, 'data.users.recent')
            ->assertJsonCount(0, 'data.audit.recent');

        Sanctum::actingAs($privilegedUser);

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonCount(2, 'data.users.recent')
            ->assertJsonFragment([
                'id' => $auditLog->id,
                'ip_address' => '203.0.113.50',
            ]);

        Sanctum::actingAs($limitedUser);

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonCount(0, 'data.users.recent')
            ->assertJsonCount(0, 'data.audit.recent');
    }

    /*
    |--------------------------------------------------------------------------
    | Response Structure
    |--------------------------------------------------------------------------
    */

    public function test_dashboard_response_contains_expected_structure(): void
    {
        $user = $this->createUserWithDashboardPermission();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'data' => [
                    'summary' => [
                        'users' => [
                            'total',
                            'active',
                            'inactive',
                            'suspended',
                        ],
                        'roles' => [
                            'total',
                        ],
                        'permissions' => [
                            'total',
                        ],
                        'audit_logs' => [
                            'total',
                        ],
                    ],
                    'users' => [
                        'by_status',
                        'recent',
                        'recently_active',
                    ],
                    'audit' => [
                        'by_event',
                        'recent',
                    ],
                ],
                'errors',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Summary Statistics
    |--------------------------------------------------------------------------
    */

    public function test_dashboard_returns_correct_user_summary_statistics(): void
    {
        $user = $this->createUserWithDashboardPermission();

        User::factory()->create([
            'status' => UserStatus::ACTIVE,
        ]);

        User::factory()->create([
            'status' => UserStatus::ACTIVE,
        ]);

        User::factory()->create([
            'status' => UserStatus::INACTIVE,
        ]);

        User::factory()->create([
            'status' => UserStatus::SUSPENDED,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.summary.users.total',
                5,
            )
            ->assertJsonPath(
                'data.summary.users.active',
                3,
            )
            ->assertJsonPath(
                'data.summary.users.inactive',
                1,
            )
            ->assertJsonPath(
                'data.summary.users.suspended',
                1,
            );
    }

    public function test_dashboard_returns_correct_role_count(): void
    {
        $user = $this->createUserWithDashboardPermission();

        Role::query()->delete();

        Role::create([
            'name' => 'Test Role 1',
            'guard_name' => 'sanctum',
        ]);

        Role::create([
            'name' => 'Test Role 2',
            'guard_name' => 'sanctum',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.summary.roles.total',
                2,
            );
    }

    public function test_dashboard_returns_correct_permission_count(): void
    {
        $user = $this->createUserWithDashboardPermission();

        $expected = Permission::query()
            ->where('guard_name', 'sanctum')
            ->count();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.summary.permissions.total',
                $expected,
            );
    }

    public function test_dashboard_returns_correct_audit_log_count(): void
    {
        $user = $this->createUserWithDashboardPermission();

        AuditLog::query()->delete();

        $auditLogs = [
            $this->createAuditLog($user),
            $this->createAuditLog($user),
            $this->createAuditLog($user),
        ];

        $this->assertCount(3, $auditLogs);
        $this->assertDatabaseCount('audit_logs', 3);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.summary.audit_logs.total',
                3,
            );
    }

    /*
    |--------------------------------------------------------------------------
    | User Statistics
    |--------------------------------------------------------------------------
    */

    public function test_dashboard_returns_users_grouped_by_status(): void
    {
        $user = $this->createUserWithDashboardPermission();

        User::factory()->count(2)->create([
            'status' => UserStatus::ACTIVE,
        ]);

        User::factory()->create([
            'status' => UserStatus::INACTIVE,
        ]);

        User::factory()->create([
            'status' => UserStatus::SUSPENDED,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.users.by_status.active',
                3,
            )
            ->assertJsonPath(
                'data.users.by_status.inactive',
                1,
            )
            ->assertJsonPath(
                'data.users.by_status.suspended',
                1,
            );
    }

    public function test_dashboard_returns_recent_users(): void
    {
        $user = $this->createUserWithDashboardPermission([
            PermissionEnum::USERS_VIEW->value,
        ]);
        $user->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->saveQuietly();

        $recentUsers = User::factory()
            ->count(5)
            ->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonCount(
                5,
                'data.users.recent',
            );

        foreach ($recentUsers as $recentUser) {
            $response->assertJsonFragment([
                'id' => $recentUser->id,
                'email' => $recentUser->email,
            ]);
        }
    }

    public function test_dashboard_returns_at_most_five_recent_users(): void
    {
        $user = $this->createUserWithDashboardPermission([
            PermissionEnum::USERS_VIEW->value,
        ]);

        User::factory()->count(8)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonCount(
                5,
                'data.users.recent',
            );

        $this->assertIso8601DateTime(
            $response->json('data.users.recent.0.created_at'),
        );

        $this->assertIso8601DateTime(
            $response->json('data.users.recent.0.updated_at'),
        );
    }

    public function test_dashboard_returns_recently_active_users(): void
    {
        $user = $this->createUserWithDashboardPermission([
            PermissionEnum::USERS_VIEW->value,
        ]);

        User::factory()->count(3)->create([
            'last_login_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonCount(
                3,
                'data.users.recently_active',
            );
    }

    public function test_dashboard_returns_at_most_five_recently_active_users(): void
    {
        $user = $this->createUserWithDashboardPermission([
            PermissionEnum::USERS_VIEW->value,
        ]);

        User::factory()->count(8)->create([
            'last_login_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonCount(
                5,
                'data.users.recently_active',
            );

        $this->assertIso8601DateTime(
            $response->json('data.users.recently_active.0.last_login_at'),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Audit Statistics
    |--------------------------------------------------------------------------
    */

    public function test_dashboard_returns_audit_events_grouped_by_event(): void
    {
        $user = $this->createUserWithDashboardPermission();

        AuditLog::query()->delete();

        $this->createAuditLog($user, [
            'event' => AuditEvent::Created,
        ]);

        $this->createAuditLog($user, [
            'event' => AuditEvent::Created,
        ]);

        $this->createAuditLog($user, [
            'event' => AuditEvent::Updated,
        ]);

        $this->createAuditLog($user, [
            'event' => AuditEvent::Deleted,
        ]);

        $this->assertDatabaseCount('audit_logs', 4);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.audit.by_event.created',
                2,
            )
            ->assertJsonPath(
                'data.audit.by_event.updated',
                1,
            )
            ->assertJsonPath(
                'data.audit.by_event.deleted',
                1,
            );
    }

    public function test_dashboard_returns_recent_audit_logs(): void
    {
        $user = $this->createUserWithDashboardPermission([
            PermissionEnum::AUDIT_LOGS_VIEW->value,
        ]);

        AuditLog::query()->delete();

        $this->createAuditLog($user);
        $this->createAuditLog($user);
        $this->createAuditLog($user);

        $this->assertDatabaseCount('audit_logs', 3);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonCount(
                3,
                'data.audit.recent',
            );
    }

    public function test_dashboard_returns_at_most_six_recent_audit_logs(): void
    {
        $user = $this->createUserWithDashboardPermission([
            PermissionEnum::AUDIT_LOGS_VIEW->value,
        ]);

        AuditLog::query()->delete();

        for ($i = 0; $i < 15; $i++) {
            $this->createAuditLog($user);
        }

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonCount(
                6,
                'data.audit.recent',
            );

        $this->assertIso8601DateTime(
            $response->json('data.audit.recent.0.created_at'),
        );

        $this->assertIso8601DateTime(
            $response->json('data.audit.recent.0.updated_at'),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Empty Dashboard
    |--------------------------------------------------------------------------
    */

    public function test_dashboard_handles_empty_audit_logs(): void
    {
        $user = $this->createUserWithDashboardPermission();

        AuditLog::query()->delete();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.summary.audit_logs.total',
                0,
            )
            ->assertJsonCount(
                0,
                'data.audit.recent',
            );
    }

    public function test_dashboard_returns_full_avatar_url_for_recent_users(): void
    {
        $user = $this->createUserWithDashboardPermission([
            PermissionEnum::USERS_VIEW->value,
        ]);

        User::factory()->create([
            'avatar' => 'avatars/test-avatar.png',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'avatar' => asset('storage/avatars/test-avatar.png'),
            ]);
    }
}
