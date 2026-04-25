<?php
class Notification {
    private $id;
    private $user_id;
    private $message;
    private $statut;
    private $date_creation;
    private $db;
    
    public function __construct($db = null) {
        $this->db = $db;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getMessage() { return $this->message; }
    public function getStatut() { return $this->statut; }
    public function getDateCreation() { return $this->date_creation; }
    public function getDb() { return $this->db; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function setMessage($message) { $this->message = $message; }
    public function setStatut($statut) { $this->statut = $statut; }
    public function setDateCreation($date_creation) { $this->date_creation = $date_creation; }
    public function setDb($db) { $this->db = $db; }
    
    // CRUD (toutes les méthodes retournent des valeurs par défaut)
    public function create() { return false; }
    public function read() { return []; }
    public function getById($id) { return null; }
    public function getByUserId($user_id) { return []; }
    public function getUnreadByUserId($user_id) { return []; }
    public function countUnreadByUserId($user_id) { return 0; }
    public function update() { return false; }
    public function markAsRead($id) { return false; }
    public function markAllAsRead($user_id) { return false; }
    public function delete() { return false; }
    public function deleteByUserId($user_id) { return false; }
    public function send($user_id, $message) { return false; }
    public function sendToMultipleUsers($user_ids, $message) { return false; }
}
?>