<?php
require_once __DIR__ . '/../model/Evenement.php';
require_once __DIR__ . '/../config.php';

class EvenementC {
    
    // Afficher tous les événements
    public function afficherEvenements() {
        $db = config::getConnexion();
        try {
            $query = $db->query('
                SELECT e.*, c.nom as categorie_nom, c.image_url as categorie_image 
                FROM evenements e 
                LEFT JOIN categorie_evenement c ON e.categorie_id = c.id 
                ORDER BY e.date_evenement DESC
            ');
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Afficher les événements à venir
    public function afficherEvenementsAVenir() {
        $db = config::getConnexion();
        try {
            $query = $db->query('
                SELECT e.*, c.nom as categorie_nom, c.image_url as categorie_image 
                FROM evenements e 
                LEFT JOIN categorie_evenement c ON e.categorie_id = c.id 
                WHERE e.date_evenement >= CURDATE()
                ORDER BY e.date_evenement ASC
            ');
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Afficher un événement par ID
    public function afficherEvenementParId($id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('
                SELECT e.*, c.nom as categorie_nom, c.image_url as categorie_image 
                FROM evenements e 
                LEFT JOIN categorie_evenement c ON e.categorie_id = c.id 
                WHERE e.id = :id
            ');
            $query->execute(['id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Dans ajouterEvenement()
public function ajouterEvenement($evenement) {
    $db = config::getConnexion();
    try {
        $query = $db->prepare('
            INSERT INTO evenements (titre, description, max_participants, lieu, date_evenement, heure, categorie_id) 
            VALUES (:titre, :description, :max_participants, :lieu, :date_evenement, :heure, :categorie_id)
        ');
        $query->execute([
            'titre' => $evenement->getTitre(),
            'description' => $evenement->getDescription(),
            'max_participants' => $evenement->getMaxParticipants(),
            'lieu' => $evenement->getLieu(),
            'date_evenement' => $evenement->getDateEvenement(),
            'heure' => $evenement->getHeure(),
            'categorie_id' => $evenement->getCategorieId()
        ]);
        return true;
    } catch (Exception $e) {
        die('Erreur: ' . $e->getMessage());
    }
}

    // Modifier un événement
    public function modifierEvenement($evenement, $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('
                UPDATE evenements SET 
                    titre = :titre, 
                    description = :description, 
                    lieu = :lieu, 
                    date_evenement = :date_evenement, 
                    heure = :heure, 
                    categorie_id = :categorie_id 
                WHERE id = :id
            ');
            $query->execute([
                'id' => $id,
                'titre' => $evenement->getTitre(),
                'description' => $evenement->getDescription(),
                'lieu' => $evenement->getLieu(),
                'date_evenement' => $evenement->getDateEvenement(),
                'heure' => $evenement->getHeure(),
                'categorie_id' => $evenement->getCategorieId()
            ]);
            return true;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Supprimer un événement
    public function supprimerEvenement($id) {
        $db = config::getConnexion();
        try {
            $check = $db->prepare('SELECT COUNT(*) as total FROM participations WHERE event_id = :id');
            $check->execute(['id' => $id]);
            $result = $check->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                $deleteParticipation = $db->prepare('DELETE FROM participations WHERE event_id = :id');
                $deleteParticipation->execute(['id' => $id]);
            }
            
            $query = $db->prepare('DELETE FROM evenements WHERE id = :id');
            $query->execute(['id' => $id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Compter le nombre d'événements
    public function compterEvenements() {
        $db = config::getConnexion();
        try {
            $query = $db->query('SELECT COUNT(*) as total FROM evenements');
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }

    // Compter les événements par catégorie
    public function compterEvenementsParCategorie() {
        $db = config::getConnexion();
        try {
            $query = $db->query('
                SELECT c.nom, COUNT(e.id) as total 
                FROM categorie_evenement c 
                LEFT JOIN evenements e ON c.id = e.categorie_id 
                GROUP BY c.id
            ');
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>