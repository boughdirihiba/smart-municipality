<?php

namespace Config;

class Database
{
    private $host = "localhost";
    private $port = 3305;
    private $db_name = "projet";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection(): \PDO
    {
        $this->conn = null;

        try {
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
                        'mysql:host=' . $h . ';port=' . (int)$this->port . ';dbname=' . $this->db_name . ';charset=utf8',
                        $this->username,
                        $this->password
                    );
                    $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    return $this->conn;
                } catch (\PDOException $e) {
                    $lastError = $e;
                }
            }

            if ($lastError instanceof \PDOException) {
                throw $lastError;
            }

            throw new \PDOException('Erreur de connexion (cause inconnue)');
        } catch (\PDOException $e) {
            die('Erreur de connexion : ' . $e->getMessage());
        }
    }
}
