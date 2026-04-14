<?php
class Demande {

    private $conn;
    private $table = "demandes";

    public $id;
    public $nom;
    public $type_service;
    public $documents;
    public $date_creation;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $sql = "INSERT INTO demandes 
        (id, nom, type_service, documents, date_creation)
        VALUES (:id, :nom, :type_service, :documents, :date_creation)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":type_service", $this->type_service);
        $stmt->bindParam(":documents", $this->documents);
        $stmt->bindParam(":date_creation", $this->date_creation);

        return $stmt->execute();
    }

    public function read() {
        $sql = "SELECT * FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update() {
        $sql = "UPDATE " . $this->table . " 
                SET nom = :nom, 
                    type_service = :type_service, 
                    documents = :documents, 
                    date_creation = :date_creation 
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":type_service", $this->type_service);
        $stmt->bindParam(":documents", $this->documents);
        $stmt->bindParam(":date_creation", $this->date_creation);
        
        return $stmt->execute();
    }

    public function delete() {
        $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    // ========== METHODES STATISTIQUES ==========
    
    // Total des demandes
    public function getTotalDemandes() {
        $sql = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Nombre de demandes par type de service
    public function getDemandesByService() {
        $sql = "SELECT type_service, COUNT(*) as nombre FROM " . $this->table . " GROUP BY type_service ORDER BY nombre DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Demandes par mois (30 derniers jours)
    public function getDemandesByMonth() {
        $sql = "SELECT DATE_FORMAT(date_creation, '%Y-%m') as mois, COUNT(*) as nombre 
                FROM " . $this->table . " 
                WHERE date_creation >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(date_creation, '%Y-%m') 
                ORDER BY mois DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Top 3 des services les plus demandés
    public function getTopServices() {
        $sql = "SELECT type_service, COUNT(*) as nombre 
                FROM " . $this->table . " 
                GROUP BY type_service 
                ORDER BY nombre DESC 
                LIMIT 3";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Dernières demandes
    public function getLastDemandes($limit = 5) {
        $sql = "SELECT * FROM " . $this->table . " ORDER BY date_creation DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>