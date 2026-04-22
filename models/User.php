<?php
class User {
    private $id;
    private $name;
    private $email;
    private $password;
    private $avatar;
    private $role;
    private $created_at;
    private $db;
    
    public function __construct($db = null) {
        $this->db = $db;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getAvatar() { return $this->avatar; }
    public function getRole() { return $this->role; }
    public function getCreatedAt() { return $this->created_at; }
    public function getDb() { return $this->db; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setName($name) { $this->name = $name; }
    public function setEmail($email) { $this->email = $email; }
    public function setPassword($password) { $this->password = $password; }
    public function setAvatar($avatar) { $this->avatar = $avatar; }
    public function setRole($role) { $this->role = $role; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setDb($db) { $this->db = $db; }
    
    // ============ APPELS DES FONCTIONS (déclarations vides) ============
    
    public function getAll() { return []; }
    public function getById($id) { return null; }
    public function create($data) { return false; }
    public function update($data) { return false; }
    public function delete($id) { return false; }
    public function emailExists($email, $excludeId = null) { return false; }
    public function countTotal() { return 0; }
     public function getTotalPosts() { return 0; }
    public function getTotalUsers() { return 0; }
    public function getTotalComments() { return 0; }
    public function getTotalReactions() { return 0; }
    public function getPostsByDay() { return []; }
    public function getContentDistribution() { return ['with_image' => 0, 'with_video' => 0, 'text_only' => 0]; }
    public function getActivityTimeline() { return []; }
}
?>