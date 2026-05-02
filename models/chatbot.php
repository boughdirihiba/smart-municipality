<?php
class Chatbot {
    private $id;
    private $user_id;
    private $message;
    private $response;
    private $created_at;
    private $db;
    
    public function __construct($db = null) {
        $this->db = $db;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getMessage() { return $this->message; }
    public function getResponse() { return $this->response; }
    public function getCreatedAt() { return $this->created_at; }
    public function getDb() { return $this->db; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function setMessage($message) { $this->message = $message; }
    public function setResponse($response) { $this->response = $response; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setDb($db) { $this->db = $db; }
    
    // CRUD
    public function create() { return false; }
    public function read() { return []; }
    public function getById($id) { return null; }
    public function update() { return false; }
    public function delete() { return false; }
    
    // Méthodes spécifiques (vides)
    public function ask($message) { return ""; }
    public function getSuggestions() { return []; }
    public function saveConversation() { return false; }
    public function getHistory($user_id) { return []; }
    public function clearHistory($user_id) { return false; }
}
?>