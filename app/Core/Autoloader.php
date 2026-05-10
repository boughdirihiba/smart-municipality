<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $paths = [
        BASE_PATH . '/controllers/App/' . str_replace('\\', '/', $relativeClass) . '.php',
        BASE_PATH . '/models/App/' . str_replace('\\', '/', $relativeClass) . '.php',
        BASE_PATH . '/app/' . str_replace('\\', '/', $relativeClass) . '.php',
    ];

    foreach ($paths as $file) {
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});
