<?php
class Evenement {
    private $id;
    private $titre;
    private $description;
    private $lieu;
    private $date_evenement;
    private $heure;
    private $categorie_id;
    private $created_at;

    public function __construct($titre, $description, $lieu, $date_evenement, $heure, $categorie_id) {
        $this->titre = $titre;
        $this->description = $description;
        $this->lieu = $lieu;
        $this->date_evenement = $date_evenement;
        $this->heure = $heure;
        $this->categorie_id = $categorie_id;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getTitre() { return $this->titre; }
    public function getDescription() { return $this->description; }
    public function getLieu() { return $this->lieu; }
    public function getDateEvenement() { return $this->date_evenement; }
    public function getHeure() { return $this->heure; }
    public function getCategorieId() { return $this->categorie_id; }
    public function getCreatedAt() { return $this->created_at; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setTitre($titre) { $this->titre = $titre; }
    public function setDescription($description) { $this->description = $description; }
    public function setLieu($lieu) { $this->lieu = $lieu; }
    public function setDateEvenement($date_evenement) { $this->date_evenement = $date_evenement; }
    public function setHeure($heure) { $this->heure = $heure; }
    public function setCategorieId($categorie_id) { $this->categorie_id = $categorie_id; }
    
    public function getAllEvents() { }
    public function getUpcomingEvents() { }
    public function getEventById($id) { }
    public function createEvent($evenement) { }
    public function updateEvent($evenement, $id) { }
    public function deleteEvent($id) { }
    public function countAllEvents() { }
    public function countEventsByCategory() { }
}
?>