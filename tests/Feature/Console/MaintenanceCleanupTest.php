<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Enums\AuditEvent;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class MaintenanceCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_password_reset_tokens_are_removed(): void
    {
        $now = now();

        DB::table('password_reset_tokens')->insert([
            ['email' => 'expired@example.test', 'token' => 'expired', 'created_at' => $now->copy()->subHours(2)],
            ['email' => 'valid@example.test', 'token' => 'valid', 'created_at' => $now],
        ]);

        Artisan::call('auth:clear-resets');

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'expired@example.test',
        ]);
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'valid@example.test',
        ]);
    }

    public function test_failed_jobs_older_than_three_days_are_removed(): void
    {
        DB::table('failed_jobs')->insert([
            [
                'uuid' => 'expired-failed-job',
                'connection' => 'database',
                'queue' => 'default',
                'payload' => '{}',
                'exception' => 'expired',
                'failed_at' => now()->subDays(4),
            ],
            [
                'uuid' => 'recent-failed-job',
                'connection' => 'database',
                'queue' => 'default',
                'payload' => '{}',
                'exception' => 'recent',
                'failed_at' => now()->subDay(),
            ],
        ]);

        $this->artisan('queue:prune-failed', ['--hours' => 72])
            ->assertSuccessful();

        $this->assertDatabaseMissing('failed_jobs', ['uuid' => 'expired-failed-job']);
        $this->assertDatabaseHas('failed_jobs', ['uuid' => 'recent-failed-job']);
    }

    public function test_audit_prune_keeps_logs_within_the_configured_retention(): void
    {
        $user = User::factory()->createQuietly();

        $expired = AuditLog::create([
            'user_id' => $user->id,
            'event' => AuditEvent::Created,
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
        ]);
        $expired->forceFill([
            'created_at' => now()->subDays(31),
            'updated_at' => now()->subDays(31),
        ])->saveQuietly();

        $recent = AuditLog::create([
            'user_id' => $user->id,
            'event' => AuditEvent::Updated,
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
        ]);
        $recent->forceFill([
            'created_at' => now()->subDays(29),
            'updated_at' => now()->subDays(29),
        ])->saveQuietly();

        $this->artisan('audit:prune')
            ->assertSuccessful();

        $this->assertDatabaseCount('audit_logs', 1);
        $this->assertDatabaseHas('audit_logs', ['id' => $recent->id]);
    }

    public function test_audit_prune_rejects_non_positive_retention(): void
    {
        $this->artisan('audit:prune', ['--days' => 0])
            ->assertFailed();
    }
}
