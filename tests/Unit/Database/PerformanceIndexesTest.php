<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class PerformanceIndexesTest extends TestCase
{
    use RefreshDatabase;

    public function test_performance_indexes_are_present_on_core_tables(): void
    {
        $this->assertIndexes('users', [
            'users_deleted_at_index',
            'users_status_index',
            'users_last_login_at_index',
            'users_created_at_index',
            'users_updated_at_index',
        ]);

        $this->assertIndexes('roles', [
            'roles_guard_name_name_index',
        ]);

        $this->assertIndexes('permissions', [
            'permissions_guard_name_name_index',
        ]);

        $this->assertIndexes('audit_logs', [
            'audit_logs_user_id_created_at_index',
            'audit_logs_event_created_at_index',
            'audit_logs_auditable_created_at_index',
        ]);
    }

    /**
     * @param  array<int, string>  $expectedIndexes
     */
    private function assertIndexes(
        string $table,
        array $expectedIndexes,
    ): void {
        $indexes = Schema::getIndexListing($table);

        foreach ($expectedIndexes as $index) {
            $this->assertContains($index, $indexes);
        }
    }
}
