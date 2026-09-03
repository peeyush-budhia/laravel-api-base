<?php

namespace App\Policies;

final class PasswordPolicy
{
    private const DEFAULT_MIN_LENGTH = 12;

    private const DEFAULT_REQUIRE_MIXED_CASE = true;

    private const DEFAULT_REQUIRE_NUMBERS = true;

    private const DEFAULT_REQUIRE_SYMBOLS = true;

    public function minLength(): int
    {
        return (int) config(
            'auth.password_rules.password_min_length',
            self::DEFAULT_MIN_LENGTH,
        );
    }

    public function requiresMixedCase(): bool
    {
        return (bool) config(
            'auth.password_rules.require_mixed_case',
            self::DEFAULT_REQUIRE_MIXED_CASE,
        );
    }

    public function requiresNumbers(): bool
    {
        return (bool) config(
            'auth.password_rules.require_numbers',
            self::DEFAULT_REQUIRE_NUMBERS,
        );
    }

    public function requiresSymbols(): bool
    {
        return (bool) config(
            'auth.password_rules.require_symbols',
            self::DEFAULT_REQUIRE_SYMBOLS,
        );
    }

    public function rules(): array
    {
        return [
            'min_length' => $this->minLength(),
            'require_mixed_case' => $this->requiresMixedCase(),
            'require_numbers' => $this->requiresNumbers(),
            'require_symbols' => $this->requiresSymbols(),
        ];
    }
}
