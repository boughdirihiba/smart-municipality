<?php
require_once __DIR__ . '/../model/Participation.php';
require_once __DIR__ . '/../config.php';

class ParticipationC {
    
    /**
     * AJOUTER UNE PARTICIPATION (INSCRIPTION)
     * Fonctionne correctement
     */
    public function ajouterParticipation($participation) {
        $db = config::getConnexion();
        try {
            // Vérifier si déjà inscrit
            $check = $db->prepare('SELECT COUNT(*) as total FROM participations 
                                   WHERE user_id = :user_id AND event_id = :event_id');
            $check->execute([
                'user_id' => $participation->getUserId(),
                'event_id' => $participation->getEventId()
            ]);
            $result = $check->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                return ['success' => false, 'message' => 'Vous êtes déjà inscrit à cet événement'];
            }
            
            // Insérer la nouvelle participation
            $query = $db->prepare('
                INSERT INTO participations (user_id, event_id, date_participation, statut) 
                VALUES (:user_id, :event_id, :date_participation, :statut)
            ');
            $query->execute([
                'user_id' => $participation->getUserId(),
                'event_id' => $participation->getEventId(),
                'date_participation' => $participation->getDateParticipation(),
                'statut' => $participation->getStatut()
            ]);
            return ['success' => true, 'message' => 'Inscription réussie'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    /**
     * AFFICHER LES PARTICIPATIONS D'UN UTILISATEUR
     * Corrigé pour éviter les erreurs
     */
    public function afficherParticipationsParUser($user_id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('
                SELECT p.*, 
                       e.id as event_id, 
                       e.titre, 
                       e.date_evenement, 
                       e.heure, 
                       e.lieu, 
                       c.nom as categorie_nom 
                FROM participations p 
                INNER JOIN evenements e ON p.event_id = e.id 
                LEFT JOIN categorie_evenement c ON e.categorie_id = c.id 
                WHERE p.user_id = :user_id 
                ORDER BY e.date_evenement DESC
            ');
            $query->execute(['user_id' => $user_id]);
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            
            // Retourne un tableau vide si aucune participation
            return $result ? $result : [];
        } catch (Exception $e) {
            // En cas d'erreur, retourne un tableau vide au lieu de die()
            error_log('Erreur afficherParticipationsParUser: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * VÉRIFIER SI UN UTILISATEUR EST INSCRIT
     */
    public function estInscrit($user_id, $event_id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT COUNT(*) as total FROM participations 
                                   WHERE user_id = :user_id AND event_id = :event_id');
            $query->execute([
                'user_id' => $user_id,
                'event_id' => $event_id
            ]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['total'] > 0;
        } catch (Exception $e) {
            error_log('Erreur estInscrit: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ANNULER UNE PARTICIPATION (DÉSINSCRIPTION)
     * Corrigé pour fonctionner correctement
     */
    public function annulerParticipation($user_id, $event_id) {
        $db = config::getConnexion();
        try {
            // Vérifier d'abord si l'utilisateur est inscrit
            if (!$this->estInscrit($user_id, $event_id)) {
                return ['success' => false, 'message' => 'Vous n\'êtes pas inscrit à cet événement'];
            }
            
            // Supprimer la participation
            $query = $db->prepare('DELETE FROM participations 
                                   WHERE user_id = :user_id AND event_id = :event_id');
            $query->execute([
                'user_id' => $user_id,
                'event_id' => $event_id
            ]);
            
            // Vérifier si la suppression a fonctionné
            if ($query->rowCount() > 0) {
                return ['success' => true, 'message' => 'Participation annulée avec succès'];
            } else {
                return ['success' => false, 'message' => 'Impossible d\'annuler la participation'];
            }
        } catch (Exception $e) {
            error_log('Erreur annulerParticipation: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur technique: ' . $e->getMessage()];
        }
    }

    /**
     * COMPTER LES PARTICIPATIONS D'UN ÉVÉNEMENT
     */
    public function compterParticipationsParEvenement($event_id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT COUNT(*) as total FROM participations WHERE event_id = :event_id');
            $query->execute(['event_id' => $event_id]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['total'] : 0;
        } catch (Exception $e) {
            error_log('Erreur compterParticipationsParEvenement: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * COMPTER LE TOTAL DES PARTICIPATIONS
     */
    public function compterTotalParticipations() {
        $db = config::getConnexion();
        try {
            $query = $db->query('SELECT COUNT(*) as total FROM participations');
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['total'] : 0;
        } catch (Exception $e) {
            error_log('Erreur compterTotalParticipations: ' . $e->getMessage());
            return 0;
        }
    }
}
?>