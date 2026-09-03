<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->index('deleted_at', 'users_deleted_at_index');
            $table->index('status', 'users_status_index');
            $table->index('last_login_at', 'users_last_login_at_index');
            $table->index('created_at', 'users_created_at_index');
            $table->index('updated_at', 'users_updated_at_index');
        });

        Schema::table('roles', function (Blueprint $table): void {
            $table->index(
                ['guard_name', 'name'],
                'roles_guard_name_name_index',
            );
        });

        Schema::table('permissions', function (Blueprint $table): void {
            $table->index(
                ['guard_name', 'name'],
                'permissions_guard_name_name_index',
            );
        });

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->index(
                ['user_id', 'created_at'],
                'audit_logs_user_id_created_at_index',
            );
            $table->index(
                ['event', 'created_at'],
                'audit_logs_event_created_at_index',
            );
            $table->index(
                ['auditable_type', 'auditable_id', 'created_at'],
                'audit_logs_auditable_created_at_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex('audit_logs_auditable_created_at_index');
            $table->dropIndex('audit_logs_event_created_at_index');
            $table->dropIndex('audit_logs_user_id_created_at_index');
        });

        Schema::table('permissions', function (Blueprint $table): void {
            $table->dropIndex('permissions_guard_name_name_index');
        });

        Schema::table('roles', function (Blueprint $table): void {
            $table->dropIndex('roles_guard_name_name_index');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_updated_at_index');
            $table->dropIndex('users_created_at_index');
            $table->dropIndex('users_last_login_at_index');
            $table->dropIndex('users_status_index');
            $table->dropIndex('users_deleted_at_index');
        });
    }
};
