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

        $hosts = [$this->host];
        if ($this->host === 'localhost') {
            $hosts[] = '127.0.0.1';
        } elseif ($this->host === '127.0.0.1') {
            $hosts[] = 'localhost';
        }

        $lastError = null;
        foreach ($hosts as $h) {
            try {
                $this->conn = new \PDO(
                    'mysql:host=' . $h . ';port=' . (int)$this->port . ';dbname=' . $this->db_name . ';charset=utf8mb4',
                    $this->username,
                    $this->password
                );
                $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
                return $this->conn;
            } catch (\PDOException $e) {
                $lastError = $e;
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
if (!class_exists('Database', false)) {
    class_alias('Config\\Database', 'Database');
}