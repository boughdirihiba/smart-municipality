<?php

declare(strict_types=1);

session_start();

define('APP_NAME', 'Smart Municipality');
define('BASE_PATH', dirname(__DIR__));

require_once __DIR__ . '/Env.php';
\Config\Env::load(BASE_PATH . '/.env');

$baseUrl = '/smart-municipality';
if (isset($_SERVER['SCRIPT_NAME'])) {
    $baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    if ($baseUrl === '/') {
        $baseUrl = '';
    }
}
define('BASE_URL', $baseUrl);
define('UPLOAD_PATH', BASE_PATH . '/public/uploads/');
define('UPLOAD_URL', BASE_URL . '/public/uploads/');

define('DB_HOST', '127.0.0.1');

$dbPort = getenv('DB_PORT');
$sessionPort = isset($_SESSION['db_port']) ? (string)$_SESSION['db_port'] : '';
if ($sessionPort !== '' && ctype_digit($sessionPort)) {
    $dbPort = $sessionPort;
}

if (!is_string($dbPort) || $dbPort === '') {
    $dbPort = '3306';
    $ports = ['3306', '3305'];
    foreach ($ports as $port) {
        $conn = @fsockopen(DB_HOST, (int)$port, $errno, $errstr, 0.2);
        if (is_resource($conn)) {
            fclose($conn);
            $dbPort = $port;
            $_SESSION['db_port'] = $dbPort;
            break;
        }
    }
}
define('DB_PORT', $dbPort);
define('DB_NAME', 'smart_municipality');
define('DB_USER', 'root');
define('DB_PASS', '');

define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'fourat.akrout@gmail.com');
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: '05032005ff');

putenv('DB_HOST=' . DB_HOST);
putenv('DB_PORT=' . DB_PORT);
putenv('DB_NAME=' . DB_NAME);
putenv('DB_USER=' . DB_USER);
putenv('DB_PASS=' . DB_PASS);
putenv('ADMIN_EMAIL=' . ADMIN_EMAIL);
putenv('ADMIN_PASSWORD=' . ADMIN_PASSWORD);

spl_autoload_register(function (string $class): void {
    $prefixes = [
        'Config\\' => BASE_PATH . '/config/',
        'Models\\' => BASE_PATH . '/Models/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (strpos($class, $prefix) !== 0) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
        if (is_file($file)) {
            require $file;
        }
        return;
    }
});

// ─── Chatbot / AI API ────────────────────────────────────────────────────────
// Set your Groq API key here (https://console.groq.com) or leave empty to
// disable the AI chatbot feature.
define('GROK_API_KEY', getenv('GROK_API_KEY') ?: '');
define('GROQ_MODEL',   getenv('GROQ_MODEL')   ?: 'llama-3.1-8b-instant');

function pdo_connection(): \PDO
{
    static $pdo = null;

    if ($pdo instanceof \PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new \PDO($dsn, DB_USER, DB_PASS, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function bootstrap_user_session_from_database(): void
{
    if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
        if (!isset($_SESSION['user']['db_id']) && isset($_SESSION['user']['id'])) {
            $_SESSION['user']['db_id'] = (int)$_SESSION['user']['id'];
        }
        if (!isset($_SESSION['user']['user_id']) && isset($_SESSION['user']['id'])) {
            $_SESSION['user']['user_id'] = (int)$_SESSION['user']['id'];
        }
        if (!isset($_SESSION['user_id']) && isset($_SESSION['user']['id'])) {
            $_SESSION['user_id'] = (int)$_SESSION['user']['id'];
        }
        return;
    }

    // Bridge old login format ($_SESSION['user_id']) → new format ($_SESSION['user'])
    if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0) {
        try {
            $pdo = pdo_connection();
            $stmt = $pdo->prepare('SELECT id, nom, prenom, email, avatar, telephone, adresse, role FROM utilisateurs WHERE id = :id');
            $stmt->execute(['id' => (int)$_SESSION['user_id']]);
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
        } catch (\Throwable $e) {}
    }

    try {
        $pdo = pdo_connection();
        $stmt = $pdo->prepare('SELECT id, nom, prenom, email, avatar, telephone, adresse, role FROM utilisateurs ORDER BY id ASC LIMIT 1');
        $stmt->execute();
        $row = $stmt->fetch();

        if (is_array($row)) {
            $_SESSION['user'] = [
                'id' => (int)$row['id'],
                'db_id' => (int)$row['id'],
                'user_id' => (int)$row['id'],
                'nom' => (string)($row['nom'] ?? ''),
                'prenom' => (string)($row['prenom'] ?? ''),
                'mail' => (string)($row['email'] ?? ''),
                'email' => (string)($row['email'] ?? ''),
                'avatar' => (string)($row['avatar'] ?? 'sidebar-photo.svg'),
                'telephone' => (string)($row['telephone'] ?? ''),
                'adresse' => (string)($row['adresse'] ?? ''),
                'role' => (string)($row['role'] ?? 'citoyen'),
            ];
            $_SESSION['user_id'] = (int)$row['id'];
            return;
        }
    } catch (\Throwable $e) {
        // Fallback below when DB is unavailable or table is missing.
    }

    $_SESSION['user'] = [
        'id' => 1,
        'db_id' => 1,
        'user_id' => 1,
        'nom' => 'Demo',
        'prenom' => 'Citoyen',
        'mail' => 'citoyen@demo.tn',
        'email' => 'citoyen@demo.tn',
        'avatar' => 'sidebar-photo.svg',
        'telephone' => '',
        'adresse' => '',
        'role' => 'citoyen',
    ];
    $_SESSION['user_id'] = 1;
}

// Disable automatic session bootstrap - use proper login instead
$autoLogin = getenv('DEMO_AUTO_LOGIN');
if (is_string($autoLogin) && $autoLogin === '1') {
    bootstrap_user_session_from_database();
}

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
