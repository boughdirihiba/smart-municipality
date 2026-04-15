<?php

class RendezVous {

    private $id;
    private $user_id;
    private $categorie_id;
    private $date_rdv;
    private $heure;
    private $statut;
    private $conn;
    private $table = "rendez_vous";

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getCategorieId() { return $this->categorie_id; }
    public function getDateRdv() { return $this->date_rdv; }
    public function getHeure() { return $this->heure; }
    public function getStatut() { return $this->statut; }

    public function setId($id) { $this->id = $id; }
    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function setCategorieId($categorie_id) { $this->categorie_id = $categorie_id; }
    public function setDateRdv($date_rdv) { $this->date_rdv = $date_rdv; }
    public function setHeure($heure) { $this->heure = $heure; }
    public function setStatut($statut) { $this->statut = $statut; }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (user_id, categorie_id, date_rdv, heure, statut) 
                  VALUES (:user_id, :categorie_id, :date_rdv, :heure, :statut)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':categorie_id', $this->categorie_id);
        $stmt->bindParam(':date_rdv', $this->date_rdv);
        $stmt->bindParam(':heure', $this->heure);
        $stmt->bindParam(':statut', $this->statut);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT r.*, c.nom AS service_nom, u.nom AS user_nom, u.prenom AS user_prenom, u.email AS user_email 
                  FROM " . $this->table . " r 
                  JOIN categorie c ON r.categorie_id = c.id 
                  JOIN users u ON r.user_id = u.id 
                  ORDER BY r.date_rdv DESC, r.heure ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readByUser($user_id) {
        $query = "SELECT r.*, c.nom AS service_nom 
                  FROM " . $this->table . " r 
                  JOIN categorie c ON r.categorie_id = c.id 
                  WHERE r.user_id = :user_id 
                  ORDER BY r.date_rdv DESC, r.heure ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readOne($id) {
        $query = "SELECT r.*, c.nom AS service_nom 
                  FROM " . $this->table . " r 
                  JOIN categorie c ON r.categorie_id = c.id 
                  WHERE r.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->categorie_id = $row['categorie_id'];
            $this->date_rdv = $row['date_rdv'];
            $this->heure = $row['heure'];
            $this->statut = $row['statut'];
            return $row;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET categorie_id = :categorie_id, date_rdv = :date_rdv, heure = :heure, statut = :statut 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categorie_id', $this->categorie_id);
        $stmt->bindParam(':date_rdv', $this->date_rdv);
        $stmt->bindParam(':heure', $this->heure);
        $stmt->bindParam(':statut', $this->statut);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function getAvailableSlots($categorie_id, $date_rdv) {
        $all_slots = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];

        $query = "SELECT heure FROM " . $this->table . " 
                  WHERE categorie_id = :categorie_id AND date_rdv = :date_rdv AND statut != 'annule'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categorie_id', $categorie_id);
        $stmt->bindParam(':date_rdv', $date_rdv);
        $stmt->execute();

        $booked = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $available = array_diff($all_slots, $booked);

        return array_values($available);
    }

    public function isSlotTaken($categorie_id, $date_rdv, $heure) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " 
                  WHERE categorie_id = :categorie_id AND date_rdv = :date_rdv AND heure = :heure AND statut != 'annule'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categorie_id', $categorie_id);
        $stmt->bindParam(':date_rdv', $date_rdv);
        $stmt->bindParam(':heure', $heure);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function getAllCategories() {
        $query = "SELECT * FROM categorie ORDER BY id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

?>