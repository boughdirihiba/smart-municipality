<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Blog.php';
require_once __DIR__ . '/../models/User.php';

class BlogController {
    private $db;
    private $blogModel;
    private $userModel;
    private $current_lang = 'fr';
    private $translations = [];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->blogModel = new Blog($this->db);
        $this->userModel = new User($this->db);
        $this->initLanguage();
    }

    // ===================== GESTION LANGUE (reste sur la même page) =====================
    private function initLanguage() {
        $allowed = ['fr', 'en', 'ar'];
        $default = 'fr';
        
        // Si le paramètre 'lang' est présent, on change la session et on recharge la page actuelle
        if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed)) {
            $_SESSION['app_lang'] = $_GET['lang'];
            $this->current_lang = $_GET['lang'];
            
            // Reconstruire l'URL actuelle sans le paramètre 'lang'
            $url = strtok($_SERVER['REQUEST_URI'], '?');
            $params = [];
            parse_str($_SERVER['QUERY_STRING'], $params);
            unset($params['lang']); // enlever lang
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }
            header("Location: $url");
            exit;
        } 
        // Sinon, prendre la langue de session ou la valeur par défaut
        elseif (isset($_SESSION['app_lang']) && in_array($_SESSION['app_lang'], $allowed)) {
            $this->current_lang = $_SESSION['app_lang'];
        } else {
            $this->current_lang = $default;
            $_SESSION['app_lang'] = $this->current_lang;
        }
        $this->loadTranslations();
    }

    private function loadTranslations() {
        $this->translations = [
            'fr' => [
                'total_posts' => 'Totale des publications',
                'total_users' => 'Totale des utilisateurs',
                'total_comments' => 'Totale des commentaires',
                'total_reactions' => 'Totale des réactions',
                'posts_by_day' => 'Publications par jour',
                'media_distribution' => 'Distribution des médias',
                'activity_30d' => 'Activité (30 jours)',
                'with_image' => 'Avec image',
                'with_video' => 'Avec vidéo',
                'text_only' => 'Texte seul',
                'create_post' => 'Créer une publication',
                'add_comment' => 'Ajouter un commentaire',
                'all_posts' => 'Toutes les publications',
                'recent_comments' => 'Commentaires récents',
                'author' => 'Auteur',
                'post' => 'Publication',
                'date' => 'Date',
                'actions' => 'Actions',
                'edit' => 'Modifier',
                'delete' => 'Supprimer',
                'listen' => 'Écouter',
                'items' => 'éléments',
                'choose_post' => 'Choisir une publication',
                'current_media' => 'Médias actuels',
                'current_image' => 'Image actuelle',
                'current_video' => 'Vidéo actuelle',
                'no_image' => 'Aucune image',
                'no_video' => 'Aucune vidéo',
                'refresh' => 'Actualiser',
                'error_image_video_both' => 'Vous ne pouvez pas ajouter une image ET une vidéo en même temps.',
                'error_select_post' => 'Veuillez choisir une publication.',
                'read_more' => 'Lire la suite',
                'back_to_blog' => 'Retour au blog',
                'article_not_found' => 'Article introuvable.',
                'profile' => 'Profil',
                'events' => 'Événements',
                'map' => 'Carte',
                'blog' => 'Blog',
                'services' => 'Services',
                'appointments' => 'Rendez-vous',
                'dashboard' => 'Tableau de bord',
                'image' => 'Image',
                'video' => 'Vidéo',
                'publish' => 'Publier',
                'login_to_post' => 'Connectez-vous pour publier',
                'login_to_comment' => 'Connectez-vous pour commenter',
                'comment' => 'Commenter',
                'share' => 'Partager',
                'trends' => 'Tendances',
                'trend1' => 'Nouveau centre aquatique',
                'trend2' => 'Plantation participative',
                'trend3' => 'Véloroutes urbaines',
                'announcements' => 'Annonces municipales',
                'alert1' => 'Réunion publique le 25 avril',
                'alert2' => 'Collecte des déchets modifiée',
                'ai_insights' => 'AI Insights',
                'category' => 'Catégorie',
                'mobility' => 'Mobilité verte',
                'sentiment' => 'sentiment positif',
                'login_title' => 'Connexion',
                'cancel' => 'Annuler',
                'login_btn' => 'Se connecter',
                'logged_as' => 'Connecté en tant que',
                'logout' => 'Déconnexion',
                'edit_post_title' => 'Modifier la publication',
                'edit_comment_title' => 'Modifier le commentaire',
                'save' => 'Enregistrer',
                'confirm_delete_title' => 'Confirmation de suppression',
                'confirm_delete_post' => 'Êtes-vous sûr de vouloir supprimer cette publication ?<br>Cette action est irréversible.',
                'confirm_delete_comment' => 'Êtes-vous sûr de vouloir supprimer ce commentaire ?<br>Cette action est irréversible.',
                'delete' => 'Supprimer',
                'ok' => 'OK',
                'no_posts' => 'Aucun post trouvé.',
                'search_placeholder' => 'Rechercher un post ou un utilisateur...',
                'write_comment' => 'Écrire un commentaire...',
                'react' => 'Réagir',
                'like' => 'J\'aime',
                'heart' => 'Cœur',
                'love' => 'Cœur',
                'haha' => 'Haha',
                'wow' => 'Wow',
                'sad' => 'Triste',
                'angry' => 'En colère',
                'error_empty_content' => 'Le contenu du post ne peut pas être vide',
                'error_empty_comment' => 'Le commentaire ne peut pas être vide',
                'error_empty_fields' => 'Veuillez remplir tous les champs',
                'link_copied' => 'Lien copié !',
                'error' => 'Erreur',
                'post_published' => 'Post publié',
                'post_updated' => 'Post modifié',
                'post_deleted' => 'Post supprimé',
                'comment_added' => 'Commentaire ajouté',
                'comment_updated' => 'Commentaire modifié',
                'comment_deleted' => 'Commentaire supprimé',
                'reaction_added' => 'Réaction ajoutée',
                'reaction_updated' => 'Réaction mise à jour',
                'reaction_removed' => 'Réaction supprimée',
                'login_error_empty' => 'Veuillez remplir tous les champs',
                'login_error_invalid' => 'Email ou mot de passe incorrect',
                'error_login_required' => 'Vous devez être connecté',
                'error_missing_id' => 'ID manquant',
                'error_invalid_data' => 'Données invalides',
                'error_invalid_request' => 'Demande invalide',
                'this_post' => 'cette publication',
                'this_comment' => 'ce commentaire',
                'this_item' => 'cet élément',
                'reactions_list' => 'Liste des réactions',
                'close' => 'Fermer'
            ],
            'en' => [ // Version anglaise (à compléter si besoin)
                'dashboard' => 'Dashboard',
                'search_placeholder' => 'Search a post or user...',
                'write_comment' => 'Write a comment...',
                'image' => 'Image',
                'video' => 'Video',
                'publish' => 'Publish',
                'no_posts' => 'No posts found.',
                'trends' => 'Trends',
                'trend1' => 'New aquatic center',
                'trend2' => 'Participatory planting',
                'trend3' => 'Urban bike paths',
                'announcements' => 'Municipal announcements',
                'alert1' => 'Public meeting on April 25',
                'alert2' => 'Waste collection modified',
                'ai_insights' => 'AI Insights',
                'category' => 'Category',
                'mobility' => 'Green mobility',
                'sentiment' => 'positive sentiment',
                'login_title' => 'Login',
                'cancel' => 'Cancel',
                'login_btn' => 'Login',
                'logged_as' => 'Logged in as',
                'logout' => 'Logout',
                'comment' => 'Comment',
                'share' => 'Share',
                'react' => 'React',
                'like' => 'Like',
                'heart' => 'Love',
                'haha' => 'Haha',
                'wow' => 'Wow',
                'sad' => 'Sad',
                'angry' => 'Angry',
                'listen' => 'Listen',
                'read_more' => 'Read more',
                'back_to_blog' => 'Back to blog',
                'article_not_found' => 'Article not found.',
                'edit_post_title' => 'Edit post',
                'edit_comment_title' => 'Edit comment',
                'save' => 'Save',
                'delete' => 'Delete',
                'confirm_delete_title' => 'Confirm deletion',
                'confirm_delete_post' => 'Are you sure you want to delete this post?',
                'confirm_delete_comment' => 'Are you sure you want to delete this comment?',
                'ok' => 'OK',
                'close' => 'Close',
                'reactions_list' => 'Reactions list',
                'link_copied' => 'Link copied!',
                'error_empty_content' => 'Post content cannot be empty',
                'error_empty_comment' => 'Comment cannot be empty',
                'error_empty_fields' => 'Please fill all fields',
                'login_error_empty' => 'Please fill all fields',
                'login_error_invalid' => 'Invalid email or password',
                'error_login_required' => 'You must be logged in',
                'error_missing_id' => 'Missing ID',
                'error_invalid_data' => 'Invalid data',
                'error_invalid_request' => 'Invalid request',
                'this_post' => 'this post',
                'this_comment' => 'this comment',
                'this_item' => 'this item'
            ],
            'ar' => [ // Version arabe (à compléter si besoin)
                'dashboard' => 'لوحة التحكم',
                'search_placeholder' => 'ابحث عن منشور أو مستخدم...',
                'write_comment' => 'اكتب تعليقاً...',
                'image' => 'صورة',
                'video' => 'فيديو',
                'publish' => 'نشر',
                'no_posts' => 'لا توجد منشورات.',
                'trends' => 'الاتجاهات',
                'trend1' => 'مركز مائي جديد',
                'trend2' => 'زراعة تشاركية',
                'trend3' => 'مسارات دراجات حضرية',
                'announcements' => 'إعلانات البلدية',
                'alert1' => 'اجتماع عام في 25 أبريل',
                'alert2' => 'تعديل جمع النفايات',
                'ai_insights' => 'رؤى الذكاء الاصطناعي',
                'category' => 'الفئة',
                'mobility' => 'تنقل أخضر',
                'sentiment' => 'مشاعر إيجابية',
                'login_title' => 'تسجيل الدخول',
                'cancel' => 'إلغاء',
                'login_btn' => 'تسجيل الدخول',
                'logged_as' => 'مسجل الدخول كـ',
                'logout' => 'تسجيل الخروج',
                'comment' => 'تعليق',
                'share' => 'مشاركة',
                'react' => 'تفاعل',
                'like' => 'أعجبني',
                'heart' => 'حب',
                'haha' => 'ههه',
                'wow' => 'واو',
                'sad' => 'حزين',
                'angry' => 'غاضب',
                'listen' => 'استماع',
                'read_more' => 'اقرأ أكثر',
                'back_to_blog' => 'العودة إلى المدونة',
                'article_not_found' => 'المقال غير موجود.',
                'edit_post_title' => 'تعديل المنشور',
                'edit_comment_title' => 'تعديل التعليق',
                'save' => 'حفظ',
                'delete' => 'حذف',
                'confirm_delete_title' => 'تأكيد الحذف',
                'confirm_delete_post' => 'هل أنت متأكد من حذف هذا المنشور؟',
                'confirm_delete_comment' => 'هل أنت متأكد من حذف هذا التعليق؟',
                'ok' => 'موافق',
                'close' => 'إغلاق',
                'reactions_list' => 'قائمة التفاعلات',
                'link_copied' => 'تم نسخ الرابط!',
                'error_empty_content' => 'محتويات المنشور لا يمكن أن تكون فارغة',
                'error_empty_comment' => 'التعليق لا يمكن أن يكون فارغاً',
                'error_empty_fields' => 'الرجاء ملء جميع الحقول',
                'login_error_empty' => 'الرجاء ملء جميع الحقول',
                'login_error_invalid' => 'بريد إلكتروني أو كلمة مرور غير صحيحة',
                'error_login_required' => 'يجب تسجيل الدخول',
                'error_missing_id' => 'المعرف مفقود',
                'error_invalid_data' => 'بيانات غير صالحة',
                'error_invalid_request' => 'طلب غير صالح',
                'this_post' => 'هذا المنشور',
                'this_comment' => 'هذا التعليق',
                'this_item' => 'هذا العنصر'
            ]
        ];
    }

    public function t($key) {
        return $this->translations[$this->current_lang][$key] ?? $key;
    }
    public function getCurrentLang() { return $this->current_lang; }
    public function isRtl() { return $this->current_lang === 'ar'; }

    // ========== VALIDATION USER_ID ==========
    private function getValidUserId() {
        if (!isset($_SESSION['user_id'])) return null;
        $userId = (int)$_SESSION['user_id'];
        $stmt = $this->db->prepare("SELECT id FROM utilisateurs WHERE id = ?");
        $stmt->execute([$userId]);
        if ($stmt->fetch()) return $userId;
        // Sinon admin par défaut (id=2) ou premier utilisateur
        $stmt = $this->db->prepare("SELECT id FROM utilisateurs WHERE role = 'admin' LIMIT 1");
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) {
            $_SESSION['user_id'] = $admin['id'];
            return $admin['id'];
        }
        $stmt = $this->db->prepare("SELECT id FROM utilisateurs LIMIT 1");
        $stmt->execute();
        $any = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($any) {
            $_SESSION['user_id'] = $any['id'];
            return $any['id'];
        }
        return null;
    }

    // ========== AFFICHAGE DES POSTS ==========
    public function getPosts($search = '') {
        try {
            $sql = "SELECT p.*, CONCAT(u.prenom, ' ', u.nom) as user_name, u.avatar as user_avatar,
                    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count,
                    (SELECT COUNT(*) FROM reactions WHERE post_id = p.id) as reactions_count
                    FROM posts p 
                    JOIN utilisateurs u ON p.user_id = u.id";
            if (!empty($search)) {
                $sql .= " WHERE (p.content LIKE :search OR CONCAT(u.prenom, ' ', u.nom) LIKE :search)";
            }
            $sql .= " ORDER BY p.created_at DESC";
            $stmt = $this->db->prepare($sql);
            if (!empty($search)) {
                $searchParam = '%' . $search . '%';
                $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $posts = [];
            foreach ($rows as $row) {
                $post = new Blog($this->db);
                $post->setId($row['id']);
                $post->setUserId($row['user_id']);
                $post->setContent($row['content']);
                $post->setImage($row['image']);
                $post->setVideo($row['video']);
                $post->setCreatedAt($row['created_at']);
                $comments = $this->getCommentsByPost($row['id']);
                $userReaction = null;
                $uid = $this->getValidUserId();
                if ($uid) {
                    $stmtR = $this->db->prepare("SELECT type FROM reactions WHERE post_id = ? AND user_id = ?");
                    $stmtR->execute([$row['id'], $uid]);
                    $react = $stmtR->fetch(PDO::FETCH_ASSOC);
                    if ($react) $userReaction = $react['type'];
                }
                $posts[] = [
                    'post' => $post,
                    'user_name'    => $row['user_name'] ?? 'Utilisateur',
                    'user_avatar'  => $row['user_avatar'] ?? 'https://randomuser.me/api/portraits/lego/1.jpg',
                    'comments_count' => (int)$row['comments_count'],
                    'reactions_count' => (int)$row['reactions_count'],
                    'comments'     => $comments,
                    'user_reaction' => $userReaction
                ];
            }
            return $posts;
        } catch (PDOException $e) {
            error_log("getPosts error: " . $e->getMessage());
            return [];
        }
    }

    public function searchPostsInPhp($search = '') {
        return $this->getPosts($search);
    }

    public function getPostById($id) {
        try {
            $sql = "SELECT p.*, CONCAT(u.prenom, ' ', u.nom) as user_name, u.avatar as user_avatar,
                    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count,
                    (SELECT COUNT(*) FROM reactions WHERE post_id = p.id) as reactions_count
                    FROM posts p
                    JOIN utilisateurs u ON p.user_id = u.id
                    WHERE p.id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;
            $post = new Blog($this->db);
            $post->setId($row['id']);
            $post->setUserId($row['user_id']);
            $post->setContent($row['content']);
            $post->setImage($row['image']);
            $post->setVideo($row['video']);
            $post->setCreatedAt($row['created_at']);
            $comments = $this->getCommentsByPost($row['id']);
            $userReaction = null;
            $uid = $this->getValidUserId();
            if ($uid) {
                $stmtR = $this->db->prepare("SELECT type FROM reactions WHERE post_id = ? AND user_id = ?");
                $stmtR->execute([$row['id'], $uid]);
                $react = $stmtR->fetch(PDO::FETCH_ASSOC);
                if ($react) $userReaction = $react['type'];
            }
            return [
                'post' => $post,
                'user_name' => $row['user_name'],
                'user_avatar' => $row['user_avatar'] ?? 'https://randomuser.me/api/portraits/lego/1.jpg',
                'comments_count' => (int)$row['comments_count'],
                'reactions_count' => (int)$row['reactions_count'],
                'comments' => $comments,
                'user_reaction' => $userReaction
            ];
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getCommentsByPost($post_id) {
        $sql = "SELECT c.*, CONCAT(u.prenom, ' ', u.nom) as user_name, u.avatar as user_avatar 
                FROM comments c 
                JOIN utilisateurs u ON c.user_id = u.id 
                WHERE c.post_id = :post_id 
                ORDER BY c.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':post_id' => $post_id]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($comments as &$c) {
            if (empty($c['user_avatar'])) $c['user_avatar'] = 'https://randomuser.me/api/portraits/lego/1.jpg';
        }
        return $comments;
    }

    // ===================== CRUD POSTS =====================
    public function createPost($data, $files) {
        $userId = $this->getValidUserId();
        if (!$userId) {
            $_SESSION['error'] = $this->t('error_login_required');
            $this->redirectBack();
            return;
        }
        if (empty($data['content'])) {
            $_SESSION['error'] = $this->t('error_empty_content');
            $this->redirectBack();
            return;
        }
        $image = null; $video = null;
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
            ':user_id' => $userId,
            ':content' => trim(htmlspecialchars($data['content'])),
            ':image' => $image,
            ':video' => $video
        ]);
        if ($result) {
            $_SESSION['success'] = $this->t('post_published');
        } else {
            $_SESSION['error'] = $this->t('error') . " : " . implode(", ", $stmt->errorInfo());
        }
        $this->redirectBack();
    }

    public function updatePost($data) {
        $userId = $this->getValidUserId();
        if (!$userId || empty($data['post_id']) || empty($data['content'])) {
            $_SESSION['error'] = $this->t('error_invalid_data');
            $this->redirectBack();
            return;
        }
        $sql = "UPDATE posts SET content = :content WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':content' => trim(htmlspecialchars($data['content'])),
            ':id' => (int)$data['post_id'],
            ':user_id' => $userId
        ]);
        $_SESSION[$result ? 'success' : 'error'] = $result ? $this->t('post_updated') : $this->t('error');
        $this->redirectBack();
    }

    public function deletePost($data) {
        $userId = $this->getValidUserId();
        if (!$userId || empty($data['post_id'])) {
            $_SESSION['error'] = $this->t('error_missing_id');
            $this->redirectBack();
            return;
        }
        $post_id = (int)$data['post_id'];
        $this->db->prepare("DELETE FROM comments WHERE post_id = ?")->execute([$post_id]);
        $this->db->prepare("DELETE FROM reactions WHERE post_id = ?")->execute([$post_id]);
        $stmt = $this->db->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        $result = $stmt->execute([$post_id, $userId]);
        $_SESSION[$result ? 'success' : 'error'] = $result ? $this->t('post_deleted') : $this->t('error');
        $this->redirectBack();
    }

    // ===================== CRUD COMMENTAIRES =====================
    public function createComment($data) {
        $userId = $this->getValidUserId();
        if (!$userId || empty($data['post_id']) || empty($data['content'])) {
            $_SESSION['error'] = $this->t('error_empty_comment');
            $this->redirectBack();
            return;
        }
        $sql = "INSERT INTO comments (post_id, user_id, content, created_at) VALUES (:post_id, :user_id, :content, NOW())";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':post_id' => (int)$data['post_id'],
            ':user_id' => $userId,
            ':content' => trim(htmlspecialchars($data['content']))
        ]);
        $_SESSION[$result ? 'success' : 'error'] = $result ? $this->t('comment_added') : $this->t('error');
        $this->redirectBack();
    }

    public function updateComment($data) {
        $userId = $this->getValidUserId();
        if (!$userId || empty($data['comment_id']) || empty($data['content'])) {
            $_SESSION['error'] = $this->t('error_invalid_data');
            $this->redirectBack();
            return;
        }
        $sql = "UPDATE comments SET content = :content WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':content' => trim(htmlspecialchars($data['content'])),
            ':id' => (int)$data['comment_id'],
            ':user_id' => $userId
        ]);
        $_SESSION[$result ? 'success' : 'error'] = $result ? $this->t('comment_updated') : $this->t('error');
        $this->redirectBack();
    }

    public function deleteComment($data) {
        $userId = $this->getValidUserId();
        if (!$userId || empty($data['comment_id'])) {
            $_SESSION['error'] = $this->t('error_missing_id');
            $this->redirectBack();
            return;
        }
        $sql = "DELETE FROM comments WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':id' => (int)$data['comment_id'],
            ':user_id' => $userId
        ]);
        $_SESSION[$result ? 'success' : 'error'] = $result ? $this->t('comment_deleted') : $this->t('error');
        $this->redirectBack();
    }

    // ===================== RÉACTIONS =====================
    public function reactToPost($data) {
        $userId = $this->getValidUserId();
        if (!$userId || empty($data['post_id']) || empty($data['type'])) {
            $_SESSION['error'] = $this->t('error_invalid_data');
            $this->redirectBack();
            return;
        }
        $post_id = (int)$data['post_id'];
        $type = $data['type'];
        if ($type === 'heart') $type = 'love';
        if (!in_array($type, ['like','love','haha','wow','sad','angry'])) $type = 'like';
        $stmt = $this->db->prepare("SELECT type FROM reactions WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$post_id, $userId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            if ($existing['type'] === $type) {
                $stmt = $this->db->prepare("DELETE FROM reactions WHERE post_id = ? AND user_id = ?");
                $result = $stmt->execute([$post_id, $userId]);
                $message = $result ? $this->t('reaction_removed') : $this->t('error');
            } else {
                $stmt = $this->db->prepare("UPDATE reactions SET type = ? WHERE post_id = ? AND user_id = ?");
                $result = $stmt->execute([$type, $post_id, $userId]);
                $message = $result ? $this->t('reaction_updated') : $this->t('error');
            }
        } else {
            $stmt = $this->db->prepare("INSERT INTO reactions (post_id, user_id, type) VALUES (?, ?, ?)");
            $result = $stmt->execute([$post_id, $userId, $type]);
            $message = $result ? $this->t('reaction_added') : $this->t('error');
        }
        $_SESSION[$result ? 'success' : 'error'] = $message;
        $this->redirectBack();
    }

    // ===================== MÉTHODES AJAX =====================
    public function ajaxReactToPost() {
        header('Content-Type: application/json');
        $uid = $this->getValidUserId();
        if (!$uid) { echo json_encode(['error' => 'non connecté']); exit; }
        $post_id = (int)($_POST['post_id'] ?? 0);
        $type = $_POST['type'] ?? '';
        if ($type === 'heart') $type = 'love';
        if (!$post_id || !in_array($type, ['like','love','haha','wow','sad','angry'])) {
            echo json_encode(['error' => 'données invalides']); exit;
        }
        $stmt = $this->db->prepare("SELECT type FROM reactions WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$post_id, $uid]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            if ($existing['type'] === $type) {
                $stmt = $this->db->prepare("DELETE FROM reactions WHERE post_id = ? AND user_id = ?");
                $stmt->execute([$post_id, $uid]);
            } else {
                $stmt = $this->db->prepare("UPDATE reactions SET type = ? WHERE post_id = ? AND user_id = ?");
                $stmt->execute([$type, $post_id, $uid]);
            }
        } else {
            $stmt = $this->db->prepare("INSERT INTO reactions (post_id, user_id, type) VALUES (?, ?, ?)");
            $stmt->execute([$post_id, $uid, $type]);
        }
        $totalStmt = $this->db->prepare("SELECT COUNT(*) FROM reactions WHERE post_id = ?");
        $totalStmt->execute([$post_id]);
        $total = $totalStmt->fetchColumn();
        $userStmt = $this->db->prepare("SELECT type FROM reactions WHERE post_id = ? AND user_id = ?");
        $userStmt->execute([$post_id, $uid]);
        $userReactionType = $userStmt->fetch(PDO::FETCH_ASSOC)['type'] ?? null;
        $mapEmoji = ['like'=>'👍','love'=>'❤️','haha'=>'😂','wow'=>'😮','sad'=>'😢','angry'=>'😡'];
        $currentEmoji = $mapEmoji[$userReactionType] ?? '👍';
        echo json_encode(['success'=>true, 'total'=>$total, 'user_reaction'=>$userReactionType, 'current_emoji'=>$currentEmoji]);
        exit;
    }

    public function ajaxCreateComment() {
        header('Content-Type: application/json');
        $uid = $this->getValidUserId();
        if (!$uid) { echo json_encode(['error' => 'non connecté']); exit; }
        $post_id = (int)($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        if (!$post_id || empty($content)) { echo json_encode(['error' => 'données invalides']); exit; }
        $user_name = $_SESSION['user_name'] ?? 'Utilisateur';
        $user_avatar = $_SESSION['user_avatar'] ?? 'https://randomuser.me/api/portraits/lego/1.jpg';
        $stmt = $this->db->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
        $result = $stmt->execute([$post_id, $uid, $content]);
        if (!$result) { echo json_encode(['error' => 'erreur insertion']); exit; }
        $comment_id = $this->db->lastInsertId();
        $totalStmt = $this->db->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
        $totalStmt->execute([$post_id]);
        $total = $totalStmt->fetchColumn();
        echo json_encode([
            'success' => true,
            'comment_id' => $comment_id,
            'user_name' => $user_name,
            'user_avatar' => $user_avatar,
            'content' => nl2br(htmlspecialchars($content)),
            'total_comments' => $total
        ]);
        exit;
    }

    public function ajaxDeleteComment() {
        header('Content-Type: application/json');
        $uid = $this->getValidUserId();
        if (!$uid) { echo json_encode(['error' => 'non connecté']); exit; }
        $comment_id = (int)($_POST['comment_id'] ?? 0);
        if (!$comment_id) { echo json_encode(['error' => 'ID manquant']); exit; }
        $stmt = $this->db->prepare("SELECT post_id FROM comments WHERE id = ? AND user_id = ?");
        $stmt->execute([$comment_id, $uid]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$comment) { echo json_encode(['error' => 'Commentaire non trouvé ou non autorisé']); exit; }
        $post_id = $comment['post_id'];
        $delStmt = $this->db->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $result = $delStmt->execute([$comment_id, $uid]);
        if (!$result) { echo json_encode(['error' => 'erreur suppression']); exit; }
        $totalStmt = $this->db->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
        $totalStmt->execute([$post_id]);
        $total = $totalStmt->fetchColumn();
        echo json_encode(['success'=>true, 'total_comments'=>$total]);
        exit;
    }

    public function ajaxUpdateComment() {
        header('Content-Type: application/json');
        $uid = $this->getValidUserId();
        if (!$uid) { echo json_encode(['error' => 'non connecté']); exit; }
        $comment_id = (int)($_POST['comment_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        if (!$comment_id || empty($content)) { echo json_encode(['error' => 'données invalides']); exit; }
        $stmt = $this->db->prepare("UPDATE comments SET content = ? WHERE id = ? AND user_id = ?");
        $result = $stmt->execute([$content, $comment_id, $uid]);
        if (!$result) { echo json_encode(['error' => 'erreur mise à jour']); exit; }
        echo json_encode(['success'=>true, 'content'=>nl2br(htmlspecialchars($content))]);
        exit;
    }

    public function ajaxDeletePost() {
        header('Content-Type: application/json');
        $uid = $this->getValidUserId();
        if (!$uid) { echo json_encode(['error' => 'non connecté']); exit; }
        $post_id = (int)($_POST['post_id'] ?? 0);
        if (!$post_id) { echo json_encode(['error' => 'ID manquant']); exit; }
        $check = $this->db->prepare("SELECT id FROM posts WHERE id = ? AND user_id = ?");
        $check->execute([$post_id, $uid]);
        if (!$check->fetch()) { echo json_encode(['error' => 'Non autorisé']); exit; }
        $this->db->prepare("DELETE FROM comments WHERE post_id = ?")->execute([$post_id]);
        $this->db->prepare("DELETE FROM reactions WHERE post_id = ?")->execute([$post_id]);
        $del = $this->db->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        $result = $del->execute([$post_id, $uid]);
        echo json_encode(['success'=>$result]);
        exit;
    }

    public function getReactionsList() {
        header('Content-Type: application/json');
        $post_id = (int)($_GET['post_id'] ?? 0);
        if (!$post_id) { echo json_encode(['error' => 'ID manquant']); exit; }
        $sql = "SELECT u.id, CONCAT(u.prenom, ' ', u.nom) as name, u.avatar, r.type
                FROM reactions r JOIN utilisateurs u ON r.user_id = u.id
                WHERE r.post_id = :post_id ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':post_id' => $post_id]);
        $reactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $mapEmoji = ['like'=>'👍','love'=>'❤️','haha'=>'😂','wow'=>'😮','sad'=>'😢','angry'=>'😡'];
        foreach ($reactions as &$r) {
            $r['emoji'] = $mapEmoji[$r['type']] ?? '👍';
            if (empty($r['avatar'])) $r['avatar'] = 'https://randomuser.me/api/portraits/lego/1.jpg';
        }
        echo json_encode($reactions);
        exit;
    }

    public function searchAjax() {
        header('Content-Type: application/json');
        $search = $_GET['q'] ?? '';
        $posts = $this->getPosts($search);
        $result = [];
        foreach ($posts as $p) {
            $result[] = [
                'id' => $p['post']->getId(),
                'content' => mb_substr(strip_tags($p['post']->getContent()), 0, 120),
                'author' => $p['user_name'],
                'avatar' => $p['user_avatar'],
                'created_at' => $p['post']->getCreatedAt(),
                'image' => $p['post']->getImage(),
                'video' => $p['post']->getVideo(),
                'comments_count' => $p['comments_count']
            ];
        }
        echo json_encode($result);
        exit;
    }

    // ===================== TEXT-TO-SPEECH =====================
    public function getSpeakText() {
        header('Content-Type: application/json; charset=utf-8');
        $post_id = (int)($_GET['post_id'] ?? 0);
        if (!$post_id) { echo json_encode(['error' => 'ID manquant']); exit; }
        $stmt = $this->db->prepare("SELECT content FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$post) { echo json_encode(['error' => 'Post introuvable']); exit; }
        $text = strip_tags($post['content']);
        $text = html_entity_decode($text, ENT_QUOTES|ENT_SUBSTITUTE|ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[^\p{Arabic}\p{Latin}\p{N}\p{P}\s]/u', '', $text);
        $text = preg_replace('/\s+/', ' ', trim($text));
        if (empty($text)) { echo json_encode(['error' => 'Texte vide']); exit; }
        $langMap = ['fr'=>'fr-FR','en'=>'en-US','ar'=>'ar-SA'];
        $ttsLang = $langMap[$this->current_lang] ?? 'fr-FR';
        echo json_encode(['text' => $text, 'lang' => $ttsLang, 'rate' => 0.85, 'pitch' => 1.0]);
        exit;
    }

    // ===================== THÈME, TAILLE, LANGUE =====================
    public function setTheme() {
        if (isset($_GET['theme']) && in_array($_GET['theme'], ['light','dark']))
            $_SESSION['user_theme'] = $_GET['theme'];
        $this->redirectBack();
    }
    public function setFontSize() {
        if (isset($_GET['size']) && is_numeric($_GET['size']))
            $_SESSION['font_size'] = min(130, max(80, (int)$_GET['size']));
        $this->redirectBack();
    }
    public function setLanguage() {
        $allowed = ['fr','en','ar'];
        if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed))
            $_SESSION['app_lang'] = $_GET['lang'];
        $this->redirectBack();
    }
    public function getUserTheme() { return $_SESSION['user_theme'] ?? 'light'; }
    public function getUserFontSize() { return $_SESSION['font_size'] ?? 100; }

    private function redirectBack() {
        $redirect = $_GET['redirect'] ?? '';
        if (!empty($redirect) && strpos($redirect, '://') === false && strpos($redirect, '/') === 0) {
            header("Location: $redirect");
            exit;
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (!empty($referer) && strpos($referer, $_SERVER['HTTP_HOST']) !== false) {
            header("Location: $referer");
            exit;
        }
        header("Location: /smart/smart-municipality/index.php?action=blog");
        exit;
    }

    // ===================== AUTHENTIFICATION =====================
    public function login($data) {
        if (empty($data['email']) || empty($data['password'])) {
            $_SESSION['error'] = $this->t('login_error_empty');
            $this->redirectBack();
            return;
        }
        $email = trim(htmlspecialchars($data['email']));
        $password = trim(htmlspecialchars($data['password']));
        $sql = "SELECT id, CONCAT(prenom, ' ', nom) as name, avatar, role FROM utilisateurs WHERE email = :email AND mot_de_passe = :password";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email'=>$email, ':password'=>$password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_avatar'] = $user['avatar'] ?? 'https://randomuser.me/api/portraits/lego/1.jpg';
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['success'] = "Bienvenue " . $user['name'];
            header('Location: /smart/smart-municipality/views/frontoffice.php');
            exit;
        } else {
            $_SESSION['error'] = $this->t('login_error_invalid');
            $this->redirectBack();
        }
    }

    public function logout() {
        session_destroy();
        header('Location: /smart/smart-municipality/views/frontoffice.php');
        exit;
    }

    public function confirmDelete() {
        echo "<h1>Confirmation</h1>";
        exit;
    }
}

// ========== ROUTEUR ==========
if (basename($_SERVER['SCRIPT_FILENAME']) === 'BlogController.php') {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $ctrl = new BlogController();
    switch ($action) {
        case 'createPost': $ctrl->createPost($_POST, $_FILES); break;
        case 'updatePost': $ctrl->updatePost($_POST); break;
        case 'deletePost': $ctrl->deletePost($_POST); break;
        case 'createComment': $ctrl->createComment($_POST); break;
        case 'updateComment': $ctrl->updateComment($_POST); break;
        case 'deleteComment': $ctrl->deleteComment($_POST); break;
        case 'reactToPost': $ctrl->reactToPost($_POST); break;
        case 'ajaxReactToPost': $ctrl->ajaxReactToPost(); break;
        case 'ajaxCreateComment': $ctrl->ajaxCreateComment(); break;
        case 'ajaxDeleteComment': $ctrl->ajaxDeleteComment(); break;
        case 'ajaxUpdateComment': $ctrl->ajaxUpdateComment(); break;
        case 'ajaxDeletePost': $ctrl->ajaxDeletePost(); break;
        case 'getReactionsList': $ctrl->getReactionsList(); break;
        case 'searchAjax': $ctrl->searchAjax(); break;
        case 'getSpeakText': $ctrl->getSpeakText(); break;
        case 'setTheme': $ctrl->setTheme(); break;
        case 'setFontSize': $ctrl->setFontSize(); break;
        case 'setLanguage': $ctrl->setLanguage(); break;
        case 'login': $ctrl->login($_POST); break;
        case 'logout': $ctrl->logout(); break;
        default: header('Location: /smart/smart-municipality/index.php?action=blog'); exit;
    }
}
?>