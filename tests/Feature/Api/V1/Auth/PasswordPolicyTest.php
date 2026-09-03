<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Policies\PasswordPolicy;
use Tests\TestCase;

class PasswordPolicyTest extends TestCase
{
    public function test_password_policy_returns_minimum_length(): void
    {
        $response = $this->getJson('/api/v1/auth/password-policy');

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.min_length',
                app(PasswordPolicy::class)->minLength()
            );
    }

    public function test_password_policy_returns_configured_minimum_length(): void
    {
        config([
            'auth.password_rules.password_min_length' => 16,
        ]);

        $response = $this->getJson('/api/v1/auth/password-policy');

        $response
            ->assertOk()
            ->assertJsonPath('data.min_length', 16);
    }

    public function test_password_policy_returns_configured_required_mixed_case(): void
    {
        config([
            'auth.password_rules.require_mixed_case' => true,
        ]);

        $response = $this->getJson('/api/v1/auth/password-policy');

        $response
            ->assertOk()
            ->assertJsonPath('data.require_mixed_case', true);
    }

    public function test_password_policy_returns_configured_required_numbers(): void
    {
        config([
            'auth.password_rules.require_numbers' => true,
        ]);

        $response = $this->getJson('/api/v1/auth/password-policy');

        $response
            ->assertOk()
            ->assertJsonPath('data.require_numbers', true);
    }

    public function test_password_policy_returns_configured_required_symbols(): void
    {
        config([
            'auth.password_rules.require_symbols' => true,
        ]);

        $response = $this->getJson('/api/v1/auth/password-policy');

        $response
            ->assertOk()
            ->assertJsonPath('data.require_symbols', true);
    }
}
