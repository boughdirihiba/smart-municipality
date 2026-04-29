<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            if (!function_exists('pdo_connection')) {
                die('Fonction pdo_connection introuvable.');
            }

            self::$connection = \pdo_connection();
        }

        return self::$connection;
    }
}
