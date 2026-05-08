<?php

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

if (!class_exists('config')) {
    class config
    {
        public static function getConnexion(): PDO
        {
            return pdo_connection();
        }
    }
}