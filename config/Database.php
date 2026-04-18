<?php

declare(strict_types=1);

namespace Config;

use PDO;

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $configPath = __DIR__ . '/config.php';
        if (!is_file($configPath)) {
            $configPath = __DIR__ . '/config.example.php';
        }

        $config = is_file($configPath) ? require $configPath : [];
        $db = is_array($config) ? ($config['db'] ?? null) : null;
        if (!is_array($db)) {
            throw new \RuntimeException('Database config missing. Create config/config.php from config/config.example.php');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $db['host'],
            (int) $db['port'],
            $db['name'],
            $db['charset']
        );

        self::$pdo = new PDO(
            $dsn,
            $db['user'],
            $db['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return self::$pdo;
    }
}
