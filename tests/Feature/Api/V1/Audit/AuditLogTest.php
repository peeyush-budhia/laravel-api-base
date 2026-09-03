<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Audit;

use App\Enums\AuditEvent;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuditLogTest extends TestCase
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

    private function createUserWithPermission(
        string $permission = 'audit-logs.view',
    ): User {
        $user = User::factory()->create();

        $user->givePermissionTo($permission);

        return $user;
    }

    private function createAuditLog(
        ?User $user = null,
        array $attributes = [],
    ): AuditLog {
        $auditable = User::factory()->createQuietly();

        return AuditLog::create(array_merge([
            'user_id' => $user?->id,
            'event' => AuditEvent::Created,
            'auditable_type' => User::class,
            'auditable_id' => $auditable->id,
            'old_values' => null,
            'new_values' => [
                'name' => 'Test User',
            ],
            'url' => 'http://api-base.test/api/v1/users',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    public function test_unauthenticated_user_cannot_view_audit_logs(): void
    {
        $response = $this->getJson('/api/v1/audit-logs');

        $response->assertUnauthorized();
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    public function test_user_without_audit_log_permission_cannot_view_audit_logs(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/audit-logs');

        $response->assertForbidden();
    }

    public function test_user_with_audit_log_permission_can_view_audit_logs(): void
    {
        $user = $this->createUserWithPermission();

        $this->createAuditLog($user);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/audit-logs');

        $response
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Listing
    |--------------------------------------------------------------------------
    */

    public function test_audit_logs_are_paginated(): void
    {
        $user = $this->createUserWithPermission();

        AuditLog::query()->delete();

        for ($i = 0; $i < 5; $i++) {
            $this->createAuditLog($user);
        }

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/audit-logs?per_page=2');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'data',
                'errors',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                    'from',
                    'to',
                    'path',
                    'links',
                ],
            ]);

        $response->assertJsonPath('meta.per_page', 2);
        $response->assertJsonPath('meta.total', 5);
    }

    public function test_audit_logs_can_be_filtered_by_event(): void
    {
        $user = $this->createUserWithPermission();

        $this->createAuditLog(
            $user,
            ['event' => AuditEvent::Created],
        );

        $this->createAuditLog(
            $user,
            ['event' => AuditEvent::Updated],
        );

        Sanctum::actingAs($user);

        $response = $this->getJson(
            '/api/v1/audit-logs?event=updated',
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true);

        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath(
            'data.0.event',
            AuditEvent::Updated->value,
        );
    }

    public function test_audit_logs_can_be_filtered_by_user(): void
    {
        $viewer = $this->createUserWithPermission();

        $auditingUser = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->createAuditLog($auditingUser);
        $this->createAuditLog($otherUser);

        Sanctum::actingAs($viewer);

        $response = $this->getJson(
            "/api/v1/audit-logs?user_id={$auditingUser->id}",
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true);

        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath(
            'data.0.user_id',
            $auditingUser->id,
        );
    }

    public function test_audit_logs_can_be_sorted(): void
    {
        $user = $this->createUserWithPermission();

        $old = $this->createAuditLog($user, [
            'created_at' => now()->subDay(),
        ]);

        $new = $this->createAuditLog($user, [
            'created_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(
            '/api/v1/audit-logs?sort=created_at&direction=desc',
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true);

        $response->assertJsonPath(
            'data.0.id',
            $new->id,
        );

        $this->assertNotSame(
            $old->id,
            $new->id,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Audit Events
    |--------------------------------------------------------------------------
    */

    public function test_created_event_is_recorded(): void
    {
        $user = $this->createUserWithPermission();

        $auditLog = $this->createAuditLog(
            $user,
            ['event' => AuditEvent::Created],
        );

        $this->assertDatabaseHas('audit_logs', [
            'id' => $auditLog->id,
            'user_id' => $user->id,
            'event' => AuditEvent::Created->value,
        ]);
    }

    public function test_updated_event_is_recorded(): void
    {
        $user = $this->createUserWithPermission();

        $auditLog = $this->createAuditLog(
            $user,
            [
                'event' => AuditEvent::Updated,
                'old_values' => [
                    'name' => 'Old Name',
                ],
                'new_values' => [
                    'name' => 'New Name',
                ],
            ],
        );

        $this->assertDatabaseHas('audit_logs', [
            'id' => $auditLog->id,
            'event' => AuditEvent::Updated->value,
        ]);
    }

    public function test_deleted_event_is_recorded(): void
    {
        $user = $this->createUserWithPermission();

        $auditLog = $this->createAuditLog(
            $user,
            ['event' => AuditEvent::Deleted],
        );

        $this->assertDatabaseHas('audit_logs', [
            'id' => $auditLog->id,
            'event' => AuditEvent::Deleted->value,
        ]);
    }

    public function test_restored_event_is_recorded(): void
    {
        $user = $this->createUserWithPermission();

        $auditLog = $this->createAuditLog(
            $user,
            ['event' => AuditEvent::Restored],
        );

        $this->assertDatabaseHas('audit_logs', [
            'id' => $auditLog->id,
            'event' => AuditEvent::Restored->value,
        ]);
    }

    public function test_force_deleted_event_is_recorded(): void
    {
        $user = $this->createUserWithPermission();

        $auditLog = $this->createAuditLog(
            $user,
            ['event' => AuditEvent::ForceDeleted],
        );

        $this->assertDatabaseHas('audit_logs', [
            'id' => $auditLog->id,
            'event' => AuditEvent::ForceDeleted->value,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Response Structure
    |--------------------------------------------------------------------------
    */

    public function test_audit_log_response_contains_expected_fields(): void
    {
        $user = $this->createUserWithPermission();

        $auditLog = $this->createAuditLog($user);

        Sanctum::actingAs($user);

        $response = $this->getJson(
            '/api/v1/audit-logs?sort=created_at&direction=desc',
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.id', $auditLog->id)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'event',
                        'auditable_type',
                        'auditable_id',
                        'old_values',
                        'new_values',
                        'url',
                        'ip_address',
                        'user_agent',
                        'created_at',
                    ],
                ],
            ]);
    }

    /*
|--------------------------------------------------------------------------
| Role / Permission Auditing
|--------------------------------------------------------------------------
*/

    public function test_role_created_event_is_recorded(): void
    {
        $user = $this->createUserWithPermission();

        Sanctum::actingAs($user);

        $role = Role::create([
            'name' => 'Audit Test Role',
            'guard_name' => 'sanctum',
        ]);

        $auditLog = AuditLog::query()
            ->where('auditable_type', Role::class)
            ->where('auditable_id', $role->id)
            ->where('event', AuditEvent::Created->value)
            ->latest()
            ->first();

        $this->assertNotNull($auditLog);

        $this->assertSame($user->id, $auditLog->user_id);
        $this->assertSame(Role::class, $auditLog->auditable_type);
        $this->assertSame($role->id, $auditLog->auditable_id);

        $this->assertSame([], $auditLog->old_values);

        $this->assertSame(
            'Audit Test Role',
            $auditLog->new_values['name'],
        );

        $this->assertSame(
            'sanctum',
            $auditLog->new_values['guard_name'],
        );
    }

    public function test_role_updated_event_is_recorded(): void
    {
        $user = $this->createUserWithPermission();

        Sanctum::actingAs($user);

        $role = Role::create([
            'name' => 'Original Role Name',
            'guard_name' => 'sanctum',
        ]);

        $role->update([
            'name' => 'Updated Role Name',
        ]);

        $auditLog = AuditLog::query()
            ->where('auditable_type', Role::class)
            ->where('auditable_id', $role->id)
            ->where('event', AuditEvent::Updated->value)
            ->latest()
            ->first();

        $this->assertNotNull($auditLog);

        $this->assertSame($user->id, $auditLog->user_id);
        $this->assertSame(
            'Updated Role Name',
            $auditLog->new_values['name'],
        );

        $this->assertSame(
            'Original Role Name',
            $auditLog->old_values['name'],
        );

        $this->assertArrayNotHasKey(
            'guard_name',
            $auditLog->new_values,
        );
    }

    public function test_role_deleted_event_is_recorded(): void
    {
        $user = $this->createUserWithPermission();

        Sanctum::actingAs($user);

        $role = Role::create([
            'name' => 'Role To Delete',
            'guard_name' => 'sanctum',
        ]);

        $roleId = $role->id;

        $role->delete();

        $auditLog = AuditLog::query()
            ->where('auditable_type', Role::class)
            ->where('auditable_id', $roleId)
            ->where('event', AuditEvent::Deleted->value)
            ->latest()
            ->first();

        $this->assertNotNull($auditLog);

        $this->assertSame($user->id, $auditLog->user_id);
        $this->assertSame([], $auditLog->new_values);

        $this->assertSame(
            'Role To Delete',
            $auditLog->old_values['name'],
        );

        $this->assertSame(
            'sanctum',
            $auditLog->old_values['guard_name'],
        );
    }

    public function test_permission_created_event_is_recorded(): void
    {
        $user = $this->createUserWithPermission();

        Sanctum::actingAs($user);

        $permission = Permission::create([
            'name' => 'audit.test.create',
            'guard_name' => 'sanctum',
        ]);

        $auditLog = AuditLog::query()
            ->where('auditable_type', Permission::class)
            ->where('auditable_id', $permission->id)
            ->where('event', AuditEvent::Created->value)
            ->latest()
            ->first();

        $this->assertNotNull($auditLog);

        $this->assertSame($user->id, $auditLog->user_id);
        $this->assertSame(
            Permission::class,
            $auditLog->auditable_type,
        );

        $this->assertSame(
            $permission->id,
            $auditLog->auditable_id,
        );

        $this->assertSame([], $auditLog->old_values);

        $this->assertSame(
            'audit.test.create',
            $auditLog->new_values['name'],
        );

        $this->assertSame(
            'sanctum',
            $auditLog->new_values['guard_name'],
        );
    }

    public function test_permission_updated_event_is_recorded(): void
    {
        $user = $this->createUserWithPermission();

        Sanctum::actingAs($user);

        $permission = Permission::create([
            'name' => 'audit.test.original',
            'guard_name' => 'sanctum',
        ]);

        $permission->update([
            'name' => 'audit.test.updated',
        ]);

        $auditLog = AuditLog::query()
            ->where('auditable_type', Permission::class)
            ->where('auditable_id', $permission->id)
            ->where('event', AuditEvent::Updated->value)
            ->latest()
            ->first();

        $this->assertNotNull($auditLog);

        $this->assertSame($user->id, $auditLog->user_id);

        $this->assertSame(
            'audit.test.updated',
            $auditLog->new_values['name'],
        );

        $this->assertSame(
            'audit.test.original',
            $auditLog->old_values['name'],
        );

        $this->assertArrayNotHasKey(
            'guard_name',
            $auditLog->new_values,
        );
    }

    public function test_permission_deleted_event_is_recorded(): void
    {
        $user = $this->createUserWithPermission();

        Sanctum::actingAs($user);

        $permission = Permission::create([
            'name' => 'audit.test.delete',
            'guard_name' => 'sanctum',
        ]);

        $permissionId = $permission->id;

        $permission->delete();

        $auditLog = AuditLog::query()
            ->where(
                'auditable_type',
                Permission::class,
            )
            ->where('auditable_id', $permissionId)
            ->where('event', AuditEvent::Deleted->value)
            ->latest()
            ->first();

        $this->assertNotNull($auditLog);

        $this->assertSame($user->id, $auditLog->user_id);
        $this->assertSame([], $auditLog->new_values);

        $this->assertSame(
            'audit.test.delete',
            $auditLog->old_values['name'],
        );

        $this->assertSame(
            'sanctum',
            $auditLog->old_values['guard_name'],
        );
    }
}
