<?php
class config {
    private static $pdo = null;

    public static function getConnexion() {
        if (self::$pdo == null) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=localhost;dbname=smart_municipality;charset=utf8',
                    'root',
                    '',
                    array(
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    )
                );
            } catch (Exception $e) {
                die('Erreur de connexion : ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
?>