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
        // Vérifier si déjà inscrit
        $check = $this->db->prepare('SELECT COUNT(*) as total FROM participations WHERE user_id = :user_id AND event_id = :event_id');
        $check->execute([
            'user_id' => $participation->getUserId(),
            'event_id' => $participation->getEventId()
        ]);
        $result = $check->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            return ['success' => false, 'message' => 'Vous êtes déjà inscrit'];
        }
        
        // Vérifier les places disponibles
        $placesPrises = $this->compterParticipationsValidees($participation->getEventId());
        $queryEvent = $this->db->prepare('SELECT max_participants FROM evenements WHERE id = :id');
        $queryEvent->execute(['id' => $participation->getEventId()]);
        $event = $queryEvent->fetch(PDO::FETCH_ASSOC);
        
        if ($event && ($placesPrises + $participation->getNombreParticipants() > $event['max_participants'])) {
            return ['success' => false, 'message' => 'Nombre de places insuffisant'];
        }
        
        // Insérer la participation
        $query = $this->db->prepare('
            INSERT INTO participations (user_id, event_id, date_participation, statut, statut_validation, nombre_participants)
            VALUES (:user_id, :event_id, NOW(), "inscrit", "valide", :nombre_participants)
        ');
        $query->execute([
            'user_id' => $participation->getUserId(),
            'event_id' => $participation->getEventId(),
            'nombre_participants' => $participation->getNombreParticipants()
        ]);
        
        return ['success' => true, 'message' => 'Inscription confirmée.'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
    }
}
    /**
     * AJOUTER UNE PARTICIPATION DIRECTE (pour admin)
     */
    public function ajouterParticipationDirecte($userId, $eventId, $nbParticipants = 1) {
        try {
            $check = $this->db->prepare('SELECT COUNT(*) as total FROM participations WHERE user_id = :user_id AND event_id = :event_id');
            $check->execute([
                'user_id' => $userId,
                'event_id' => $eventId
            ]);
            $result = $check->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                return ['success' => false, 'message' => 'Vous êtes déjà inscrit'];
            }
            
            // Vérifier les places disponibles
            $eventCheck = $this->db->prepare('SELECT max_participants FROM evenements WHERE id = :event_id');
            $eventCheck->execute(['event_id' => $eventId]);
            $event = $eventCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($event) {
                $placesPrises = $this->compterParticipationsValidees($eventId);
                $placesRestantes = $event['max_participants'] - $placesPrises;
                if ($placesRestantes < $nbParticipants) {
                    return ['success' => false, 'message' => 'Places insuffisantes'];
                }
            }
            
            $query = $this->db->prepare('
                INSERT INTO participations (user_id, event_id, date_participation, statut, statut_validation, nombre_participants) 
                VALUES (:user_id, :event_id, NOW(), "inscrit", "valide", :nombre_participants)
            ');
            $query->execute([
                'user_id' => $userId,
                'event_id' => $eventId,
                'nombre_participants' => $nbParticipants
            ]);
            return ['success' => true, 'message' => 'Inscription réussie'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * VALIDER UNE PARTICIPATION
     */
     /**
     * VALIDER UNE PARTICIPATION AVEC ENVOI D'EMAIL
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
            
            // Email (optional — only if mailer is available)
            $mailerFile = __DIR__ . '/MailerC.php';
            if (file_exists($mailerFile)) {
                require_once $mailerFile;
                $cls = 'MailerC';
                $mailer = new $cls();
                $mailer->envoyerValidationInscription(
                    $participation['email'], $participation['nom'],
                    $participation['prenom'], $participation['titre'],
                    $participation['date_evenement'], $participation['heure'],
                    $participation['lieu']
                );
            }
            
            return [
                'success' => true, 
                'message' => '✅ Participation validée. Un email de confirmation a été enregistré dans les logs.',
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
        /**
     * AFFICHER TOUTES LES PARTICIPATIONS (admin)
     */
    public function afficherToutesParticipations() {
        try {
            $query = $this->db->query('
                SELECT p.*, u.nom, u.prenom, u.email,
                       e.titre, e.lieu, e.date_evenement, e.heure,
                       c.nom as categorie_nom
                FROM participations p
                JOIN users u ON p.user_id = u.id
                JOIN evenements e ON p.event_id = e.id
                LEFT JOIN categorie_evenement c ON e.categorie_id = c.id
                ORDER BY p.date_participation DESC
            ');
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * ALIAS pour modifier.php
     */
    public function afficherParticipationParId($id) {
        return $this->getParticipationById($id);
    }

    /**
     * MODIFIER UNE PARTICIPATION
     */
    public function modifierParticipation($participation, $id) {
        try {
            $query = $this->db->prepare('
                UPDATE participations SET
                    statut = :statut,
                    nombre_participants = :nombre_participants,
                    commentaire_refus = :commentaire_refus
                WHERE id = :id
            ');
            $query->execute([
                'id'                 => $id,
                'statut'             => $participation->getStatut(),
                'nombre_participants'=> $participation->getNombreParticipants(),
                'commentaire_refus'  => $participation->getCommentaireRefus(),
            ]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * SUPPRIMER UNE PARTICIPATION PAR ID
     */
    public function supprimerParticipationById($id) {
        try {
            $query = $this->db->prepare('DELETE FROM participations WHERE id = :id');
            $query->execute(['id' => $id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * RÉCUPÉRER LES INFOS UTILISATEUR
     */
    private function getUserInfo($userId) {
        try {
            $query = $this->db->prepare('SELECT email, nom, prenom FROM users WHERE id = :id');
            $query->execute(['id' => $userId]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * RÉCUPÉRER LES INFOS ÉVÉNEMENT
     */
    private function getEventInfo($eventId) {
        try {
            $query = $this->db->prepare('SELECT titre, date_evenement, heure, lieu FROM evenements WHERE id = :id');
            $query->execute(['id' => $eventId]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
}
?>