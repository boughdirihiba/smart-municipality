<?php
class CategorieEvenement {
    private $id;
    private $nom;
    private $description;
    private $image_url;
    private $created_at;

    public function __construct($nom, $description, $image_url = null) {
        $this->nom = $nom;
        $this->description = $description;
        $this->image_url = $image_url;
    }

    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getDescription() { return $this->description; }
    public function getImageUrl() { return $this->image_url; }
    public function getCreatedAt() { return $this->created_at; }

    public function setNom($nom) { $this->nom = $nom; }
    public function setDescription($description) { $this->description = $description; }
    public function setImageUrl($image_url) { $this->image_url = $image_url; }
        public function getAllCategories() { }
    public function getCategoryById($id) { }
    public function createCategory($categorie) { }
    public function updateCategory($categorie, $id) { }
    public function deleteCategory($id) { }
    public function countEventsByCategory($categorie_id) { }
}
?>