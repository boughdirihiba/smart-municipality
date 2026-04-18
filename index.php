<?php

declare(strict_types=1);

// Front Controller (MVC)

// Autoload minimal (sans Composer)
spl_autoload_register(function (string $class): void {
    $map = [
        'Config\\' => __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR,
        'Controles\\' => __DIR__ . DIRECTORY_SEPARATOR . 'Controles' . DIRECTORY_SEPARATOR,
        'Models\\' => __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR,
    ];

    foreach ($map as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        $path = $baseDir . $relativePath;

        if (is_file($path)) {
            require $path;
        }
        return;
    }
});

use Controles\AuthController;
use Controles\AdminUsersController;
use Controles\DashboardController;
use Controles\ProfileController;
use Controles\PublicController;
use Config\Auth;
use Config\View;

$route = (string)($_GET['route'] ?? 'login');
$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

$auth = new AuthController();
$adminUsers = new AdminUsersController();
$dashboard = new DashboardController();
$profile = new ProfileController();
$public = new PublicController();

// If already authenticated, prevent showing login/signup again.
if (($route === 'login' || $route === 'signup') && Auth::check()) {
    header('Location: index.php?route=' . (Auth::isAdmin() ? 'dashboard' : 'profile'));
    exit;
}

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

    case 'profile':
        if ($method === 'POST') {
            $profile->update();
        }
        $profile->show();
        break;

    case 'dashboard':
        $dashboard->dashboard();
        break;

    case 'admin-blog':
        $dashboard->section('blog');
        break;

    case 'admin-signalement':
        $dashboard->section('signalement');
        break;

    case 'admin-events':
        $dashboard->section('events');
        break;

    case 'admin-map':
        $dashboard->section('map');
        break;

    case 'admin-services':
        $dashboard->section('services');
        break;

    case 'admin-rdv':
        $dashboard->section('rdv');
        break;

    case 'admin-users':
        $adminUsers->index();
        break;

    case 'admin-users-create':
        $adminUsers->create();
        break;

    case 'admin-users-update':
        $adminUsers->update();
        break;

    case 'admin-users-delete':
        $adminUsers->delete();
        break;

    case 'events':
        $public->events();
        break;

    case 'map':
        $public->map();
        break;

    case 'blog':
        $public->blog();
        break;

    case 'services':
        $public->services();
        break;

    case 'rdv':
        $public->rdv();
        break;

    case 'page':
        $page = (string)($_GET['page'] ?? 'login');

        $flash = null;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        if (isset($_SESSION['_flash']) && is_array($_SESSION['_flash'])) {
            $flash = $_SESSION['_flash'];
            unset($_SESSION['_flash']);
        }

        $forgotSent = false;
        if ($page === 'forgot' && $method === 'POST') {
            $mail = trim((string)($_POST['mail'] ?? ''));
            $errors = [];

            if ($mail === '') {
                $errors['mail'] = "L'email est obligatoire.";
            } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $errors['mail'] = "Format d'email invalide.";
            }

            if ($errors !== []) {
                if (session_status() !== PHP_SESSION_ACTIVE) {
                    @session_start();
                }

                $_SESSION['_flash'] = [
                    'errors' => $errors,
                    'old' => ['mail' => $mail],
                ];

                header('Location: index.php?route=page&page=forgot');
                exit;
            }

            $forgotSent = true;
        }

        View::render('pages.php', ['page' => $page, 'flash' => $flash, 'forgotSent' => $forgotSent]);
        break;

    default:
        http_response_code(404);
        echo 'Not Found';
}
