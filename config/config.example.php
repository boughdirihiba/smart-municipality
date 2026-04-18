<?php

declare(strict_types=1);

// Copy this file to `config/config.php` and fill values for your environment.

return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'projet',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'redirect_after_login' => 'index.php?route=login',
    'redirect_after_logout' => 'index.php?route=login',
    'redirect_after_signup' => 'index.php?route=login',
];
