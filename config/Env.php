<?php

declare(strict_types=1);

namespace Config;

final class Env
{
    /**
     * Loads environment variables from a dotenv-style file.
     *
     * - Lines: KEY=VALUE
     * - Ignores blank lines and comments (#...)
     * - Supports quoted values: KEY="value"
     * - Does not override already-set env vars (non-empty)
     */
    public static function load(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $pos));
            if ($key === '') {
                continue;
            }

            $value = trim(substr($line, $pos + 1));

            if ($value !== '' && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
                $value = substr($value, 1, -1);
            }

            $existing = getenv($key);
            if (is_string($existing) && trim($existing) !== '') {
                continue;
            }

            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
