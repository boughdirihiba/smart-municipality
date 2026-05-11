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
        if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0) {
            return true;
        }

        if (isset($_SESSION['user']['id']) && (int)$_SESSION['user']['id'] > 0) {
            return true;
        }

        if (isset($_SESSION['user']['db_id']) && (int)$_SESSION['user']['db_id'] > 0) {
            return true;
        }

        return false;
    }

    public static function id(): int
    {
        self::startSession();

        if (isset($_SESSION['user_id'])) {
            return (int)$_SESSION['user_id'];
        }

        if (isset($_SESSION['user']['id'])) {
            return (int)$_SESSION['user']['id'];
        }

        if (isset($_SESSION['user']['db_id'])) {
            return (int)$_SESSION['user']['db_id'];
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

    public static function requireLogin(string $redirectTo = 'index.php?route=auth/login'): void
    {
        if (!self::check()) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }

    public static function isAdmin(): bool
    {
        self::startSession();

        $u = self::user();
        $role = strtolower(trim((string)($u['role'] ?? $_SESSION['user_role'] ?? '')));
        if ($role !== '') {
            return $role === 'admin';
        }

        $mail = strtolower(trim((string)($u['mail'] ?? $u['email'] ?? '')));
        return $mail !== '' && $mail === strtolower(self::adminEmail());
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

        $id = (int)$user->getId();
        $nom = method_exists($user, 'getNom') ? trim((string)$user->getNom()) : '';
        $prenom = method_exists($user, 'getPrenom') ? trim((string)$user->getPrenom()) : '';
        $email = method_exists($user, 'getMail')
            ? trim((string)$user->getMail())
            : (method_exists($user, 'getEmail') ? trim((string)$user->getEmail()) : '');
        $telephone = method_exists($user, 'getTelephone') ? trim((string)$user->getTelephone()) : '';
        $avatar = method_exists($user, 'getAvatar') ? trim((string)$user->getAvatar()) : '';
        $role = method_exists($user, 'getRole') ? trim((string)$user->getRole()) : '';

        if ($prenom === '' && $nom === '' && method_exists($user, 'getName')) {
            $fullName = trim((string)$user->getName());
            if ($fullName !== '') {
                $parts = preg_split('/\s+/', $fullName, 2);
                $prenom = (string)($parts[0] ?? '');
                $nom = (string)($parts[1] ?? '');
            }
        }

        if ($role === '') {
            $role = 'citoyen';
        }

        $displayName = trim($prenom . ' ' . $nom);
        if ($displayName === '') {
            $displayName = $email !== '' ? $email : 'Utilisateur';
        }

        $_SESSION['user_id'] = $id;

        $_SESSION['user'] = [
            'id' => $id,
            'db_id' => $id,
            'nom' => $nom,
            'prenom' => $prenom,
            'name' => $displayName,
            'email' => $email,
            'mail' => $email,
            'telephone' => $telephone,
            'avatar' => $avatar !== '' ? $avatar : 'sidebar-photo.svg',
            'role' => $role,
        ];

        $_SESSION['user_name'] = $displayName;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_avatar'] = $avatar !== '' ? $avatar : 'sidebar-photo.svg';

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
