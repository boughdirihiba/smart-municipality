<?php
class Rating {
    private $id;
    private $service_id;
    private $user_id;
    private $rating;
    private $comment;
    private $created_at;
    private $db;
    
    public function __construct($db = null) {
        $this->db = $db;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getServiceId() { return $this->service_id; }
    public function getUserId() { return $this->user_id; }
    public function getRating() { return $this->rating; }
    public function getComment() { return $this->comment; }
    public function getCreatedAt() { return $this->created_at; }
    public function getDb() { return $this->db; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setServiceId($service_id) { $this->service_id = $service_id; }
    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function setRating($rating) { $this->rating = $rating; }
    public function setComment($comment) { $this->comment = $comment; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setDb($db) { $this->db = $db; }
    
    // CRUD de base (vides)
    public function create() { return false; }
    public function read() { return []; }
    public function getById($id) { return null; }
    public function update() { return false; }
    public function delete() { return false; }
    
    // Méthodes spécifiques pour les ratings (à implémenter dans le contrôleur)
    public function addOrUpdateRating($service_id, $user_id, $rating, $comment = null) { return false; }
    public function getAverageRating($service_id) { return 0; }
    public function getUserRating($service_id, $user_id) { return null; }
    public function getAllRatings($service_id) { return []; }
    public function getTopRatedServices($limit = 10) { return []; }
}
?>