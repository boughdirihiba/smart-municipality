<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Blog.php';
require_once __DIR__ . '/../models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class BlogController {
    private $db;
    private $blogModel;
    private $userModel;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->blogModel = new Blog($this->db);
        $this->userModel = new User($this->db);
    }
    
    // Récupère tous les posts avec leurs commentaires et réactions
    public function getPosts($search = '') {
    try {
        $sql = "SELECT p.*, u.name as user_name, u.avatar as user_avatar,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count
                FROM posts p 
                JOIN users u ON p.user_id = u.id";
        
        if (!empty($search)) {
            $sql .= " WHERE (p.content LIKE :search OR u.name LIKE :search)";
        }
        $sql .= " ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        if (!empty($search)) {
            $searchParam = '%' . $search . '%';
            $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
        }
        $stmt->execute();
        error_log("SQL: $sql, search: $search");
       
        $postsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $posts = [];
        foreach ($postsData as $data) {
            $post = new Blog($this->db);
            $post->setId($data['id']);
            $post->setUserId($data['user_id']);
            $post->setContent($data['content']);
            $post->setImage($data['image']);
            $post->setVideo($data['video']);
            $post->setCreatedAt($data['created_at']);
            
            $comments = $this->getCommentsByPost($data['id']);
            
            $userReaction = null;
            if (isset($_SESSION['user_id'])) {
                $sql_reaction = "SELECT type FROM reactions WHERE post_id = :post_id AND user_id = :user_id";
                $stmt_reaction = $this->db->prepare($sql_reaction);
                $stmt_reaction->execute([
                    ':post_id' => $data['id'],
                    ':user_id' => $_SESSION['user_id']
                ]);
                $reactionData = $stmt_reaction->fetch(PDO::FETCH_ASSOC);
                if ($reactionData) {
                    $userReaction = $reactionData['type'];
                }
            }
            
            $posts[] = [
                'post' => $post,
                'user_name' => $data['user_name'] ?? 'Utilisateur',
                'user_avatar' => !empty($data['user_avatar']) ? $data['user_avatar'] : 'https://randomuser.me/api/portraits/lego/1.jpg',
                'comments_count' => $data['comments_count'] ?? 0,
                'comments' => $comments,
                'user_reaction' => $userReaction
            ];
        }
        return $posts;
    } catch (PDOException $e) {
        error_log("Erreur getPosts: " . $e->getMessage());
        return [];
    }
}
public function searchPostsInPhp($search) {
    $allPosts = $this->getPosts(); 
    if (empty($search)) {
        return $allPosts;
    }
    $filtered = [];
    $searchLower = mb_strtolower($search);
    foreach ($allPosts as $postData) {
        $content = mb_strtolower($postData['post']->getContent());
        $author  = mb_strtolower($postData['user_name']);
        
        if (strpos($content, $searchLower) !== false || 
            strpos($author, $searchLower) !== false) {
            $filtered[] = $postData;
        }
    }
    
    return $filtered;
}
    
    public function getCommentsByPost($post_id) {
        $sql = "SELECT c.*, u.name as user_name, u.avatar as user_avatar 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.post_id = :post_id 
                ORDER BY c.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':post_id' => $post_id]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($comments as &$comment) {
            if (empty($comment['user_avatar'])) {
                $comment['user_avatar'] = 'https://randomuser.me/api/portraits/lego/1.jpg';
            }
        }
        return $comments;
    }
    
    // Connexion
    public function login($data) {
        if (empty($data['email']) || empty($data['password'])) {
            $_SESSION['error'] = "Veuillez remplir tous les champs";
            $this->redirectBack();
            return;
        }
        $email = trim(htmlspecialchars($data['email']));
        $password = trim(htmlspecialchars($data['password']));
        
        $sql = "SELECT id, name, avatar, role FROM users WHERE email = :email AND password = :password";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email, ':password' => $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_avatar'] = !empty($user['avatar']) ? $user['avatar'] : 'https://randomuser.me/api/portraits/lego/1.jpg';
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['success'] = "Bienvenue " . $user['name'] . " !";
            
            if ($user['role'] === 'admin') {
                header('Location: /projetweb/views/backoffice.php');
            } else {
                header('Location: /projetweb/views/frontoffice.php');
            }
            exit();
        } else {
            $_SESSION['error'] = "Email ou mot de passe incorrect";
            $this->redirectBack();
        }
    }
    
    public function logout() {
        session_destroy();
        header('Location: /projetweb/views/frontoffice.php');
        exit();
    }
    
    // Créer un post
    public function createPost($data, $files) {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté";
            $this->redirectBack();
            return;
        }
        if (empty($data['content'])) {
            $_SESSION['error'] = "Le contenu ne peut pas être vide";
            $this->redirectBack();
            return;
        }
        
        $image = null;
        $video = null;
        if (!empty($files['image']['tmp_name'])) {
            $imageData = file_get_contents($files['image']['tmp_name']);
            $image = 'data:image/' . pathinfo($files['image']['name'], PATHINFO_EXTENSION) . ';base64,' . base64_encode($imageData);
        }
        if (!empty($files['video']['tmp_name'])) {
            $videoData = file_get_contents($files['video']['tmp_name']);
            $video = 'data:video/' . pathinfo($files['video']['name'], PATHINFO_EXTENSION) . ';base64,' . base64_encode($videoData);
        }
        
        $sql = "INSERT INTO posts (user_id, content, image, video, created_at) VALUES (:user_id, :content, :image, :video, NOW())";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':content' => trim(htmlspecialchars($data['content'])),
            ':image' => $image,
            ':video' => $video
        ]);
        
        $_SESSION[$result ? 'success' : 'error'] = $result ? "Post publié" : "Erreur";
        $this->redirectBack();
    }
    
    public function updatePost($data) {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté";
            $this->redirectBack();
            return;
        }
        if (empty($data['post_id']) || empty($data['content'])) {
            $_SESSION['error'] = "Données invalides";
            $this->redirectBack();
            return;
        }
        $sql = "UPDATE posts SET content = :content WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':content' => trim(htmlspecialchars($data['content'])),
            ':id' => (int)$data['post_id'],
            ':user_id' => $_SESSION['user_id']
        ]);
        $_SESSION[$result ? 'success' : 'error'] = $result ? "Post modifié" : "Erreur";
        $this->redirectBack();
    }
    
    public function deletePost($data) {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté";
            $this->redirectBack();
            return;
        }
        if (empty($data['post_id'])) {
            $_SESSION['error'] = "ID manquant";
            $this->redirectBack();
            return;
        }
        $post_id = (int)$data['post_id'];
        $this->db->prepare("DELETE FROM comments WHERE post_id = ?")->execute([$post_id]);
        $this->db->prepare("DELETE FROM reactions WHERE post_id = ?")->execute([$post_id]);
        $stmt = $this->db->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        $result = $stmt->execute([$post_id, $_SESSION['user_id']]);
        $_SESSION[$result ? 'success' : 'error'] = $result ? "Post supprimé" : "Erreur";
        $this->redirectBack();
    }
    
    public function createComment($data) {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Connectez-vous pour commenter";
            $this->redirectBack();
            return;
        }
        if (empty($data['post_id']) || empty($data['content'])) {
            $_SESSION['error'] = "Le commentaire ne peut pas être vide";
            $this->redirectBack();
            return;
        }
        $sql = "INSERT INTO comments (post_id, user_id, content, created_at) VALUES (:post_id, :user_id, :content, NOW())";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':post_id' => (int)$data['post_id'],
            ':user_id' => $_SESSION['user_id'],
            ':content' => trim(htmlspecialchars($data['content']))
        ]);
        $_SESSION[$result ? 'success' : 'error'] = $result ? "Commentaire ajouté" : "Erreur";
        $this->redirectBack();
    }
    
    public function updateComment($data) {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté";
            $this->redirectBack();
            return;
        }
        if (empty($data['comment_id']) || empty($data['content'])) {
            $_SESSION['error'] = "Données invalides";
            $this->redirectBack();
            return;
        }
        $sql = "UPDATE comments SET content = :content WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':content' => trim(htmlspecialchars($data['content'])),
            ':id' => (int)$data['comment_id'],
            ':user_id' => $_SESSION['user_id']
        ]);
        $_SESSION[$result ? 'success' : 'error'] = $result ? "Commentaire modifié" : "Erreur";
        $this->redirectBack();
    }
    
    public function deleteComment($data) {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté";
            $this->redirectBack();
            return;
        }
        if (empty($data['comment_id'])) {
            $_SESSION['error'] = "ID manquant";
            $this->redirectBack();
            return;
        }
        $sql = "DELETE FROM comments WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':id' => (int)$data['comment_id'],
            ':user_id' => $_SESSION['user_id']
        ]);
        $_SESSION[$result ? 'success' : 'error'] = $result ? "Commentaire supprimé" : "Erreur";
        $this->redirectBack();
    }
    
    // Réaction classique (recharge la page)
  public function reactToPost($data) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Connectez-vous pour réagir";
        $this->redirectBack();
        return;
    }
    if (empty($data['post_id']) || empty($data['type'])) {
        $_SESSION['error'] = "Données invalides";
        $this->redirectBack();
        return;
    }
    $post_id = (int)$data['post_id'];
    $user_id = $_SESSION['user_id'];
    $type = in_array($data['type'], ['like', 'heart', 'haha', 'wow', 'sad', 'angry']) ? $data['type'] : 'like';
    
    // Vérifier si une réaction existe déjà pour cet utilisateur sur ce post
    $stmt = $this->db->prepare("SELECT type FROM reactions WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        if ($existing['type'] === $type) {
            // Même réaction -> suppression
            $stmt = $this->db->prepare("DELETE FROM reactions WHERE post_id = ? AND user_id = ?");
            $result = $stmt->execute([$post_id, $user_id]);
            $message = $result ? "Réaction supprimée" : "Erreur lors de la suppression";
        } else {
            // Réaction différente -> mise à jour
            $stmt = $this->db->prepare("UPDATE reactions SET type = ? WHERE post_id = ? AND user_id = ?");
            $result = $stmt->execute([$type, $post_id, $user_id]);
            $message = $result ? "Réaction mise à jour" : "Erreur";
        }
    } else {
        // Aucune réaction -> insertion
        $stmt = $this->db->prepare("INSERT INTO reactions (post_id, user_id, type) VALUES (?, ?, ?)");
        $result = $stmt->execute([$post_id, $user_id, $type]);
        $message = $result ? "Réaction ajoutée" : "Erreur";
    }
    
    $_SESSION[$result ? 'success' : 'error'] = $message;
    $this->redirectBack();
}
    // Popup de confirmation avec redirection explicite
    public function confirmDelete() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté.";
            header('Location: /projetweb/views/frontoffice.php');
            exit();
        }
        
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (empty($type) || $id <= 0) {
            $_SESSION['error'] = "Demande invalide.";
            header('Location: /projetweb/views/frontoffice.php');
            exit();
        }
        
        // Si le formulaire de confirmation a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            if ($type === 'post') {
                $this->db->prepare("DELETE FROM comments WHERE post_id = ?")->execute([$id]);
                $this->db->prepare("DELETE FROM reactions WHERE post_id = ?")->execute([$id]);
                $stmt = $this->db->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
                $result = $stmt->execute([$id, $_SESSION['user_id']]);
                $_SESSION[$result ? 'success' : 'error'] = $result ? "Post supprimé" : "Erreur lors de la suppression";
            } elseif ($type === 'comment') {
                $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
                $result = $stmt->execute([$id, $_SESSION['user_id']]);
                $_SESSION[$result ? 'success' : 'error'] = $result ? "Commentaire supprimé" : "Erreur lors de la suppression";
            }
            header('Location: /projetweb/views/frontoffice.php');
            exit();
        }
        
        // Affichage de la page de confirmation
        $messages = ['post' => 'cette publication', 'comment' => 'ce commentaire'];
        $item = $messages[$type] ?? 'cet élément';
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Confirmation - Smart Municipality</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: 'Inter', sans-serif; background: #f0f2f5; min-height: 100vh; display: flex; justify-content: center; align-items: center; }
                .confirm-card { background: white; border-radius: 1rem; padding: 2rem; max-width: 450px; width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; }
                .confirm-icon { font-size: 3rem; color: #dc3545; margin-bottom: 1rem; }
                h3 { color: #1e2a32; margin-bottom: 0.5rem; }
                p { color: #475569; margin-bottom: 1.5rem; }
                .buttons { display: flex; gap: 1rem; justify-content: center; }
                .btn { padding: 0.6rem 1.5rem; border-radius: 0.6rem; text-decoration: none; font-weight: 600; transition: 0.3s; cursor: pointer; border: none; font-size: 1rem; }
                .btn-danger { background: #dc3545; color: white; }
                .btn-danger:hover { background: #bb2d3b; transform: translateY(-2px); }
                .btn-secondary { background: #e2e8f0; color: #475569; }
                .btn-secondary:hover { background: #cbd5e1; }
            </style>
        </head>
        <body>
        <div class="confirm-card">
            <div class="confirm-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h3>Confirmation de suppression</h3>
            <p>Êtes-vous sûr de vouloir supprimer <?php echo $item; ?> ?<br>Cette action est irréversible.</p>
            <form method="POST" action="">
                <input type="hidden" name="confirm" value="yes">
                <div class="buttons">
                    <a href="/projetweb/views/frontoffice.php" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </div>
            </form>
        </div>
        </body>
        </html>
        <?php
        exit();
    }
    
    private function redirectBack() {
        $url = $_SERVER['HTTP_REFERER'] ?? '/projetweb/views/frontoffice.php';
        header('Location: ' . $url);
        exit();
    }
}

// Routeur
$controller = new BlogController();
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch($action) {
    case 'login':
        $controller->login($_POST);
        break;
    case 'logout':
        $controller->logout();
        break;
    case 'createPost':
        $controller->createPost($_POST, $_FILES);
        break;
    case 'updatePost':
        $controller->updatePost($_POST);
        break;
    case 'deletePost':
        $controller->deletePost($_POST);
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
    case 'reactToPost':
        $controller->reactToPost($_POST);
        break;
    case 'confirmDelete':
        $controller->confirmDelete();
        break;
}
?>