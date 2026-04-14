<?php
class Database {

    private $host = "localhost";
    private $db_name = "smart_municipality";
    private $username = "root";
    private $password = "";

    public function connect() {

        try {
            $conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );

            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conn;

        } catch(PDOException $e) {
            echo "Erreur connexion : " . $e->getMessage();
        }
    }
}