<?php
require_once __DIR__ . '/../model/CategorieEvenement.php';
require_once __DIR__ . '/../config.php';

class CategorieEvenementC {
    
    public function afficherCategories() {
        $db = config::getConnexion();
        try {
            $query = $db->query('SELECT * FROM categorie_evenement ORDER BY id DESC');
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function afficherCategorieParId($id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM categorie_evenement WHERE id = :id');
            $query->execute(['id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function ajouterCategorie($categorie) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('INSERT INTO categorie_evenement (nom, description, image_url) VALUES (:nom, :description, :image_url)');
            $query->execute([
                'nom' => $categorie->getNom(),
                'description' => $categorie->getDescription(),
                'image_url' => $categorie->getImageUrl()
            ]);
            return true;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function modifierCategorie($categorie, $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('UPDATE categorie_evenement SET nom = :nom, description = :description, image_url = :image_url WHERE id = :id');
            $query->execute([
                'id' => $id,
                'nom' => $categorie->getNom(),
                'description' => $categorie->getDescription(),
                'image_url' => $categorie->getImageUrl()
            ]);
            return true;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function supprimerCategorie($id) {
        $db = config::getConnexion();
        try {
            $check = $db->prepare('SELECT COUNT(*) as total FROM evenements WHERE categorie_id = :id');
            $check->execute(['id' => $id]);
            $result = $check->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                return ['success' => false, 'message' => 'Cette catégorie contient des événements'];
            }
            
            $query = $db->prepare('DELETE FROM categorie_evenement WHERE id = :id');
            $query->execute(['id' => $id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function compterEvenementsParCategorie($categorie_id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT COUNT(*) as total FROM evenements WHERE categorie_id = :categorie_id');
            $query->execute(['categorie_id' => $categorie_id]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }
}
?>