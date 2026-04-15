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
    public function getConn() { return $this->conn; }
    public function getTable() { return $this->table; }

    public function setId($id) { $this->id = $id; }
    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function setCategorieId($categorie_id) { $this->categorie_id = $categorie_id; }
    public function setDateRdv($date_rdv) { $this->date_rdv = $date_rdv; }
    public function setHeure($heure) { $this->heure = $heure; }
    public function setStatut($statut) { $this->statut = $statut; }

    public function create() {
        return RendezVousController::create($this);
    }

    public function readAll() {
        return RendezVousController::readAll($this);
    }

    public function readByUser($user_id) {
        return RendezVousController::readByUser($this, $user_id);
    }

    public function readOne($id) {
        return RendezVousController::readOne($this, $id);
    }

    public function update() {
        return RendezVousController::update($this);
    }

    public function delete($id) {
        return RendezVousController::delete($this, $id);
    }

    public function getAvailableSlots($categorie_id, $date_rdv) {
        return RendezVousController::getAvailableSlots($this, $categorie_id, $date_rdv);
    }

    public function isSlotTaken($categorie_id, $date_rdv, $heure) {
        return RendezVousController::isSlotTaken($this, $categorie_id, $date_rdv, $heure);
    }

    public function getAllCategories() {
        return RendezVousController::getAllCategories($this);
    }

}

require_once __DIR__ . '/../controllers/RendezVousController.php';

?>