<?php

declare(strict_types=1);

// Front Controller (MVC)

// Autoload minimal (sans Composer)
spl_autoload_register(function (string $class): void {
    $prefix = 'Modules\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    $path = __DIR__ . DIRECTORY_SEPARATOR . $relative;
    if (is_file($path)) {
        require $path;
    }
});

use Modules\Controllers\AuthController;

$route = (string)($_GET['route'] ?? 'login');
$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

$auth = new AuthController();

switch ($route) {
    case 'login':
        if ($method === 'POST') {
            $auth->login();
        }
        $auth->showLogin();
        break;

    case 'signup':
        if ($method === 'POST') {
            $auth->signup();
        }
        $auth->showSignup();
        break;

    case 'logout':
        $auth->logout();
        break;

    default:
        http_response_code(404);
        echo 'Not Found';
}
