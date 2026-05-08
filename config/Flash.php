<?php

declare(strict_types=1);

namespace Config;

final class Flash
{
    /** @param array<string, mixed> $data */
    public static function set(array $data): void
    {
        Auth::startSession();
        $_SESSION['_flash'] = $data;
    }

    /** @return array<string, mixed>|null */
    public static function consume(): ?array
    {
        Auth::startSession();
        $flash = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);
        return is_array($flash) ? $flash : null;
    }

    /** @param array<string,string> $errors @param array<string,mixed> $old */
    public static function setErrors(array $errors, array $old = []): void
    {
        self::set([
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    /** @param string $message */
    public static function success(string $message): void
    {
        self::set(['success' => $message]);
    }
}
