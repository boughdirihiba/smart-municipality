<?php

declare(strict_types=1);

// Front Controller (MVC)

// Optional dotenv loader (keeps config consistent across terminals/Apache)
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Env.php';
\Config\Env::load(__DIR__ . DIRECTORY_SEPARATOR . '.env');

// Autoload minimal (sans Composer)
spl_autoload_register(function (string $class): void {
    // If controllers are bundled in a single file, load it once.
    if (str_starts_with($class, 'Controles\\')) {
        $bundle = __DIR__ . DIRECTORY_SEPARATOR . 'Controles' . DIRECTORY_SEPARATOR . 'Controllers.php';
        if (is_file($bundle)) {
            require_once $bundle;
            return;
        }
    }

    $map = [
        'Config\\' => __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR,
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
use Controles\FaceIdController;
use Controles\AdminUsersController;
use Controles\DashboardController;
use Controles\ProfileController;
use Controles\PublicController;
use Config\Auth;
use Config\Flash;
use Config\View;

$route = (string)($_GET['route'] ?? 'page');
$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

// If already authenticated, prevent showing login/signup again.
if (($route === 'login' || $route === 'signup') && Auth::check()) {
    header('Location: index.php?route=' . (Auth::isAdmin() ? 'dashboard' : 'profile'));
    exit;
}

switch ($route) {
    case 'faceid-enroll':
        $face = new FaceIdController();
        $face->enroll();
        break;

    case 'faceid-login':
        $face = new FaceIdController();
        $face->login();
        break;

    case 'login':
        $auth = new AuthController();
        if ($method === 'POST') {
            $auth->login();
        }
        $auth->showLogin();
        break;

    case 'signup':
        $auth = new AuthController();
        if ($method === 'POST') {
            $auth->signup();
        }
        $auth->showSignup();
        break;

    case 'logout':
        $auth = new AuthController();
        $auth->logout();
        break;

    case 'profile':
        $profile = new ProfileController();
        if ($method === 'POST') {
            $profile->update();
        }
        $profile->show();
        break;

    case 'dashboard':
        $dashboard = new DashboardController();
        $dashboard->dashboard();
        break;

    case 'admin-blog':
        $dashboard = new DashboardController();
        $dashboard->section('blog');
        break;

    case 'admin-signalement':
        $dashboard = new DashboardController();
        $dashboard->section('signalement');
        break;

    case 'admin-events':
        $dashboard = new DashboardController();
        $dashboard->section('events');
        break;

    case 'admin-map':
        $dashboard = new DashboardController();
        $dashboard->section('map');
        break;

    case 'admin-services':
        $dashboard = new DashboardController();
        $dashboard->section('services');
        break;

    case 'admin-rdv':
        $dashboard = new DashboardController();
        $dashboard->section('rdv');
        break;

    case 'admin-users':
        $adminUsers = new AdminUsersController();
        $adminUsers->index();
        break;

    case 'admin-users-create':
        $adminUsers = new AdminUsersController();
        $adminUsers->create();
        break;

    case 'admin-users-update':
        $adminUsers = new AdminUsersController();
        $adminUsers->update();
        break;

    case 'admin-users-delete':
        $adminUsers = new AdminUsersController();
        $adminUsers->delete();
        break;

    case 'events':
        $public = new PublicController();
        $public->events();
        break;

    case 'map':
        $public = new PublicController();
        $public->map();
        break;

    case 'blog':
        $public = new PublicController();
        $public->blog();
        break;

    case 'services':
        $public = new PublicController();
        $public->services();
        break;

    case 'rdv':
        $public = new PublicController();
        $public->rdv();
        break;

    case 'page':
        $page = (string)($_GET['page'] ?? 'home');
        if ($page === 'forgot' || $page === 'reset') {
            header('Location: index.php?route=login');
            exit;
        }

        $flash = Flash::consume();

        View::render('pages.php', [
            'page' => $page,
            'flash' => $flash,
        ]);
        break;

    default:
        http_response_code(404);
        echo 'Not Found';
}
