<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            [
                'email' => 'peeyush@example.com',
            ],
            [
                'first_name' => 'Peeyush',
                'last_name' => 'Budhia',

                'avatar' => null,

                'email_verified_at' => now(),

                'password' => 'password',
            ]
        );

        // User::factory()->count(10)->create();
    }
}
