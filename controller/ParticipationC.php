<?php
require_once __DIR__ . '/../config.php';

class ParticipationC {
    
    private $db;
    
    public function __construct() {
        $this->db = config::getConnexion();
    }
    
    /**
     * AJOUTER UNE PARTICIPATION
     */
    public function ajouterParticipation($participation) {
        try {
            $check = $this->db->prepare('SELECT COUNT(*) as total FROM participations WHERE user_id = :user_id AND event_id = :event_id');
            $check->execute([
                'user_id' => $participation->getUserId(),
                'event_id' => $participation->getEventId()
            ]);
            $result = $check->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                return ['success' => false, 'message' => 'Vous êtes déjà inscrit'];
            }
            
            $query = $this->db->prepare('
                INSERT INTO participations (user_id, event_id, date_participation, statut, statut_validation, nombre_participants) 
                VALUES (:user_id, :event_id, NOW(), "inscrit", "en_attente", :nombre_participants)
            ');
            $query->execute([
                'user_id' => $participation->getUserId(),
                'event_id' => $participation->getEventId(),
                'nombre_participants' => $participation->getNombreParticipants()
            ]);
            return ['success' => true, 'message' => 'Inscription en attente de validation'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * VALIDER UNE PARTICIPATION
     */
    public function validerParticipation($participation_id) {
        try {
            $query = $this->db->prepare('
                SELECT p.*, u.nom, u.prenom, u.email, e.titre, e.lieu, e.date_evenement, e.heure
                FROM participations p
                JOIN users u ON p.user_id = u.id
                JOIN evenements e ON p.event_id = e.id
                WHERE p.id = :id
            ');
            $query->execute(['id' => $participation_id]);
            $participation = $query->fetch(PDO::FETCH_ASSOC);
            
            if (!$participation) {
                return ['success' => false, 'message' => 'Participation non trouvée'];
            }
            
            $update = $this->db->prepare('
                UPDATE participations 
                SET statut_validation = "valide", date_validation = NOW() 
                WHERE id = :id
            ');
            $update->execute(['id' => $participation_id]);
            
            return [
                'success' => true, 
                'message' => 'Participation validée',
                'ticket' => $participation
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * REFUSER UNE PARTICIPATION
     */
    public function refuserParticipation($participation_id, $commentaire = null) {
        try {
            $query = $this->db->prepare('
                UPDATE participations 
                SET statut_validation = "refuse", commentaire_refus = :commentaire 
                WHERE id = :id
            ');
            $query->execute([
                'id' => $participation_id,
                'commentaire' => $commentaire
            ]);
            return ['success' => true, 'message' => 'Participation refusée'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * ANNULER UNE PARTICIPATION
     */
    public function annulerParticipation($user_id, $event_id) {
        try {
            $query = $this->db->prepare('
                DELETE FROM participations 
                WHERE user_id = :user_id AND event_id = :event_id
            ');
            $query->execute([
                'user_id' => $user_id,
                'event_id' => $event_id
            ]);
            return ['success' => true, 'message' => 'Participation annulée'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * RÉCUPÉRER LE STATUT DE VALIDATION
     */
    public function getStatutValidation($user_id, $event_id) {
        try {
            $query = $this->db->prepare('
                SELECT statut_validation FROM participations 
                WHERE user_id = :user_id AND event_id = :event_id
            ');
            $query->execute([
                'user_id' => $user_id,
                'event_id' => $event_id
            ]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['statut_validation'] : null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * RÉCUPÉRER UNE PARTICIPATION PAR ID
     */
/**
 * RÉCUPÉRER UNE PARTICIPATION PAR ID AVEC DÉTAILS
 */
public function getParticipationById($id) {
    try {
        $query = $this->db->prepare('
            SELECT p.*, u.nom, u.prenom, u.email, e.titre, e.lieu, e.date_evenement, e.heure
            FROM participations p
            JOIN users u ON p.user_id = u.id
            JOIN evenements e ON p.event_id = e.id
            WHERE p.id = :id
        ');
        $query->execute(['id' => $id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}
    
    /**
     * COMPTER LE TOTAL DES PARTICIPATIONS
     */
    public function compterTotalParticipations() {
        try {
            $query = $this->db->query('SELECT COALESCE(SUM(nombre_participants), 0) as total FROM participations');
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return intval($result['total']);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * COMPTER LES PARTICIPATIONS PAR ÉVÉNEMENT
     */
    public function compterParticipationsParEvenement($event_id) {
        try {
            $query = $this->db->prepare('SELECT COALESCE(SUM(nombre_participants), 0) as total FROM participations WHERE event_id = :event_id');
            $query->execute(['event_id' => $event_id]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return intval($result['total']);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * COMPTER LES PARTICIPATIONS VALIDÉES
     */
    public function compterParticipationsValidees($event_id) {
        try {
            $query = $this->db->prepare('SELECT COALESCE(SUM(nombre_participants), 0) as total FROM participations WHERE event_id = :event_id AND statut_validation = "valide"');
            $query->execute(['event_id' => $event_id]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return intval($result['total']);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * COMPTER LES PARTICIPATIONS EN ATTENTE
     */
    public function compterParticipationsEnAttente($event_id) {
        try {
            $query = $this->db->prepare('SELECT COALESCE(SUM(nombre_participants), 0) as total FROM participations WHERE event_id = :event_id AND statut_validation = "en_attente"');
            $query->execute(['event_id' => $event_id]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return intval($result['total']);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * VÉRIFIER SI UN UTILISATEUR EST INSCRIT
     */
    public function estInscrit($user_id, $event_id) {
        try {
            $query = $this->db->prepare('SELECT COUNT(*) as total FROM participations WHERE user_id = :user_id AND event_id = :event_id');
            $query->execute(['user_id' => $user_id, 'event_id' => $event_id]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['total'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * RÉCUPÉRER LES PARTICIPATIONS D'UN ÉVÉNEMENT
     */
    public function getParticipationsByEvent($event_id) {
        try {
            $query = $this->db->prepare('
                SELECT p.*, u.nom, u.prenom, u.email, u.telephone
                FROM participations p
                JOIN users u ON p.user_id = u.id
                WHERE p.event_id = :event_id
                ORDER BY p.date_participation DESC
            ');
            $query->execute(['event_id' => $event_id]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * RÉCUPÉRER LES PARTICIPATIONS D'UN UTILISATEUR
     */
    public function afficherParticipationsParUser($user_id) {
        try {
            $query = $this->db->prepare('
                SELECT p.*, e.titre, e.date_evenement, e.heure, e.lieu, c.nom as categorie_nom 
                FROM participations p
                JOIN evenements e ON p.event_id = e.id
                LEFT JOIN categorie_evenement c ON e.categorie_id = c.id
                WHERE p.user_id = :user_id
                ORDER BY e.date_evenement DESC
            ');
            $query->execute(['user_id' => $user_id]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>