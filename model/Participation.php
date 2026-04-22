<?php
class Participation {
    private $id;
    private $user_id;
    private $event_id;
    private $date_participation;
    private $statut;

    public function __construct($user_id, $event_id, $statut = 'inscrit') {
        $this->user_id = $user_id;
        $this->event_id = $event_id;
        $this->date_participation = date('Y-m-d H:i:s');
        $this->statut = $statut;
    }

    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getEventId() { return $this->event_id; }
    public function getDateParticipation() { return $this->date_participation; }
    public function getStatut() { return $this->statut; }

    public function setStatut($statut) { $this->statut = $statut; }
    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function setEventId($event_id) { $this->event_id = $event_id; }

    public function createParticipation($participation) { }
    public function getParticipationsByUser($user_id) { }
    public function isUserRegistered($user_id, $event_id) { }
    public function cancelParticipation($user_id, $event_id) { }
    public function countParticipantsByEvent($event_id) { }
    public function countAllParticipations() { }
}
?>