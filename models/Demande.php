<?php
class Demande {
    private $id;
    private $nom;
    private $type_service;
    private $documents;
    private $date_creation;
    private $db;
    
    public function __construct($db = null) {
        $this->db = $db;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getTypeService() { return $this->type_service; }
    public function getDocuments() { return $this->documents; }
    public function getDateCreation() { return $this->date_creation; }
    public function getDb() { return $this->db; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setTypeService($type_service) { $this->type_service = $type_service; }
    public function setDocuments($documents) { $this->documents = $documents; }
    public function setDateCreation($date_creation) { $this->date_creation = $date_creation; }
    public function setDb($db) { $this->db = $db; }
    
    // CRUD (toutes les méthodes retournent des valeurs par défaut)
    public function create() { return false; }
    public function read() { return []; }
    public function getById($id) { return null; }
    public function update() { return false; }
    public function delete() { return false; }
    
    // Statistiques
    public function getTotalDemandes() { return 0; }
    public function getDemandesByService() { return []; }
    public function getDemandesByMonth() { return []; }
    public function getTopServices() { return []; }
    public function getLastDemandes($limit = 5) { return []; }
}
?>