<?php

declare(strict_types=1);

require __DIR__ . '/config/config.php';
require __DIR__ . '/config/Auth.php';
require __DIR__ . '/app/Core/Autoloader.php';

\Config\Auth::startSession();

$route = trim((string)($_GET['route'] ?? ''));
$isAuthRoute = $route !== '' && str_starts_with($route, 'auth/');

if ($route === '') {
    $route = \Config\Auth::check()
        ? (\Config\Auth::isAdmin() ? 'admin/list' : 'home/index')
        : 'auth/login';
    $_GET['route'] = $route;
}

if (!\Config\Auth::check()) {
    if (!$isAuthRoute) {
        header('Location: ' . BASE_URL . '/index.php?route=auth/login');
        exit;
    }
} elseif ($route === 'auth/login' || $route === 'auth/signup') {
    header('Location: ' . BASE_URL . '/index.php?route=' . (\Config\Auth::isAdmin() ? 'admin/list' : 'home/index'));
    exit;
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

// ─── NEW MVC ROUTER ──────────────────────────────────────────────────────────
$route = trim((string)($_GET['route'] ?? $route));
$route = trim($route, '/');

[$controllerPart, $actionPart] = array_pad(explode('/', $route, 2), 2, 'index');

$controllerKey  = strtolower(trim($controllerPart));
$controllerStem = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $controllerKey)));
$controllerName = $controllerStem . 'Controller';
$actionName     = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', strtolower($actionPart))));
$actionName     = lcfirst($actionName);

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
