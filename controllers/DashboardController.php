<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Blog.php';
require_once __DIR__ . '/../models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class DashboardController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    private function checkAdmin() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = "Accès réservé aux administrateurs.";
            header('Location: /projetweb/views/frontoffice.php');
            exit();
        }
    }
    
    public function getStats() {
        $this->checkAdmin();
        $totalPosts = $this->db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
        $totalUsers = $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalComments = $this->db->query("SELECT COUNT(*) FROM comments")->fetchColumn();
        $totalReactions = $this->db->query("SELECT COUNT(*) FROM reactions")->fetchColumn();
        
        $stmt = $this->db->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY date ASC");
        $postsByDay = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $this->db->query("SELECT SUM(CASE WHEN image IS NOT NULL AND image != '' THEN 1 ELSE 0 END) as with_image, SUM(CASE WHEN video IS NOT NULL AND video != '' THEN 1 ELSE 0 END) as with_video, SUM(CASE WHEN (image IS NULL OR image = '') AND (video IS NULL OR video = '') THEN 1 ELSE 0 END) as text_only FROM posts");
        $contentDist = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $this->db->query("SELECT DATE(created_at) as date, COUNT(*) as posts_count FROM posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY date ASC");
        $activityTimeline = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'totalPosts' => $totalPosts,
            'totalUsers' => $totalUsers,
            'totalComments' => $totalComments,
            'totalReactions' => $totalReactions,
            'postsByDay' => $postsByDay,
            'contentDistribution' => ['with_image' => (int)$contentDist['with_image'], 'with_video' => (int)$contentDist['with_video'], 'text_only' => (int)$contentDist['text_only']],
            'activityTimeline' => $activityTimeline
        ];
    }
    
    public function getAllPosts() {
        $this->checkAdmin();
        $stmt = $this->db->query("SELECT p.*, u.name as user_name, (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllComments() {
        $this->checkAdmin();
        $stmt = $this->db->query("SELECT c.*, u.name as user_name, p.content as post_content FROM comments c JOIN users u ON c.user_id = u.id JOIN posts p ON c.post_id = p.id ORDER BY c.created_at DESC LIMIT 50");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createPost($data, $files) {
        $this->checkAdmin();
        $content = trim(htmlspecialchars($data['content']));
        if (empty($content)) {
            $_SESSION['error'] = "Le contenu ne peut pas être vide";
            $this->redirectBack();
            return;
        }
        $image = null;
        $video = null;
        // Vérifier qu'on n'a pas les deux fichiers en même temps
        if (!empty($files['image']['tmp_name']) && !empty($files['video']['tmp_name'])) {
            $_SESSION['error'] = "Vous ne pouvez pas ajouter une image ET une vidéo en même temps.";
            $this->redirectBack();
            return;
        }
        if (!empty($files['image']['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $files['image']['tmp_name']);
            finfo_close($finfo);
            if (strpos($mime, 'image/') !== 0) {
                $_SESSION['error'] = "Le fichier image n'est pas valide.";
                $this->redirectBack();
                return;
            }
            $imageData = file_get_contents($files['image']['tmp_name']);
            $image = 'data:image/' . pathinfo($files['image']['name'], PATHINFO_EXTENSION) . ';base64,' . base64_encode($imageData);
        }
        if (!empty($files['video']['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $files['video']['tmp_name']);
            finfo_close($finfo);
            if (strpos($mime, 'video/') !== 0) {
                $_SESSION['error'] = "Le fichier vidéo n'est pas valide.";
                $this->redirectBack();
                return;
            }
            $videoData = file_get_contents($files['video']['tmp_name']);
            $video = 'data:video/' . pathinfo($files['video']['name'], PATHINFO_EXTENSION) . ';base64,' . base64_encode($videoData);
        }
        $stmt = $this->db->prepare("INSERT INTO posts (user_id, content, image, video, created_at) VALUES (?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$_SESSION['user_id'], $content, $image, $video]);
        $_SESSION[$result ? 'success' : 'error'] = $result ? "Post ajouté" : "Erreur lors de l'ajout";
        $this->redirectBack();
    }
    
    public function updatePost($data, $files) {
        $this->checkAdmin();
        if (empty($data['post_id']) || empty($data['content'])) {
            $_SESSION['error'] = "Données invalides";
            $this->redirectBack();
            return;
        }
        $post_id = (int)$data['post_id'];
        $content = htmlspecialchars(trim($data['content']));
        
        // Récupérer les anciens médias
        $stmt = $this->db->prepare("SELECT image, video FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $old = $stmt->fetch(PDO::FETCH_ASSOC);
        $image = $old['image'];
        $video = $old['video'];
        
    
        // Nouveaux fichiers (priorité sur les anciens)
        if (!empty($files['image']['tmp_name']) && !empty($files['video']['tmp_name'])) {
            $_SESSION['error'] = "Vous ne pouvez pas remplacer par une image ET une vidéo en même temps.";
            $this->redirectBack();
            return;
        }
        if (!empty($files['image']['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $files['image']['tmp_name']);
            finfo_close($finfo);
            if (strpos($mime, 'image/') !== 0) {
                $_SESSION['error'] = "Le nouveau fichier image est invalide.";
                $this->redirectBack();
                return;
            }
            $imageData = file_get_contents($files['image']['tmp_name']);
            $image = 'data:image/' . pathinfo($files['image']['name'], PATHINFO_EXTENSION) . ';base64,' . base64_encode($imageData);
        }
        if (!empty($files['video']['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $files['video']['tmp_name']);
            finfo_close($finfo);
            if (strpos($mime, 'video/') !== 0) {
                $_SESSION['error'] = "Le nouveau fichier vidéo est invalide.";
                $this->redirectBack();
                return;
            }
            $videoData = file_get_contents($files['video']['tmp_name']);
            $video = 'data:video/' . pathinfo($files['video']['name'], PATHINFO_EXTENSION) . ';base64,' . base64_encode($videoData);
        }
        
        $sql = "UPDATE posts SET content = ?, image = ?, video = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$content, $image, $video, $post_id]);
        $_SESSION[$result ? 'success' : 'error'] = $result ? "Post modifié avec succès" : "Erreur lors de la modification";
        $this->redirectBack();
    }
    
    public function deletePost($data) {
        $this->checkAdmin();
        if (empty($data['post_id'])) {
            $_SESSION['error'] = "ID manquant";
            $this->redirectBack();
            return;
        }
        $post_id = (int)$data['post_id'];
        $this->db->prepare("DELETE FROM comments WHERE post_id = ?")->execute([$post_id]);
        $this->db->prepare("DELETE FROM reactions WHERE post_id = ?")->execute([$post_id]);
        $stmt = $this->db->prepare("DELETE FROM posts WHERE id = ?");
        $result = $stmt->execute([$post_id]);
        $_SESSION[$result ? 'success' : 'error'] = $result ? "Post supprimé" : "Erreur suppression";
        $this->redirectBack();
    }
    
    public function createComment($data) {
        $this->checkAdmin();
        if (empty($data['post_id']) || empty($data['content'])) {
            $_SESSION['error'] = "Données invalides";
            $this->redirectBack();
            return;
        }
        $stmt = $this->db->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
        $result = $stmt->execute([$data['post_id'], $_SESSION['user_id'], htmlspecialchars(trim($data['content']))]);
        $_SESSION[$result ? 'success' : 'error'] = $result ? "Commentaire ajouté" : "Erreur";
        $this->redirectBack();
    }
    
    public function updateComment($data) {
        $this->checkAdmin();
        if (empty($data['comment_id']) || empty($data['content'])) {
            $_SESSION['error'] = "Données invalides";
            $this->redirectBack();
            return;
        }
        $stmt = $this->db->prepare("UPDATE comments SET content = ? WHERE id = ?");
        $result = $stmt->execute([htmlspecialchars(trim($data['content'])), $data['comment_id']]);
        $_SESSION[$result ? 'success' : 'error'] = $result ? "Commentaire modifié" : "Erreur";
        $this->redirectBack();
    }
    
    public function deleteComment($data) {
        $this->checkAdmin();
        if (empty($data['comment_id'])) {
            $_SESSION['error'] = "ID manquant";
            $this->redirectBack();
            return;
        }
        $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ?");
        $result = $stmt->execute([$data['comment_id']]);
        $_SESSION[$result ? 'success' : 'error'] = $result ? "Commentaire supprimé" : "Erreur";
        $this->redirectBack();
    }
    
    private function redirectBack() {
        $url = $_SERVER['HTTP_REFERER'] ?? '/projetweb/views/backoffice.php';
        header('Location: ' . $url);
        exit();
    }
}

// Gestion des actions
$controller = new DashboardController();
$action = $_POST['action'] ?? '';
switch($action) {
    case 'updatePost': 
        $controller->updatePost($_POST, $_FILES); 
        break;
    case 'deletePost': 
        $controller->deletePost($_POST); 
        break;
    case 'createPost': 
        $controller->createPost($_POST, $_FILES); 
        break;
    case 'createComment': 
        $controller->createComment($_POST); 
        break;
    case 'updateComment': 
        $controller->updateComment($_POST); 
        break;
    case 'deleteComment': 
        $controller->deleteComment($_POST); 
        break;
}
?>