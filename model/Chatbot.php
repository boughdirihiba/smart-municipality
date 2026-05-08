<?php
class Chatbot {
    private $id;
    private $message;
    private $reponse;
    private $intention;
    private $parametres;
    private $created_at;
    private $db;
    
    public function __construct($db = null) {
        $this->db = $db;
    }
    
    // ========== GETTERS ==========
    public function getId() { return $this->id; }
    public function getMessage() { return $this->message; }
    public function getReponse() { return $this->reponse; }
    public function getIntention() { return $this->intention; }
    public function getParametres() { return $this->parametres; }
    public function getCreatedAt() { return $this->created_at; }
    public function getDb() { return $this->db; }
    
    // ========== SETTERS ==========
    public function setId($id) { $this->id = $id; }
    public function setMessage($message) { $this->message = $message; }
    public function setReponse($reponse) { $this->reponse = $reponse; }
    public function setIntention($intention) { $this->intention = $intention; }
    public function setParametres($parametres) { $this->parametres = $parametres; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setDb($db) { $this->db = $db; }
    
    // ========== FONCTIONS UTILISÉES DANS LE CONTRÔLEUR ==========
    
    public function getAllUpcomingEvents() { return []; }
    public function getAllCategories() { return []; }
    public function searchEventsByKeyword($keyword) { return []; }
    public function searchEventsByCategory($category) { return []; }
    public function searchEventsByDate($dateType) { return []; }
    public function searchEventsBySpecificDate($date) { return []; }
    public function searchEvents($criteria) { return []; }
    public function getEventById($id) { return null; }
    public function getUserParticipations($userId) { return []; }
    public function isUserRegistered($userId, $eventId) { return false; }
    public function createParticipation($userId, $eventId, $nombreParticipants = 1) { return false; }
    public function countAllEvents() { return 0; }
    public function countEventsByCategory() { return []; }
    public function callGemini($userMessage) { return null; }
    public function saveConversation($message, $reponse, $intention) { return false; }
    public function getConversationHistory($limit = 50) { return []; }
}
?>