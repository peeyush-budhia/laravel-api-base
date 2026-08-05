<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The password used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),

            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->numerify('9#########'),

            'address' => fake()->address(),
            'job_title' => fake()->jobTitle(),
            'avatar' => null,

            'email_verified_at' => now(),

            'password' => static::$password ??= Hash::make('password'),

            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }
}
