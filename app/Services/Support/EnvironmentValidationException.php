<?php

namespace App\Services\Support;

use RuntimeException;

class EnvironmentValidationException extends RuntimeException
{
    /**
     * @param  array<int, string>  $names env variable names only — never values
     */
    public static function missingVariables(array $names): self
    {
        return new self(
            'Missing required environment variables: ' .
                implode(', ', $names) .
                '. Set them via 1Password (.env.1password) or platform secrets.',
        );
    }

    /**
     * @param  array<int, string>  $available
     */
    public static function unknownContext(string $context, array $available): self
    {
        return new self("Unknown environment validation context [{$context}]. Available: " . implode(', ', $available));
    }
}
