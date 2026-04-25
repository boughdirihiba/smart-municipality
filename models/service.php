<?php
class Service {
    private $id;
    private $nom;
    private $description;
    private $icone;
    private $actif;
    private $date_creation;
    private $db;
    
    public function __construct($db = null) {
        $this->db = $db;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getDescription() { return $this->description; }
    public function getIcone() { return $this->icone; }
    public function getActif() { return $this->actif; }
    public function getDateCreation() { return $this->date_creation; }
    public function getDb() { return $this->db; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setDescription($description) { $this->description = $description; }
    public function setIcone($icone) { $this->icone = $icone; }
    public function setActif($actif) { $this->actif = $actif; }
    public function setDateCreation($date_creation) { $this->date_creation = $date_creation; }
    public function setDb($db) { $this->db = $db; }
    
    // CRUD (vides)
    public function create() { return false; }
    public function read() { return []; }
    public function getById($id) { return null; }
    public function update() { return false; }
    public function delete() { return false; }
    public function count() { return 0; }
    public function getAllActive() { return []; }
    public function getAllAdmin() { return []; }
}
?>