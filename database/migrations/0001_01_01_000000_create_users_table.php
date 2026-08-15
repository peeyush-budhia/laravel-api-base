<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('first_name', 100);
            $table->string('last_name', 100);

            $table->string('email', 255)->unique();

            $table->string('avatar')->nullable();

            $table->string('status', 20)->default('active');

            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();

            $table->string('password');

            $table->boolean('must_change_password')->default(false);

            $table->rememberToken();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
