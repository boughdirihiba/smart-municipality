<?php

declare(strict_types=1);

// Copy this file to `Modules/config.php` and fill values for your environment.

return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'projet',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'redirect_after_login' => '../views/login.html',
    'redirect_after_logout' => '../views/login.html',
    'redirect_after_signup' => '../views/login.html',
];
