<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Environment configuration loader
 * Loads variables from .env file
 */
final class Environment
{
    private static array $vars = [];
    private static bool $loaded = false;

    /**
     * Load environment variables from .env file
     */
    public static function load(?string $path = null): void
    {
        if (self::$loaded) {
            return;
        }

        $envPath = $path ?? dirname(__DIR__, 2) . '/.env';

        if (!file_exists($envPath)) {
            throw new \RuntimeException("Environment file not found: {$envPath}");
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if (!empty($key)) {
                self::$vars[$key] = $value;
            }
        }

        self::$loaded = true;
    }

    /**
     * Get environment variable
     */
    public static function get(string $key, string $default = ''): string
    {
        return self::$vars[$key] ?? $default;
    }

    /**
     * Get all loaded variables
     */
    public static function all(): array
    {
        return self::$vars;
    }
}
