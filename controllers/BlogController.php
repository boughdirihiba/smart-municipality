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
    private $current_lang = 'fr';
    private $translations = [];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->blogModel = new Blog($this->db);
        $this->userModel = new User($this->db);
        $this->initLanguage();
    }

    private function initLanguage() {
        $allowed = ['fr', 'en', 'ar'];
        $default = 'fr';
        if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed)) {
            $_SESSION['app_lang'] = $_GET['lang'];
            $this->current_lang = $_GET['lang'];
            $redirect = strtok($_SERVER['REQUEST_URI'], '?');
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            if (!empty($search)) {
                $redirect .= '?search=' . urlencode($search);
            }
            header("Location: $redirect");
            exit;
        } elseif (isset($_SESSION['app_lang']) && in_array($_SESSION['app_lang'], $allowed)) {
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
                'profile' => 'Profil', 'events' => 'Événements', 'map' => 'Carte', 'blog' => 'Blog',
                'services' => 'Services', 'appointments' => 'RDV', 'dashboard' => 'Tableau de bord',
                'image' => 'Image', 'video' => 'Vidéo', 'publish' => 'Publier',
                'login_to_post' => 'Connectez-vous pour publier', 'login_to_comment' => 'Connectez-vous pour commenter',
                'comment' => 'Commenter', 'share' => 'Partager', 'trends' => 'Tendances',
                'trend1' => 'Nouveau centre aquatique', 'trend2' => 'Plantation participative', 'trend3' => 'Véloroutes urbaines',
                'announcements' => 'Annonces municipales', 'alert1' => 'Réunion publique le 25 avril', 'alert2' => 'Collecte des déchets modifiée',
                'ai_insights' => 'AI Insights', 'category' => 'Catégorie', 'mobility' => 'Mobilité verte', 'sentiment' => 'sentiment positif',
                'login_title' => 'Connexion', 'cancel' => 'Annuler', 'login_btn' => 'Se connecter', 'logged_as' => 'Connecté en tant que',
                'logout' => 'Déconnexion', 'edit_post_title' => 'Modifier la publication', 'edit_comment_title' => 'Modifier le commentaire',
                'save' => 'Enregistrer', 'confirm_delete_title' => 'Confirmation de suppression',
                'confirm_delete_post' => 'Êtes-vous sûr de vouloir supprimer cette publication ?<br>Cette action est irréversible.',
                'confirm_delete_comment' => 'Êtes-vous sûr de vouloir supprimer ce commentaire ?<br>Cette action est irréversible.',
                'delete' => 'Supprimer', 'ok' => 'OK', 'no_posts' => 'Aucun post trouvé.',
                'search_placeholder' => 'Rechercher un post ou un utilisateur...', 'write_comment' => 'Écrire un commentaire...',
                'react' => 'Réagir', 'like' => 'J\'aime', 'heart' => 'Cœur', 'haha' => 'Haha', 'wow' => 'Wow', 'sad' => 'Triste', 'angry' => 'En colère',
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
            'en' => [
                'profile' => 'Profile', 'events' => 'Events', 'map' => 'Map', 'blog' => 'Blog',
                'services' => 'Services', 'appointments' => 'Appointments', 'dashboard' => 'Dashboard',
                'image' => 'Image', 'video' => 'Video', 'publish' => 'Publish',
                'login_to_post' => 'Login to post', 'login_to_comment' => 'Login to comment',
                'comment' => 'Comment', 'share' => 'Share', 'trends' => 'Trends',
                'trend1' => 'New aquatic center', 'trend2' => 'Participatory planting', 'trend3' => 'Urban bike paths',
                'announcements' => 'Municipal announcements', 'alert1' => 'Public meeting on April 25', 'alert2' => 'Waste collection modified',
                'ai_insights' => 'AI Insights', 'category' => 'Category', 'mobility' => 'Green mobility', 'sentiment' => 'positive sentiment',
                'login_title' => 'Login', 'cancel' => 'Cancel', 'login_btn' => 'Login', 'logged_as' => 'Logged in as',
                'logout' => 'Logout', 'edit_post_title' => 'Edit post', 'edit_comment_title' => 'Edit comment',
                'save' => 'Save', 'confirm_delete_title' => 'Confirm deletion',
                'confirm_delete_post' => 'Are you sure you want to delete this post?<br>This action is irreversible.',
                'confirm_delete_comment' => 'Are you sure you want to delete this comment?<br>This action is irreversible.',
                'delete' => 'Delete', 'ok' => 'OK', 'no_posts' => 'No posts found.',
                'search_placeholder' => 'Search a post or user...', 'write_comment' => 'Write a comment...',
                'react' => 'React', 'like' => 'Like', 'heart' => 'Love', 'haha' => 'Haha', 'wow' => 'Wow', 'sad' => 'Sad', 'angry' => 'Angry',
                'error_empty_content' => 'Post content cannot be empty',
                'error_empty_comment' => 'Comment cannot be empty',
                'error_empty_fields' => 'Please fill all fields',
                'link_copied' => 'Link copied!',
                'error' => 'Error',
                'post_published' => 'Post published',
                'post_updated' => 'Post updated',
                'post_deleted' => 'Post deleted',
                'comment_added' => 'Comment added',
                'comment_updated' => 'Comment updated',
                'comment_deleted' => 'Comment deleted',
                'reaction_added' => 'Reaction added',
                'reaction_updated' => 'Reaction updated',
                'reaction_removed' => 'Reaction removed',
                'login_error_empty' => 'Please fill all fields',
                'login_error_invalid' => 'Invalid email or password',
                'error_login_required' => 'You must be logged in',
                'error_missing_id' => 'Missing ID',
                'error_invalid_data' => 'Invalid data',
                'error_invalid_request' => 'Invalid request',
                'this_post' => 'this post',
                'this_comment' => 'this comment',
                'this_item' => 'this item',
                'reactions_list' => 'Reactions list',
                'close' => 'Close'
            ],
            'ar' => [
                'profile' => 'الملف الشخصي',
                'events' => 'الفعاليات',
                'map' => 'الخريطة',
                'blog' => 'المدونة',
                'services' => 'الخدمات',
                'appointments' => 'المواعيد',
                'dashboard' => 'لوحة التحكم',
                'image' => 'صورة',
                'video' => 'فيديو',
                'publish' => 'نشر',
                'login_to_post' => 'سجل الدخول للنشر',
                'login_to_comment' => 'سجل الدخول للتعليق',
                'comment' => 'تعليق',
                'share' => 'مشاركة',
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
                'edit_post_title' => 'تعديل المنشور',
                'edit_comment_title' => 'تعديل التعليق',
                'save' => 'حفظ',
                'confirm_delete_title' => 'تأكيد الحذف',
                'confirm_delete_post' => 'هل أنت متأكد من حذف هذا المنشور؟<br>هذا الإجراء لا رجعة فيه.',
                'confirm_delete_comment' => 'هل أنت متأكد من حذف هذا التعليق؟<br>هذا الإجراء لا رجعة فيه.',
                'delete' => 'حذف',
                'ok' => 'موافق',
                'no_posts' => 'لا توجد منشورات.',
                'search_placeholder' => 'ابحث عن منشور أو مستخدم...',
                'write_comment' => 'اكتب تعليقاً...',
                'react' => 'تفاعل',
                'like' => 'أعجبني',
                'heart' => 'حب',
                'haha' => 'ههه',
                'wow' => 'واو',
                'sad' => 'حزين',
                'angry' => 'غاضب',
                'error_empty_content' => 'محتويات المنشور لا يمكن أن تكون فارغة',
                'error_empty_comment' => 'التعليق لا يمكن أن يكون فارغاً',
                'error_empty_fields' => 'الرجاء ملء جميع الحقول',
                'link_copied' => 'تم نسخ الرابط!',
                'error' => 'خطأ',
                'post_published' => 'تم نشر المنشور',
                'post_updated' => 'تم تعديل المنشور',
                'post_deleted' => 'تم حذف المنشور',
                'comment_added' => 'تم إضافة التعليق',
                'comment_updated' => 'تم تعديل التعليق',
                'comment_deleted' => 'تم حذف التعليق',
                'reaction_added' => 'تم إضافة التفاعل',
                'reaction_updated' => 'تم تحديث التفاعل',
                'reaction_removed' => 'تم إزالة التفاعل',
                'login_error_empty' => 'الرجاء ملء جميع الحقول',
                'login_error_invalid' => 'بريد إلكتروني أو كلمة مرور غير صحيحة',
                'error_login_required' => 'يجب تسجيل الدخول',
                'error_missing_id' => 'المعرف مفقود',
                'error_invalid_data' => 'بيانات غير صالحة',
                'error_invalid_request' => 'طلب غير صالح',
                'this_post' => 'هذا المنشور',
                'this_comment' => 'هذا التعليق',
                'this_item' => 'هذا العنصر',
                'reactions_list' => 'قائمة التفاعلات',
                'close' => 'إغلاق',
                'total_posts' => 'إجمالي المنشورات',
                'total_users' => 'إجمالي المستخدمين',
                'total_comments' => 'إجمالي التعليقات',
                'total_reactions' => 'إجمالي التفاعلات',
                'posts_by_day' => 'المنشورات حسب اليوم',
                'media_distribution' => 'توزيع الوسائط',
                'activity_30d' => 'النشاط خلال 30 يوماً',
                'with_image' => 'مع صورة',
                'with_video' => 'مع فيديو',
                'text_only' => 'نص فقط',
                'create_post' => 'إنشاء منشور',
                'add_comment' => 'إضافة تعليق',
                'all_posts' => 'جميع المنشورات',
                'recent_comments' => 'أحدث التعليقات',
                'author' => 'الكاتب',
                'post' => 'المنشور',
                'date' => 'التاريخ',
                'actions' => 'إجراءات',
                'edit' => 'تعديل',
                'listen' => 'استماع',
                'items' => 'عناصر',
                'choose_post' => 'اختر منشوراً',
                'current_media' => 'الوسائط الحالية',
                'current_image' => 'الصورة الحالية',
                'current_video' => 'الفيديو الحالي',
                'no_image' => 'لا توجد صورة',
                'no_video' => 'لا يوجد فيديو',
                'refresh' => 'تحديث',
                'error_image_video_both' => 'لا يمكنك إضافة صورة وفيديو معاً',
                'error_select_post' => 'الرجاء اختيار منشور',
            ]
        ];
    }

    public function t($key) {
        return $this->translations[$this->current_lang][$key] ?? $key;
    }

    public function getCurrentLang() {
        return $this->current_lang;
    }

    public function isRtl() {
        return $this->current_lang === 'ar';
    }

    // ========== RÉCUPÉRATION DES POSTS ==========
    public function getPosts($search = '') {
        try {
            $sql = "SELECT p.*, CONCAT(u.prenom, ' ', u.nom) as user_name, u.avatar as user_avatar,
                    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count,
                    (SELECT COUNT(*) FROM reactions WHERE post_id = p.id) as reactions_count
                    FROM posts p 
                    JOIN utilisateurs u ON p.user_id = u.id";
            if (!empty($search)) {
                $sql .= " WHERE (p.content LIKE :search OR u.prenom LIKE :search OR u.nom LIKE :search)";
            }
            $sql .= " ORDER BY p.created_at DESC";
            $stmt = $this->db->prepare($sql);
            if (!empty($search)) {
                $searchParam = '%' . $search . '%';
                $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
            }
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
                $userReaction = null;
                if (isset($_SESSION['user_id'])) {
                    $sql_reaction = "SELECT type FROM reactions WHERE post_id = :post_id AND user_id = :user_id";
                    $stmt_reaction = $this->db->prepare($sql_reaction);
                    $stmt_reaction->execute([':post_id' => $data['id'], ':user_id' => $_SESSION['user_id']]);
                    $reactionData = $stmt_reaction->fetch(PDO::FETCH_ASSOC);
                    if ($reactionData) $userReaction = $reactionData['type'];
                }
                $posts[] = [
                    'post' => $post,
                    'user_name' => $data['user_name'] ?? 'Utilisateur',
                    'user_avatar' => !empty($data['user_avatar']) ? $data['user_avatar'] : 'https://randomuser.me/api/portraits/lego/1.jpg',
                    'comments_count' => $data['comments_count'] ?? 0,
                    'reactions_count' => $data['reactions_count'] ?? 0,
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
        if (empty($search)) return $allPosts;
        $filtered = [];
        $searchLower = mb_strtolower($search);
        foreach ($allPosts as $postData) {
            $content = mb_strtolower($postData['post']->getContent());
            $author  = mb_strtolower($postData['user_name']);
            if (strpos($content, $searchLower) !== false || strpos($author, $searchLower) !== false) {
                $filtered[] = $postData;
            }
        }
        return $filtered;
    }

    // ========== PAGINATION ==========
    public function getPostsPaginated($search = '', $limit = 10, $offset = 0) {
        try {
            $sql = "SELECT p.*, CONCAT(u.prenom, ' ', u.nom) as user_name, u.avatar as user_avatar,
                    COUNT(DISTINCT c.id) as comments_count,
                    COUNT(DISTINCT r.id) as reactions_count
                    FROM posts p
                    JOIN utilisateurs u ON p.user_id = u.id
                    LEFT JOIN comments c ON c.post_id = p.id
                    LEFT JOIN reactions r ON r.post_id = p.id";
            if (!empty($search)) {
                $sql .= " WHERE (p.content LIKE :search OR u.prenom LIKE :search OR u.nom LIKE :search)";
            }
            $sql .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            if (!empty($search)) {
                $searchParam = '%' . $search . '%';
                $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
            }
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $postsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $postIds = array_column($postsData, 'id');
            $commentsByPost = [];
            if (!empty($postIds)) {
                $placeholders = implode(',', array_fill(0, count($postIds), '?'));
                $stmtComments = $this->db->prepare("SELECT c.*, CONCAT(u.prenom, ' ', u.nom) as user_name, u.avatar as user_avatar 
                                                     FROM comments c 
                                                     JOIN utilisateurs u ON c.user_id = u.id 
                                                     WHERE c.post_id IN ($placeholders) 
                                                     ORDER BY c.created_at ASC");
                $stmtComments->execute($postIds);
                while ($row = $stmtComments->fetch(PDO::FETCH_ASSOC)) {
                    $commentsByPost[$row['post_id']][] = $row;
                }
            }

            $userReactions = [];
            if (isset($_SESSION['user_id']) && !empty($postIds)) {
                $placeholders = implode(',', array_fill(0, count($postIds), '?'));
                $stmtReactions = $this->db->prepare("SELECT post_id, type FROM reactions WHERE post_id IN ($placeholders) AND user_id = ?");
                $params = array_merge($postIds, [$_SESSION['user_id']]);
                $stmtReactions->execute($params);
                while ($row = $stmtReactions->fetch(PDO::FETCH_ASSOC)) {
                    $userReactions[$row['post_id']] = $row['type'];
                }
            }

            $posts = [];
            foreach ($postsData as $data) {
                $post = new Blog($this->db);
                $post->setId($data['id']);
                $post->setUserId($data['user_id']);
                $post->setContent($data['content']);
                $post->setImage($data['image']);
                $post->setVideo($data['video']);
                $post->setCreatedAt($data['created_at']);

                $posts[] = [
                    'post' => $post,
                    'user_name' => $data['user_name'] ?? 'Utilisateur',
                    'user_avatar' => !empty($data['user_avatar']) ? $data['user_avatar'] : 'https://randomuser.me/api/portraits/lego/1.jpg',
                    'comments_count' => $data['comments_count'] ?? 0,
                    'reactions_count' => $data['reactions_count'] ?? 0,
                    'comments' => $commentsByPost[$data['id']] ?? [],
                    'user_reaction' => $userReactions[$data['id']] ?? null
                ];
            }
            return $posts;
        } catch (PDOException $e) {
            error_log("Erreur getPostsPaginated: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalPostsCount($search = '') {
        try {
            $sql = "SELECT COUNT(DISTINCT p.id) FROM posts p
                    JOIN utilisateurs u ON p.user_id = u.id";
            if (!empty($search)) {
                $sql .= " WHERE (p.content LIKE :search OR u.prenom LIKE :search OR u.nom LIKE :search)";
            }
            $stmt = $this->db->prepare($sql);
            if (!empty($search)) {
                $searchParam = '%' . $search . '%';
                $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
            }
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erreur getTotalPostsCount: " . $e->getMessage());
            return 0;
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
        foreach ($comments as &$comment) {
            if (empty($comment['user_avatar'])) {
                $comment['user_avatar'] = 'https://randomuser.me/api/portraits/lego/1.jpg';
            }
        }
        return $comments;
    }
    
    // ========== LISTE DES RÉACTIONS (AJAX) ==========
    public function getReactionsList() {
        header('Content-Type: application/json');
        if (!isset($_GET['post_id'])) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $post_id = (int)$_GET['post_id'];
        $sql = "SELECT u.id, CONCAT(u.prenom, ' ', u.nom) AS name, u.avatar, r.type
            FROM reactions r
            JOIN utilisateurs u ON r.user_id = u.id
                WHERE r.post_id = :post_id
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':post_id' => $post_id]);
        $reactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $mapEmoji = [
            'like' => '👍',
            'heart' => '❤️',
            'haha' => '😂',
            'wow' => '😮',
            'sad' => '😢',
            'angry' => '😡'
        ];
        foreach ($reactions as &$r) {
            $r['emoji'] = $mapEmoji[$r['type']] ?? '👍';
            if (empty($r['avatar'])) {
                $r['avatar'] = 'https://randomuser.me/api/portraits/lego/1.jpg';
            }
        }
        echo json_encode($reactions);
        exit;
    }
    
    // ========== MÉTHODES AJAX ==========
    public function ajaxReactToPost() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'non connecté']);
            exit;
        }
        $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
        $type = isset($_POST['type']) ? $_POST['type'] : '';
        if (!$post_id || !in_array($type, ['like', 'heart', 'haha', 'wow', 'sad', 'angry'])) {
            echo json_encode(['error' => 'données invalides']);
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $db = $this->db;
        
        $stmt = $db->prepare("SELECT type FROM reactions WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            if ($existing['type'] === $type) {
                $stmt = $db->prepare("DELETE FROM reactions WHERE post_id = ? AND user_id = ?");
                $stmt->execute([$post_id, $user_id]);
            } else {
                $stmt = $db->prepare("UPDATE reactions SET type = ? WHERE post_id = ? AND user_id = ?");
                $stmt->execute([$type, $post_id, $user_id]);
            }
        } else {
            $stmt = $db->prepare("INSERT INTO reactions (post_id, user_id, type) VALUES (?, ?, ?)");
            $stmt->execute([$post_id, $user_id, $type]);
        }
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM reactions WHERE post_id = ?");
        $stmt->execute([$post_id]);
        $total = $stmt->fetchColumn();
        
        $stmt = $db->prepare("SELECT type FROM reactions WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);
        $userReaction = $stmt->fetch(PDO::FETCH_ASSOC);
        $userReactionType = $userReaction ? $userReaction['type'] : null;
        
        $mapEmoji = ['like'=>'👍', 'heart'=>'❤️', 'haha'=>'😂', 'wow'=>'😮', 'sad'=>'😢', 'angry'=>'😡'];
        $mapTexte = [
            'like' => $this->t('like'),
            'heart' => $this->t('heart'),
            'haha' => $this->t('haha'),
            'wow' => $this->t('wow'),
            'sad' => $this->t('sad'),
            'angry' => $this->t('angry')
        ];
        $currentEmoji = $userReactionType ? $mapEmoji[$userReactionType] : '👍';
        $currentText = $userReactionType ? $mapTexte[$userReactionType] : $this->t('react');
        
        echo json_encode([
            'success' => true,
            'total' => $total,
            'user_reaction' => $userReactionType,
            'current_emoji' => $currentEmoji,
            'current_text' => $currentText
        ]);
        exit;
    }
    
    public function ajaxCreateComment() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'non connecté']);
            exit;
        }
        $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        if (!$post_id || empty($content)) {
            echo json_encode(['error' => 'données invalides']);
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'];
        $user_avatar = $_SESSION['user_avatar'] ?? 'https://randomuser.me/api/portraits/lego/1.jpg';
        
        $db = $this->db;
        $stmt = $db->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
        $result = $stmt->execute([$post_id, $user_id, $content]);
        if (!$result) {
            echo json_encode(['error' => 'erreur insertion']);
            exit;
        }
        $comment_id = $db->lastInsertId();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
        $stmt->execute([$post_id]);
        $total = $stmt->fetchColumn();
        
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
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'non connecté']);
            exit;
        }
        $comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
        if (!$comment_id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $db = $this->db;
        
        $stmt = $db->prepare("SELECT post_id FROM comments WHERE id = ? AND user_id = ?");
        $stmt->execute([$comment_id, $user_id]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$comment) {
            echo json_encode(['error' => 'Commentaire non trouvé ou non autorisé']);
            exit;
        }
        $post_id = $comment['post_id'];
        
        $stmt = $db->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $result = $stmt->execute([$comment_id, $user_id]);
        if (!$result) {
            echo json_encode(['error' => 'erreur suppression']);
            exit;
        }
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
        $stmt->execute([$post_id]);
        $total = $stmt->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'total_comments' => $total
        ]);
        exit;
    }
    
    public function ajaxUpdateComment() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'non connecté']);
            exit;
        }
        $comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        if (!$comment_id || empty($content)) {
            echo json_encode(['error' => 'données invalides']);
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $db = $this->db;
        
        $stmt = $db->prepare("UPDATE comments SET content = ? WHERE id = ? AND user_id = ?");
        $result = $stmt->execute([$content, $comment_id, $user_id]);
        if (!$result) {
            echo json_encode(['error' => 'erreur mise à jour']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'content' => nl2br(htmlspecialchars($content))
        ]);
        exit;
    }
    
    public function ajaxDeletePost() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'non connecté']);
            exit;
        }
        $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
        if (!$post_id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $db = $this->db;
        
        $stmt = $db->prepare("SELECT id FROM posts WHERE id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Non autorisé']);
            exit;
        }
        
        $db->prepare("DELETE FROM comments WHERE post_id = ?")->execute([$post_id]);
        $db->prepare("DELETE FROM reactions WHERE post_id = ?")->execute([$post_id]);
        $stmt = $db->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        $result = $stmt->execute([$post_id, $user_id]);
        
        echo json_encode(['success' => $result]);
        exit;
    }

    // ========== TEXT-TO-SPEECH ==========
    public function getSpeakText() {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_GET['post_id'])) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $post_id = (int)$_GET['post_id'];
        $sql = "SELECT content FROM posts WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $post_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$post) {
            echo json_encode(['error' => 'Post introuvable']);
            exit;
        }
        
        $text = strip_tags($post['content']);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[^\p{Arabic}\p{Latin}\p{N}\p{P}\s]/u', '', $text);
        $text = preg_replace('/\s+/', ' ', trim($text));
        
        if (empty($text)) {
            echo json_encode(['error' => 'Texte vide après nettoyage']);
            exit;
        }
        
        $langMap = ['fr' => 'fr-FR', 'en' => 'en-US', 'ar' => 'ar-SA'];
        $ttsLang = $langMap[$this->current_lang] ?? 'fr-FR';
        
        echo json_encode([
            'text' => $text,
            'lang' => $ttsLang,
            'rate' => 0.85,
            'pitch' => 1.0
        ]);
        exit;
    }
    
    // ========== GESTION THÈME ET TAILLE ==========
    public function setTheme() {
        if (isset($_GET['theme']) && in_array($_GET['theme'], ['light', 'dark'])) {
            $_SESSION['user_theme'] = $_GET['theme'];
        }
        $this->redirectBack();
    }

    public function setFontSize() {
        if (isset($_GET['size']) && is_numeric($_GET['size'])) {
            $size = min(130, max(80, (int)$_GET['size']));
            $_SESSION['font_size'] = $size;
        }
        $this->redirectBack();
    }

    public function getUserTheme() {
        return $_SESSION['user_theme'] ?? 'light';
    }

    public function getUserFontSize() {
        return $_SESSION['font_size'] ?? 100;
    }

    private function redirectBack() {
        $fallback = BASE_URL . '/index.php?action=blog';
        $url = $_SERVER['HTTP_REFERER'] ?? $fallback;
        header('Location: ' . $url);
        exit();
    }

    // ========== AUTHENTIFICATION ET CRUD (non AJAX) ==========
    public function login($data) {
        if (empty($data['email']) || empty($data['password'])) {
            $_SESSION['error'] = "Veuillez remplir tous les champs";
            $this->redirectBack();
            return;
        }
        $email = trim(htmlspecialchars($data['email']));
        $password = trim(htmlspecialchars($data['password']));
        $sql = "SELECT id, nom, prenom, avatar, role, mot_de_passe FROM utilisateurs WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && isset($user['mot_de_passe']) && password_verify($password, (string)$user['mot_de_passe'])) {
            $displayName = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_name'] = $displayName !== '' ? $displayName : 'Utilisateur';
            $_SESSION['user_avatar'] = !empty($user['avatar']) ? $user['avatar'] : 'https://randomuser.me/api/portraits/lego/1.jpg';
            $_SESSION['user_role'] = $user['role'] ?? 'citoyen';
            $_SESSION['success'] = "Bienvenue " . $_SESSION['user_name'] . " !";
            header('Location: ' . BASE_URL . '/index.php?action=blog');
            exit();
        }

        $_SESSION['error'] = "Email ou mot de passe incorrect";
        $this->redirectBack();
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php?action=blog');
        exit();
    }

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
        $stmt = $this->db->prepare("SELECT type FROM reactions WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            if ($existing['type'] === $type) {
                $stmt = $this->db->prepare("DELETE FROM reactions WHERE post_id = ? AND user_id = ?");
                $result = $stmt->execute([$post_id, $user_id]);
                $message = $result ? "Réaction supprimée" : "Erreur lors de la suppression";
            } else {
                $stmt = $this->db->prepare("UPDATE reactions SET type = ? WHERE post_id = ? AND user_id = ?");
                $result = $stmt->execute([$type, $post_id, $user_id]);
                $message = $result ? "Réaction mise à jour" : "Erreur";
            }
        } else {
            $stmt = $this->db->prepare("INSERT INTO reactions (post_id, user_id, type) VALUES (?, ?, ?)");
            $result = $stmt->execute([$post_id, $user_id, $type]);
            $message = $result ? "Réaction ajoutée" : "Erreur";
        }
        $_SESSION[$result ? 'success' : 'error'] = $message;
        $this->redirectBack();
    }

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

        $messages = ['post' => 'cette publication', 'comment' => 'ce commentaire'];
        $item = $messages[$type] ?? 'cet élément';

        echo <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation - Smart Municipality</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#f0f2f5;min-height:100vh;display:flex;justify-content:center;align-items:center}
        .confirm-card{background:white;border-radius:1rem;padding:2rem;max-width:450px;width:90%;box-shadow:0 10px 25px rgba(0,0,0,0.1);text-align:center}
        .confirm-icon{font-size:3rem;color:#dc3545;margin-bottom:1rem}
        h3{color:#1e2a32;margin-bottom:0.5rem}
        p{color:#475569;margin-bottom:1.5rem}
        .buttons{display:flex;gap:1rem;justify-content:center}
        .btn{padding:0.6rem 1.5rem;border-radius:0.6rem;text-decoration:none;font-weight:600;transition:0.3s;cursor:pointer;border:none;font-size:1rem}
        .btn-danger{background:#dc3545;color:white}
        .btn-danger:hover{background:#bb2d3b;transform:translateY(-2px)}
        .btn-secondary{background:#e2e8f0;color:#475569}
        .btn-secondary:hover{background:#cbd5e1}
    </style>
</head>
<body>
    <div class="confirm-card">
        <div class="confirm-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <h3>Confirmation de suppression</h3>
        <p>Êtes-vous sûr de vouloir supprimer {$item} ?<br>Cette action est irréversible.</p>
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
HTML;
        exit();
    }
    
    public function searchAjax() {
        header('Content-Type: application/json');
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        $posts = $this->searchPostsInPhp($search);
        $result = [];
        foreach ($posts as $p) {
            $result[] = [
                'id'          => $p['post']->getId(),
                'content'     => mb_substr(strip_tags($p['post']->getContent()), 0, 120),
                'author'      => $p['user_name'],
                'avatar'      => $p['user_avatar'],
                'created_at'  => $p['post']->getCreatedAt(),
                'image'       => $p['post']->getImage(),
                'video'       => $p['post']->getVideo(),
                'comments_count' => $p['comments_count']
            ];
        }
        echo json_encode($result);
        exit;
    }
}

// ========== ROUTEUR ==========
if (basename($_SERVER['SCRIPT_FILENAME']) === 'BlogController.php') {
    $controller = new BlogController();
    $action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

    switch($action) {
        case 'login': $controller->login($_POST); break;
        case 'logout': $controller->logout(); break;
        case 'createPost': $controller->createPost($_POST, $_FILES); break;
        case 'updatePost': $controller->updatePost($_POST); break;
        case 'deletePost': $controller->deletePost($_POST); break;
        case 'createComment': $controller->createComment($_POST); break;
        case 'updateComment': $controller->updateComment($_POST); break;
        case 'deleteComment': $controller->deleteComment($_POST); break;
        case 'reactToPost': $controller->reactToPost($_POST); break;
        case 'confirmDelete': $controller->confirmDelete(); break;
        case 'getSpeakText': $controller->getSpeakText(); break;
        case 'setTheme': $controller->setTheme(); break;
        case 'setFontSize': $controller->setFontSize(); break;
        case 'searchAjax': $controller->searchAjax(); break;
        case 'getReactionsList': $controller->getReactionsList(); break;
        case 'ajaxReactToPost': $controller->ajaxReactToPost(); break;
        case 'ajaxCreateComment': $controller->ajaxCreateComment(); break;
        case 'ajaxDeleteComment': $controller->ajaxDeleteComment(); break;
        case 'ajaxUpdateComment': $controller->ajaxUpdateComment(); break;
        case 'ajaxDeletePost': $controller->ajaxDeletePost(); break;
    }
}
?>