<?php

declare(strict_types=1);

namespace Config;

use Models\User;

final class Auth
{
    private const ADMIN_EMAIL = 'fourat.akrout@gmail.com';

    private static function adminEmail(): string
    {
        return self::ADMIN_EMAIL;
    }

    public static function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
    }

    public static function check(): bool
    {
        self::startSession();
        if (isset($_SESSION['user_id']) && is_int($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
            return true;
        }

        if (isset($_SESSION['user']['db_id']) && is_int($_SESSION['user']['db_id']) && $_SESSION['user']['db_id'] > 0) {
            return true;
        }

        return false;
    }

    public static function id(): int
    {
        self::startSession();

        if (isset($_SESSION['user_id']) && is_int($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }

        if (isset($_SESSION['user']['db_id']) && is_int($_SESSION['user']['db_id'])) {
            return $_SESSION['user']['db_id'];
        }

        return 0;
    }

    /** @return array<string, mixed> */
    public static function user(): array
    {
        self::startSession();
        $u = $_SESSION['user'] ?? [];
        return is_array($u) ? $u : [];
    }

    public static function requireLogin(string $redirectTo = 'index.php?route=login'): void
    {
        if (!self::check()) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }

    public static function isAdmin(): bool
    {
        self::startSession();
        $adminEmail = self::adminEmail();
        if ($adminEmail === '') {
            return false;
        }

        $u = self::user();
        $mail = strtolower(trim((string)($u['mail'] ?? '')));
        return $mail !== '' && $mail === strtolower($adminEmail);
    }

    public static function requireAdmin(string $redirectTo = 'index.php?route=profile'): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }

    public static function login(User $user): void
    {
        self::startSession();
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user'] = [
            'db_id' => $user->getId(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'mail' => $user->getMail(),
            'telephone' => $user->getTelephone(),
        ];

        if (!isset($_SESSION['settings']) || !is_array($_SESSION['settings'])) {
            $_SESSION['settings'] = [
                'notifications' => true,
                'dark_mode' => false,
            ];
        }
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }

        session_destroy();
    }
}
