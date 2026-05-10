<?php
class Service {
    private $db;
    private $id;
    private $nom;
    private $description;
    private $icone;
    private $date_creation;

    public function __construct($db = null) {
        $this->db = $db;
    }

    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getDescription() { return $this->description; }
    public function getIcone() { return $this->icone; }
    public function getDateCreation() { return $this->date_creation; }
    public function getDb() { return $this->db; }

    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setDescription($description) { $this->description = $description; }
    public function setIcone($icone) { $this->icone = $icone; }
    public function setDateCreation($date_creation) { $this->date_creation = $date_creation; }
}
