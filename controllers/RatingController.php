<?php
// Démarrer la session au début du fichier
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/database.php";

class RatingController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    /**
     * Ajouter ou mettre à jour une note
     */
    public function addOrUpdate() {
        header('Content-Type: application/json');
        
        // Utiliser un identifiant unique basé sur la session + IP
        $session_id = session_id(); // Récupère l'ID de session
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $visitor_id = md5($session_id . $ip_address); // Identifiant unique du visiteur
        
        $service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : null;
        
        if($service_id <= 0 || $rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            exit;
        }
        
        try {
            // Vérifier si ce visiteur a déjà noté ce service
            $checkSql = "SELECT id FROM ratings WHERE service_id = :service_id AND visitor_id = :visitor_id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindParam(":service_id", $service_id);
            $checkStmt->bindParam(":visitor_id", $visitor_id);
            $checkStmt->execute();
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if($existing) {
                // Mettre à jour
                $sql = "UPDATE ratings SET rating = :rating, comment = :comment, created_at = NOW() 
                        WHERE service_id = :service_id AND visitor_id = :visitor_id";
            } else {
                // Insérer (sans user_id)
                $sql = "INSERT INTO ratings (service_id, visitor_id, rating, comment) 
                        VALUES (:service_id, :visitor_id, :rating, :comment)";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":service_id", $service_id);
            $stmt->bindParam(":visitor_id", $visitor_id);
            $stmt->bindParam(":rating", $rating);
            $stmt->bindParam(":comment", $comment);
            
            if($stmt->execute()) {
                // Récupérer la nouvelle moyenne
                $avgSql = "SELECT AVG(rating) as average, COUNT(*) as count FROM ratings WHERE service_id = :service_id";
                $avgStmt = $this->db->prepare($avgSql);
                $avgStmt->bindParam(":service_id", $service_id);
                $avgStmt->execute();
                $stats = $avgStmt->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'message' => $existing ? 'Note mise à jour' : 'Note ajoutée',
                    'average' => round($stats['average'] ?? 0, 1),
                    'count' => (int)($stats['count'] ?? 0),
                    'user_rating' => $rating
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement']);
            }
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur SQL: ' . $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Récupérer les notes d'un service
     */
    public function getServiceRatings() {
        header('Content-Type: application/json');
        
        $service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
        
        if($service_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Service invalide']);
            exit;
        }
        
        try {
            // Moyenne et compteur
            $avgSql = "SELECT AVG(rating) as average, COUNT(*) as count FROM ratings WHERE service_id = :service_id";
            $avgStmt = $this->db->prepare($avgSql);
            $avgStmt->bindParam(":service_id", $service_id);
            $avgStmt->execute();
            $stats = $avgStmt->fetch(PDO::FETCH_ASSOC);
            
            // Note du visiteur actuel
            $session_id = session_id();
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $visitor_id = md5($session_id . $ip_address);
            
            $user_rating = null;
            $userSql = "SELECT rating FROM ratings WHERE service_id = :service_id AND visitor_id = :visitor_id";
            $userStmt = $this->db->prepare($userSql);
            $userStmt->bindParam(":service_id", $service_id);
            $userStmt->bindParam(":visitor_id", $visitor_id);
            $userStmt->execute();
            $userRating = $userStmt->fetch(PDO::FETCH_ASSOC);
            $user_rating = $userRating ? (int)$userRating['rating'] : null;
            
            // Liste des avis
            $listSql = "SELECT rating, comment, created_at, 'Visiteur' as user_name 
                        FROM ratings 
                        WHERE service_id = :service_id 
                        ORDER BY created_at DESC 
                        LIMIT 10";
            $listStmt = $this->db->prepare($listSql);
            $listStmt->bindParam(":service_id", $service_id);
            $listStmt->execute();
            $ratings = $listStmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'average' => round($stats['average'] ?? 0, 1),
                'count' => (int)($stats['count'] ?? 0),
                'user_rating' => $user_rating,
                'ratings' => $ratings
            ]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur SQL: ' . $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Supprimer sa note
     */
    public function deleteRating() {
        header('Content-Type: application/json');
        
        $service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
        
        $session_id = session_id();
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $visitor_id = md5($session_id . $ip_address);
        
        if($service_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Service invalide']);
            exit;
        }
        
        try {
            $sql = "DELETE FROM ratings WHERE service_id = :service_id AND visitor_id = :visitor_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":service_id", $service_id);
            $stmt->bindParam(":visitor_id", $visitor_id);
            $stmt->execute();
            
            // Récupérer la nouvelle moyenne
            $avgSql = "SELECT AVG(rating) as average, COUNT(*) as count FROM ratings WHERE service_id = :service_id";
            $avgStmt = $this->db->prepare($avgSql);
            $avgStmt->bindParam(":service_id", $service_id);
            $avgStmt->execute();
            $stats = $avgStmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'average' => round($stats['average'] ?? 0, 1),
                'count' => (int)($stats['count'] ?? 0)
            ]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur SQL: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>