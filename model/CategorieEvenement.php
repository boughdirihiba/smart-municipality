<?php
class CategorieEvenement {
    private $id;
    private $nom;
    private $description;
    private $image_url;
    private $created_at;
    private $db;
    
    public function __construct($nom = null, $description = null, $image_url = null) {
        $this->nom = $nom;
        $this->description = $description;
        $this->image_url = $image_url;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getDescription() { return $this->description; }
    public function getImageUrl() { return $this->image_url; }
    public function getCreatedAt() { return $this->created_at; }
    public function getDb() { return $this->db; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setDescription($description) { $this->description = $description; }
    public function setImageUrl($image_url) { $this->image_url = $image_url; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setDb($db) { $this->db = $db; }
    
    // CRUD Catégories
    public function getAllCategories() { return []; }
    public function getCategoryById($id) { return null; }
    public function createCategory($categorie) { return false; }
    public function updateCategory($categorie, $id) { return false; }
    public function deleteCategory($id) { return false; }
    public function countEventsByCategory($categorie_id) { return 0; }
}
?>