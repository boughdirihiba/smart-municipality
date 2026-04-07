<?php

class RendezVous {

    private $id;
    private $user_id;
    private $service;
    private $date_rdv;
    private $heure;
    private $statut;
    private $conn;
    private $table = "rendez_vous";

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getService() {
        return $this->service;
    }

    public function getDateRdv() {
        return $this->date_rdv;
    }

    public function getHeure() {
        return $this->heure;
    }

    public function getStatut() {
        return $this->statut;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }

    public function setService($service) {
        $this->service = $service;
    }

    public function setDateRdv($date_rdv) {
        $this->date_rdv = $date_rdv;
    }

    public function setHeure($heure) {
        $this->heure = $heure;
    }

    public function setStatut($statut) {
        $this->statut = $statut;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (user_id, service, date_rdv, heure, statut) 
                  VALUES (:user_id, :service, :date_rdv, :heure, :statut)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':service', $this->service);
        $stmt->bindParam(':date_rdv', $this->date_rdv);
        $stmt->bindParam(':heure', $this->heure);
        $stmt->bindParam(':statut', $this->statut);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function readByUser($user_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id ORDER BY date_rdv, heure";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->service = $row['service'];
            $this->date_rdv = $row['date_rdv'];
            $this->heure = $row['heure'];
            $this->statut = $row['statut'];
            return true;
        }

        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET service = :service, date_rdv = :date_rdv, heure = :heure, statut = :statut 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':service', $this->service);
        $stmt->bindParam(':date_rdv', $this->date_rdv);
        $stmt->bindParam(':heure', $this->heure);
        $stmt->bindParam(':statut', $this->statut);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function getAvailableSlots($service, $date_rdv) {
        $all_slots = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];

        $query = "SELECT heure FROM " . $this->table . " 
                  WHERE service = :service AND date_rdv = :date_rdv AND statut != 'annule'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':service', $service);
        $stmt->bindParam(':date_rdv', $date_rdv);
        $stmt->execute();

        $booked = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $available = array_diff($all_slots, $booked);

        return array_values($available);
    }

    public function isSlotTaken($service, $date_rdv, $heure) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " 
                  WHERE service = :service AND date_rdv = :date_rdv AND heure = :heure AND statut != 'annule'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':service', $service);
        $stmt->bindParam(':date_rdv', $date_rdv);
        $stmt->bindParam(':heure', $heure);
        $stmt->execute();

        $count = $stmt->fetchColumn();

        return $count > 0;
    }

}

?>