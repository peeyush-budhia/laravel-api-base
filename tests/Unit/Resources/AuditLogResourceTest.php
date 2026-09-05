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
}
