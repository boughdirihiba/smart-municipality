<?php
class Participation {
    private $id;
    private $user_id;
    private $event_id;
    private $date_participation;
    private $statut;
    private $statut_validation;
    private $date_validation;
    private $commentaire_refus;
    private $nombre_participants;
    
    public function __construct($user_id = null, $event_id = null, $statut = 'inscrit', $nombre_participants = 1) {
        $this->user_id = $user_id;
        $this->event_id = $event_id;
        $this->date_participation = date('Y-m-d H:i:s');
        $this->statut = $statut;
        $this->nombre_participants = $nombre_participants;
        $this->statut_validation = 'en_attente';
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getEventId() { return $this->event_id; }
    public function getDateParticipation() { return $this->date_participation; }
    public function getStatut() { return $this->statut; }
    public function getStatutValidation() { return $this->statut_validation; }
    public function getDateValidation() { return $this->date_validation; }
    public function getCommentaireRefus() { return $this->commentaire_refus; }
    public function getNombreParticipants() { return $this->nombre_participants; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function setEventId($event_id) { $this->event_id = $event_id; }
    public function setDateParticipation($date_participation) { $this->date_participation = $date_participation; }
    public function setStatut($statut) { $this->statut = $statut; }
    public function setStatutValidation($statut_validation) { $this->statut_validation = $statut_validation; }
    public function setDateValidation($date_validation) { $this->date_validation = $date_validation; }
    public function setCommentaireRefus($commentaire_refus) { $this->commentaire_refus = $commentaire_refus; }
    public function setNombreParticipants($nombre_participants) { $this->nombre_participants = $nombre_participants; }
    
    // ========== FONCTIONS CRUD ==========

    public function createParticipation($participation) {return false;
    }
    
    public function getParticipationsByUser($user_id) {
        return [];
    }
    
    public function getParticipationsByEvent($event_id) {
        return [];
    }
    
    public function isUserRegistered($user_id, $event_id) {
        return false;
    }
    
    /**
     * DELETE - Annuler une participation
     */
    public function cancelParticipation($participation_id) {
        // Implémentation dans le contrôleur
        return false;
    }
    
    /**
     * UPDATE - Valider une participation
     */
    public function validateParticipation($participation_id) {
        // Implémentation dans le contrôleur
        return false;
    }
    
    /**
     * UPDATE - Refuser une participation
     */
    public function refuseParticipation($participation_id, $commentaire = null) {
        // Implémentation dans le contrôleur
        return false;
    }
    
    /**
     * READ - Compter les participants validés
     */
    public function countValidatedParticipants($event_id) {
        // Implémentation dans le contrôleur
        return 0;
    }
    
    /**
     * READ - Compter les participants en attente
     */
    public function countPendingParticipants($event_id) {
        // Implémentation dans le contrôleur
        return 0;
    }
    
    /**
     * READ - Compter le total des participations
     */
    public function countAllParticipations() {
        // Implémentation dans le contrôleur
        return 0;
    }
    
    /**
     * READ - Récupérer les places restantes
     */
    public function getPlacesRestantes($event_id) {
        // Implémentation dans le contrôleur
        return ['max' => 0, 'valides' => 0, 'attente' => 0, 'restantes' => 0];
    }
}
?>