<?php

declare(strict_types=1);

namespace Tests\Unit\Resources;

use App\Enums\AuditEvent;
use App\Http\Resources\Api\V1\AuditLogResource;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuditLogResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resolves_the_nested_user_resource_to_an_array(): void
    {
        $user = User::factory()->create();

        $auditLog = AuditLog::create([
            'user_id' => $user->id,
            'event' => AuditEvent::Updated,
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => [
                'status' => 'inactive',
            ],
            'new_values' => [
                'status' => 'active',
            ],
            'url' => 'http://example.test/users/'.$user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $auditLog->load('user');

        $data = (new AuditLogResource($auditLog))->resolve();

        $this->assertIsArray($data['user']);
        $this->assertSame($user->first_name, $data['user']['first_name']);
        $this->assertSame($user->last_name, $data['user']['last_name']);
        $this->assertSame(
            $auditLog->created_at?->toIso8601String(),
            $data['created_at'],
        );
        $this->assertSame(
            $auditLog->updated_at?->toIso8601String(),
            $data['updated_at'],
        );
    }

    public function test_it_normalizes_historical_datetime_values_in_snapshots(): void
    {
        $user = User::factory()->createQuietly();

        $auditLog = AuditLog::create([
            'event' => AuditEvent::Updated,
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => [
                'created_at' => '2026-09-05 10:20:30',
                'deleted_at' => '2026-09-05 10:25:30',
                'last_login_at' => null,
                'nested' => [
                    'email_verified_at' => '2026-09-05 11:30:00',
                ],
            ],
            'new_values' => [
                'created_at' => '2026-09-06T12:45:00+02:00',
                'invalid_at' => 'not-a-date',
            ],
        ]);

        $data = (new AuditLogResource($auditLog))->resolve();

        $this->assertSame(
            '2026-09-05T10:20:30+00:00',
            $data['old_values']['created_at'],
        );
        $this->assertSame(
            '2026-09-05T10:25:30+00:00',
            $data['old_values']['deleted_at'],
        );
        $this->assertNull($data['old_values']['last_login_at']);
        $this->assertSame(
            '2026-09-05T11:30:00+00:00',
            $data['old_values']['nested']['email_verified_at'],
        );
        $this->assertSame(
            '2026-09-06T12:45:00+02:00',
            $data['new_values']['created_at'],
        );
        $this->assertSame('not-a-date', $data['new_values']['invalid_at']);
    }

    public function test_new_audit_snapshots_store_datetime_values_as_iso_8601(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => '2026-09-05 10:20:30',
        ]);

        $createdAudit = AuditLog::query()
            ->where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->where('event', AuditEvent::Created->value)
            ->latest('created_at')
            ->firstOrFail();

        $this->assertSame(
            '2026-09-05T10:20:30+00:00',
            $createdAudit->new_values['email_verified_at'],
        );
        $this->assertSame(
            $user->created_at?->toIso8601String(),
            $createdAudit->new_values['created_at'],
        );

        $user->forceFill([
            'email_verified_at' => '2026-09-06 14:30:00',
        ])->save();

        $updatedAudit = AuditLog::query()
            ->where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->where('event', AuditEvent::Updated->value)
            ->latest('created_at')
            ->firstOrFail();

        $this->assertSame(
            '2026-09-05T10:20:30+00:00',
            $updatedAudit->old_values['email_verified_at'],
        );
        $this->assertSame(
            '2026-09-06T14:30:00+00:00',
            $updatedAudit->new_values['email_verified_at'],
        );
    }
}
