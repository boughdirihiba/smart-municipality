<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Blog.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// user par defaut
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Jean Dupont';
    $_SESSION['user_avatar'] = 'https://randomuser.me/api/portraits/men/1.jpg';
}

class BlogController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Récupère tous les posts avec option de recherche
    public function getPosts($search = '') {
        try {
            // Requête de base
            $sql = "SELECT p.*, u.name as user_name, u.avatar as user_avatar,
                    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count,
                    (SELECT COUNT(*) FROM reactions WHERE post_id = p.id AND type = 'like') as likes,
                    (SELECT COUNT(*) FROM reactions WHERE post_id = p.id AND type = 'heart') as hearts,
                    (SELECT COUNT(*) FROM reactions WHERE post_id = p.id AND type = 'haha') as haha,
                    (SELECT COUNT(*) FROM reactions WHERE post_id = p.id AND type = 'wow') as wow,
                    (SELECT COUNT(*) FROM reactions WHERE post_id = p.id AND type = 'sad') as sad,
                    (SELECT COUNT(*) FROM reactions WHERE post_id = p.id AND type = 'angry') as angry
                    FROM posts p 
                    JOIN users u ON p.user_id = u.id ";
            
            // RECHERCHE 
            if (!empty($search)) {
                $sql .= " WHERE (p.content LIKE :search OR u.name LIKE :search) ";
            }
            
            $sql .= " ORDER BY p.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
        
            
            $stmt->execute();
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
                // Récupérer la réaction de l'utilisateur connecté
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
                    'user_avatar' => $data['user_avatar'] ?? 'https://randomuser.me/api/portraits/lego/1.jpg',
                    'comments_count' => $data['comments_count'] ?? 0,
                    'likes' => $data['likes'] ?? 0,
                    'hearts' => $data['hearts'] ?? 0,
                    'haha' => $data['haha'] ?? 0,
                    'wow' => $data['wow'] ?? 0,
                    'sad' => $data['sad'] ?? 0,
                    'angry' => $data['angry'] ?? 0,
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
    
    // Récupère les commentaires d'un post
    public function getCommentsByPost($post_id) {
        $sql = "SELECT c.*, u.name as user_name, u.avatar as user_avatar 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.post_id = :post_id 
                ORDER BY c.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':post_id' => $post_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // creat post
    public function createPost($data) {
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
        
        $image = isset($data['image']) && !empty($data['image']) ? $data['image'] : null;
        $video = isset($data['video']) && !empty($data['video']) ? $data['video'] : null;
        
        $sql = "INSERT INTO posts (user_id, content, image, video, created_at) 
                VALUES (:user_id, :content, :image, :video, NOW())";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':content' => trim(htmlspecialchars($data['content'])),
            ':image' => $image,
            ':video' => $video
        ]);
        
        if ($result) {
            $_SESSION['success'] = "Post publié avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la publication";
        }
        $this->redirectBack();
    }
    
    //update post
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
        
        if ($result) {
            $_SESSION['success'] = "Post modifié avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la modification";
        }
        
        $this->redirectBack();
    }
    
    //delete post
    public function deletePost($data) {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté";
            $this->redirectBack();
            return;
        }
        
        if (empty($data['post_id'])) {
            $_SESSION['error'] = "ID du post manquant";
            $this->redirectBack();
            return;
        }
        
        $post_id = (int)$data['post_id'];
        //supp cmntr
        $sql1 = "DELETE FROM comments WHERE post_id = :post_id";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute([':post_id' => $post_id]);
        
        // Supprimer les réactions
        $sql2 = "DELETE FROM reactions WHERE post_id = :post_id";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([':post_id' => $post_id]);
        
        // Supprimer le post
        $sql3 = "DELETE FROM posts WHERE id = :id AND user_id = :user_id";
        $stmt3 = $this->db->prepare($sql3);
        $result = $stmt3->execute([
            ':id' => $post_id,
            ':user_id' => $_SESSION['user_id']
        ]);
        
        if ($result) {
            $_SESSION['success'] = "Post supprimé avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression";
        }
        
        $this->redirectBack();
    }
    
    // create cmntr
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
        $sql = "INSERT INTO comments (post_id, user_id, content, created_at) 
                VALUES (:post_id, :user_id, :content, NOW())";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':post_id' => (int)$data['post_id'],
            ':user_id' => $_SESSION['user_id'],
            ':content' => trim(htmlspecialchars($data['content']))
        ]);
        if ($result) {
            $_SESSION['success'] = "Commentaire ajouté";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout";
        }
         $this->redirectBack();
    }
    
    //update cmntr
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
        
        if ($result) {
            $_SESSION['success'] = "Commentaire modifié";
        } else {
            $_SESSION['error'] = "Erreur lors de la modification";
        }
         $this->redirectBack();
    }
    
    // delete cmntr
    public function deleteComment($data) {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté";
            $this->redirectBack();
            return;
        }
        
        if (empty($data['comment_id'])) {
            $_SESSION['error'] = "ID du commentaire manquant";
            $this->redirectBack();
            return;
        }
        
        $sql = "DELETE FROM comments WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':id' => (int)$data['comment_id'],
            ':user_id' => $_SESSION['user_id']
        ]);
        
        if ($result) {
            $_SESSION['success'] = "Commentaire supprimé";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression";
        }
         $this->redirectBack();
    }
    
    // react
    public function reactToPostAjax($data) {
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
        
        // Vérifier si l'utilisateur a déjà réagi
        $sql_check = "SELECT * FROM reactions WHERE post_id = :post_id AND user_id = :user_id";
        $stmt_check = $this->db->prepare($sql_check);
        $stmt_check->execute([':post_id' => $post_id, ':user_id' => $user_id]);
        
        if ($stmt_check->rowCount() > 0) {
            $sql = "UPDATE reactions SET type = :type WHERE post_id = :post_id AND user_id = :user_id";
        } else {
            $sql = "INSERT INTO reactions (post_id, user_id, type) VALUES (:post_id, :user_id, :type)";
        }
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':post_id' => $post_id,
            ':user_id' => $user_id,
            ':type' => $type
        ]);
        
        if ($result) {
            $_SESSION['success'] = "Réaction ajoutée";
        } else {
            $_SESSION['error'] = "Erreur lors de la réaction";
        }
        
        $this->redirectBack();
    }
    // Connexion utilisateur
    public function login($data) {
        if (empty($data['email']) || empty($data['password'])) {
            $_SESSION['error'] = "Veuillez remplir tous les champs";
            $this->redirectBack();
            return;
        }
        
        $email = trim(htmlspecialchars($data['email']));
        $password = trim(htmlspecialchars($data['password']));
        
        $sql = "SELECT id, name, avatar FROM users WHERE email = :email AND password = :password";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email, ':password' => $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_avatar'] = $user['avatar'];
            $_SESSION['success'] = "Bienvenue " . $user['name'] . " !";
        } else {
            $_SESSION['error'] = "Email ou mot de passe incorrect";
        }
        
        $this->redirectBack();
    }
    // log out
    public function logout() {
        session_destroy();
        header('Location: /projetweb/views/frontoffice.php');
        exit();
    }
    
    // Redirection vers la page précédente
    private function redirectBack() {
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            header('Location: /projetweb/views/frontoffice.php');
        }
        exit();
    }
}

$controller = new BlogController();
$action = isset($_POST['action']) ? $_POST['action'] : '';

switch($action) {
    case 'login':
        $controller->login($_POST);
        break;
    case 'logout':
        $controller->logout();
        break;
    case 'createPost':
        $controller->createPost($_POST);
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
    
    case 'reactToPostAjax':
        $controller->reactToPostAjax($_POST);
        break;
}
?>