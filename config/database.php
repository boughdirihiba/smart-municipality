<?php

namespace Config;

class Database
{
    private $host     = "localhost";
    private $port     = 3306;
    private $db_name  = "smart_municipality";
    private $username = "root";
    private $password = "";
    private $conn;

    /** Singleton instance for getInstance() callers */
    private static ?self $instance = null;

    // ─── Singleton pattern (used by BlogController, DashboardController) ───
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ─── Primary connection method ──────────────────────────────────────────
    public function getConnection(): \PDO
    {
        if ($this->conn instanceof \PDO) {
            return $this->conn;
        }

        $host = getenv('DB_HOST');
        $dbName = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
        $envPort = getenv('DB_PORT');

        $host = is_string($host) && $host !== '' ? $host : $this->host;
        $dbName = is_string($dbName) && $dbName !== '' ? $dbName : $this->db_name;
        $user = is_string($user) && $user !== '' ? $user : $this->username;
        $pass = is_string($pass) ? $pass : $this->password;

        $ports = [$this->port, 3306, 3305];
        if (is_string($envPort) && ctype_digit($envPort)) {
            array_unshift($ports, (int)$envPort);
        }
        $ports = array_values(array_unique(array_filter($ports, static fn($p) => is_int($p) && $p > 0)));

        $hosts = [$host];
        if ($host === 'localhost') {
            $hosts[] = '127.0.0.1';
        } elseif ($host === '127.0.0.1') {
            $hosts[] = 'localhost';
        }

        $lastError = null;
        foreach ($hosts as $h) {
            foreach ($ports as $port) {
                try {
                    $this->conn = new \PDO(
                        'mysql:host=' . $h . ';port=' . (int)$port . ';dbname=' . $dbName . ';charset=utf8mb4',
                        $user,
                        $pass
                    );
                    $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    $this->conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
                    return $this->conn;
                } catch (\PDOException $e) {
                    $lastError = $e;
                }
            }
        }

        die('Erreur de connexion : ' . ($lastError ? $lastError->getMessage() : 'cause inconnue'));
    }

    /**
     * Alias of getConnection() — used by DemandeController, DocumentController,
     * ServiceController, NotificationController, ChatbotController, RatingController,
     * send.php, views/demandes/*, views/services/list.php, etc.
     */
    public function connect(): \PDO
    {
        return $this->getConnection();
    }
}

// ─── Global alias so legacy code can use `new Database()` without a namespace ─
if (!class_exists('\\Database', false)) {
    class_alias('Config\\Database', 'Database');
}
