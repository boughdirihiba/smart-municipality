<?php

declare(strict_types=1);

require __DIR__ . '/config/config.php';
require __DIR__ . '/app/Core/Autoloader.php';

$requestedRoute = trim((string)($_GET['route'] ?? 'login'));
$requestedRoute = trim($requestedRoute, '/');
$requestedController = strtolower(strtok($requestedRoute, '/') ?: 'login');
$actionParam = $_POST['action'] ?? $_GET['action'] ?? '';

$publicRoutes = ['login', 'signup', 'page', 'faceid-login', 'faceid-enroll'];
if (!\Config\Auth::check()) {
    if ($actionParam !== '') {
        header('Location: index.php?route=login');
        exit;
    }

    if (!in_array($requestedController, $publicRoutes, true)) {
        header('Location: index.php?route=login');
        exit;
    }
}

// ─── LEGACY ACTION ROUTER ─────────────────────────────────────────────────────
// If ?action= is present, delegate to the legacy controller system.
if (!empty($_GET['action']) || !empty($_POST['action'])) {
    $handled = require __DIR__ . '/legacy_router.php';
    if ($handled) {
        exit;
    }
    // If action not recognized, fall through to MVC or 404 below.
}

// ─── ROUTE PARSING ───────────────────────────────────────────────────────────
$defaultRoute = \Config\Auth::check()
    ? (\Config\Auth::isAdmin() ? 'dashboard' : 'home/index')
    : 'login';
$route = trim((string)($_GET['route'] ?? $defaultRoute));
$route = trim($route, '/');

[$controllerPart, $actionPart] = array_pad(explode('/', $route, 2), 2, 'index');

$controllerKey  = strtolower(trim($controllerPart));
$controllerStem = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $controllerKey)));
$controllerName = $controllerStem . 'Controller';
$actionName     = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', strtolower($actionPart))));
$actionName     = lcfirst($actionName);

// ─── LEGACY ROUTE ALIASES ───────────────────────────────────────────────────
$legacyRouteMap = [
    'events' => 'evenements',
    'blog' => 'blog',
    'services' => 'services_list',
    'rdv' => 'rendez_vous',
    'demandes' => 'manage',
];
if (isset($legacyRouteMap[$controllerKey])) {
    $_GET['action'] = $legacyRouteMap[$controllerKey];
    require __DIR__ . '/legacy_router.php';
    exit;
}

// Map alias for the main map screen.
if ($controllerKey === 'map') {
    $controllerKey = 'home';
    $controllerStem = 'Home';
    $controllerName = 'HomeController';
    $actionName = 'index';
}

// ─── CONTROLES (login branch) ROUTES ─────────────────────────────────────────
$controlesTarget = null;
switch ($controllerKey) {
    case 'login':
        $controlesTarget = ['Controles\\AuthController', $_SERVER['REQUEST_METHOD'] === 'POST' ? 'login' : 'showLogin'];
        break;
    case 'signup':
        $controlesTarget = ['Controles\\AuthController', $_SERVER['REQUEST_METHOD'] === 'POST' ? 'signup' : 'showSignup'];
        break;
    case 'logout':
        $controlesTarget = ['Controles\\AuthController', 'logout'];
        break;
    case 'profile':
        $controlesTarget = ['Controles\\ProfileController', $_SERVER['REQUEST_METHOD'] === 'POST' ? 'update' : 'show'];
        break;
    case 'faceid-enroll':
        $controlesTarget = ['Controles\\FaceIdController', 'enroll'];
        break;
    case 'faceid-login':
        $controlesTarget = ['Controles\\FaceIdController', 'login'];
        break;
    case 'dashboard':
        $controlesTarget = ['Controles\\DashboardController', 'dashboard'];
        break;
    case 'admin-users':
        $controlesTarget = ['Controles\\AdminUsersController', 'index'];
        break;
    case 'admin-users-create':
        $controlesTarget = ['Controles\\AdminUsersController', 'create'];
        break;
    case 'admin-users-update':
        $controlesTarget = ['Controles\\AdminUsersController', 'update'];
        break;
    case 'admin-users-delete':
        $controlesTarget = ['Controles\\AdminUsersController', 'delete'];
        break;
}

if (is_array($controlesTarget)) {
    require_once __DIR__ . '/controllers/Controles/Controllers.php';
    [$class, $method] = $controlesTarget;
    if (!class_exists($class)) {
        http_response_code(404);
        echo 'Controller not found.';
        exit;
    }
    $controller = new $class();
    if (!method_exists($controller, $method)) {
        http_response_code(404);
        echo 'Action not found.';
        exit;
    }
    $controller->{$method}();
    exit;
}

// ─── NEW MVC ROUTER ──────────────────────────────────────────────────────────

$controllerClass = 'App\\Controllers\\' . $controllerName;

if (!class_exists($controllerClass)) {
    // Support legacy/pluralized route names (e.g. "signalements" -> "SignalementController").
    if (str_ends_with($controllerStem, 's')) {
        $singularControllerClass = 'App\\Controllers\\' . substr($controllerStem, 0, -1) . 'Controller';
        if (class_exists($singularControllerClass)) {
            $controllerClass = $singularControllerClass;
        } else {
            http_response_code(404);
            echo 'Controller not found.';
            exit;
        }
    } else {
        http_response_code(404);
        echo 'Controller not found.';
        exit;
    }
}

$controller = new $controllerClass();

if (!method_exists($controller, $actionName)) {
    // Backward compatibility for routes that use "/list" while controller exposes index().
    if ($actionName === 'list' && method_exists($controller, 'index')) {
        $actionName = 'index';
    } else {
        http_response_code(404);
        echo 'Action not found.';
        exit;
    }
}

$controller->{$actionName}();
