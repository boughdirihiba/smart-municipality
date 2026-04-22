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
    private $blogModel;
    private $userModel;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->blogModel = new Blog($this->db);
        $this->userModel = new User($this->db);
    }
    
    public function getStats() {
        try {
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
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getAllPosts() {
        $stmt = $this->db->query("SELECT p.*, u.name as user_name, (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updatePost($data) {
        if (empty($data['post_id']) || empty($data['content'])) {
            $_SESSION['error'] = "Le contenu ne peut pas être vide";
            $this->redirectBack();
            return;
        }
        $post_id = (int)$data['post_id'];
        $content = trim(htmlspecialchars($data['content']));
        $stmt = $this->db->prepare("UPDATE posts SET content = :content WHERE id = :id");
        $result = $stmt->execute([':content' => $content, ':id' => $post_id]);
        $_SESSION[ $result ? 'success' : 'error'] = $result ? "Publication modifiée" : "Erreur lors de la modification";
        $this->redirectBack();
    }
    
    public function deletePost($data) {
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
        $_SESSION[ $result ? 'success' : 'error'] = $result ? "Publication supprimée" : "Erreur";
        $this->redirectBack();
    }
    
    // Méthodes utilisateur supprimées (plus utilisées)
    
    private function redirectBack() {
        $url = $_SERVER['HTTP_REFERER'] ?? '/projetweb/views/backoffice.php';
        header('Location: ' . $url);
        exit();
    }
}

// Routeur
$controller = new DashboardController();
$action = $_POST['action'] ?? '';
switch($action) {
    case 'updatePost': $controller->updatePost($_POST); break;
    case 'deletePost': $controller->deletePost($_POST); break;
}
?>