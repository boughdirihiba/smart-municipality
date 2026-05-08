<?php
require_once "config/database.php";

class NotificationController {

    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    // API: Récupérer le nombre de notifications non lues (pour la cloche)
    public function getUnreadCount() {
        if(isset($_GET['user_id'])) {
            $sql = "SELECT COUNT(*) as total FROM notifications WHERE user_id = :user_id AND statut = 'non_lu'";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":user_id", $_GET['user_id']);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode(['count' => $result['total'] ?? 0]);
            exit;
        }
        echo json_encode(['count' => 0]);
        exit;
    }

    // API: Récupérer toutes les notifications d'un utilisateur (pour la cloche)
    public function getUserNotifications() {
        if(isset($_GET['user_id'])) {
            $sql = "SELECT n.*, d.id as demande_id, doc.nom_fichier as document_nom 
                    FROM notifications n
                    LEFT JOIN demandes d ON n.demande_id = d.id
                    LEFT JOIN documents doc ON n.document_id = doc.id
                    WHERE n.user_id = :user_id 
                    ORDER BY n.date_creation DESC LIMIT 50";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":user_id", $_GET['user_id']);
            $stmt->execute();
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode($notifications);
            exit;
        }
        echo json_encode([]);
        exit;
    }

    // API: Marquer une notification comme lue
    public function markAsRead() {
        if(isset($_POST['id'])) {
            $sql = "UPDATE notifications SET statut = 'lu' WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":id", $_POST['id']);
            $result = $stmt->execute();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            exit;
        }
        echo json_encode(['success' => false]);
        exit;
    }

    // API: Marquer toutes les notifications comme lues
    public function markAllAsRead() {
        if(isset($_POST['user_id'])) {
            $sql = "UPDATE notifications SET statut = 'lu' WHERE user_id = :user_id AND statut = 'non_lu'";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":user_id", $_POST['user_id']);
            $result = $stmt->execute();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            exit;
        }
        echo json_encode(['success' => false]);
        exit;
    }

    // Formulaire modal pour envoyer une notification (dans dashboard)
    public function sendForm() {
        // Récupérer les demandes avec les noms des citoyens
        $sql = "SELECT d.id, d.nom, d.user_id FROM demandes d ORDER BY d.date_creation DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Inclure le formulaire modal
        include "views/notifications/modal.php";
    }

    // Envoyer une notification (AJAX)
    public function send() {
        header('Content-Type: application/json');
        
        if(!isset($_POST['demande_id']) || !isset($_POST['message'])) {
            echo json_encode(['success' => false, 'message' => 'Données manquantes']);
            exit;
        }
        
        $demande_id = intval($_POST['demande_id']);
        $message = trim($_POST['message']);
        $document_id = isset($_POST['document_id']) ? intval($_POST['document_id']) : null;
        
        if(empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Le message ne peut pas être vide']);
            exit;
        }
        
        // Récupérer le user_id du citoyen
        $sqlUser = "SELECT user_id, nom FROM demandes WHERE id = :id";
        $stmtUser = $this->db->prepare($sqlUser);
        $stmtUser->bindParam(":id", $demande_id, PDO::PARAM_INT);
        $stmtUser->execute();
        $demande = $stmtUser->fetch(PDO::FETCH_ASSOC);
        
        if(!$demande) {
            echo json_encode(['success' => false, 'message' => 'Demande non trouvée']);
            exit;
        }
        
        $user_id = !empty($demande['user_id']) ? $demande['user_id'] : 1;
        
        try {
            $sql = "INSERT INTO notifications (user_id, message, statut, date_creation, demande_id, document_id) 
                    VALUES (:user_id, :message, 'non_lu', NOW(), :demande_id, :document_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
            $stmt->bindParam(":message", $message, PDO::PARAM_STR);
            $stmt->bindParam(":demande_id", $demande_id, PDO::PARAM_INT);
            $stmt->bindParam(":document_id", $document_id, PDO::PARAM_INT);
            
            if($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Notification envoyée avec succès à ' . htmlspecialchars($demande['nom'])]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi']);
            }
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
        exit;
    }

    // Supprimer une notification
    public function delete() {
        if(isset($_GET['id'])) {
            $sql = "DELETE FROM notifications WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":id", $_GET['id']);
            $stmt->execute();
        }
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?action=dashboard'));
        exit();
    }
}
?>