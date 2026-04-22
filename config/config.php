<?php

declare(strict_types=1);

session_start();

define('APP_NAME', 'Smart Municipality');
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '/smart-municipality');
define('UPLOAD_PATH', BASE_PATH . '/public/uploads/');
define('UPLOAD_URL', BASE_URL . '/public/uploads/');

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'smart_municipality');
define('DB_USER', 'root');
define('DB_PASS', '');

function bootstrap_user_session_from_database(): void
{
    if (isset($_SESSION['user'])) {
        return;
    }

    try {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new \PDO($dsn, DB_USER, DB_PASS, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);

        $stmt = $pdo->query('SELECT id, nom, prenom, email, avatar, telephone, adresse, role FROM utilisateurs ORDER BY id ASC LIMIT 1');
        $row = $stmt->fetch();

        if (is_array($row)) {
            $_SESSION['user'] = [
                'id' => (int)$row['id'],
                'nom' => (string)($row['nom'] ?? ''),
                'prenom' => (string)($row['prenom'] ?? ''),
                'email' => (string)($row['email'] ?? ''),
                'avatar' => (string)($row['avatar'] ?? 'sidebar-photo.svg'),
                'telephone' => (string)($row['telephone'] ?? ''),
                'adresse' => (string)($row['adresse'] ?? ''),
                'role' => (string)($row['role'] ?? 'citoyen'),
            ];
            return;
        }
    } catch (\Throwable $e) {
        // Fallback below when DB is unavailable or table is missing.
    }

    $_SESSION['user'] = [
        'id' => 1,
        'nom' => 'Demo',
        'prenom' => 'Citoyen',
        'email' => 'citoyen@demo.tn',
        'avatar' => 'sidebar-photo.svg',
        'telephone' => '',
        'adresse' => '',
        'role' => 'citoyen',
    ];
}

bootstrap_user_session_from_database();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $route): void
{
    header('Location: ' . BASE_URL . '/index.php?route=' . $route);
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function add_notification(string $message, string $type = 'info'): void
{
    if (!isset($_SESSION['notifications']) || !is_array($_SESSION['notifications'])) {
        $_SESSION['notifications'] = [];
    }

    array_unshift($_SESSION['notifications'], [
        'message' => $message,
        'type' => $type,
        'created_at' => date('Y-m-d H:i:s'),
        'seen' => false,
    ]);

    if (count($_SESSION['notifications']) > 20) {
        $_SESSION['notifications'] = array_slice($_SESSION['notifications'], 0, 20);
    }
}

function get_notifications(int $limit = 8): array
{
    if (!isset($_SESSION['notifications']) || !is_array($_SESSION['notifications'])) {
        return [];
    }

    return array_slice($_SESSION['notifications'], 0, max(1, $limit));
}

function get_unread_notifications_count(): int
{
    if (!isset($_SESSION['notifications']) || !is_array($_SESSION['notifications'])) {
        return 0;
    }

    $count = 0;
    foreach ($_SESSION['notifications'] as $notification) {
        if (empty($notification['seen'])) {
            $count += 1;
        }
    }

    return $count;
}

function mark_notifications_seen(): void
{
    if (!isset($_SESSION['notifications']) || !is_array($_SESSION['notifications'])) {
        return;
    }

    foreach ($_SESSION['notifications'] as $index => $notification) {
        $_SESSION['notifications'][$index]['seen'] = true;
    }
}
