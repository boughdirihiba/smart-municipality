<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/../controllers/BlogController.php';

$controller = new BlogController();

// RECHERCHE - Récupérer le terme de recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$posts = $controller->getPosts($search);

$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality | Blog Citoyen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; color: #1e2a32; }
        
        .top-navbar {
            background: linear-gradient(135deg, #2FA084 0%, #0f3b2c 100%);
            color: white;
            padding: 0.8rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo-area { display: flex; align-items: center; gap: 0.8rem; }
        .logo-icon { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; }
        .logo-text .smart { font-size: 1.3rem; font-weight: 800; color: white; }
        .logo-text .municipality { font-size: 0.6rem; color: #c8e6d9; letter-spacing: 1px; }
        
        .nav-links { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }
        .nav-link { 
            color: #e6f4ea; 
            text-decoration: none; 
            display: flex; 
            align-items: center; 
            gap: 0.5rem; 
            padding: 0.5rem 1rem; 
            border-radius: 0.5rem; 
            transition: all 0.3s; 
            font-size: 0.9rem;
        }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.15); transform: translateY(-2px); }
        
        .user-info { display: flex; align-items: center; gap: 0.8rem; background: rgba(255,255,255,0.1); padding: 0.3rem 1rem 0.3rem 0.5rem; border-radius: 2rem; cursor: pointer; transition: all 0.3s; }
        .user-info:hover { background: rgba(255,255,255,0.2); }
        .avatar-sm { width: 35px; height: 35px; border-radius: 50%; overflow: hidden; }
        .avatar-sm img, .avatar-md img { width: 100%; height: 100%; object-fit: cover; }
        .avatar-md { width: 48px; height: 48px; border-radius: 50%; overflow: hidden; }
        
        .main-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            display: flex;
            gap: 2rem;
        }
        
        .content-left { flex: 2; }
        .sidebar-right { flex: 1; }
        
        /* BARRE DE RECHERCHE */
        .search-bar {
            background: white;
            border-radius: 2rem;
            padding: 0.6rem 1.2rem;
            display: flex;
            gap: 0.8rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            align-items: center;
        }
        .search-bar i { color: #2FA084; }
        .search-bar form { flex: 1; display: flex; gap: 0.5rem; }
        .search-bar input { 
            flex: 1; 
            border: none; 
            outline: none; 
            font-size: 0.95rem;
            background: transparent;
        }
        .search-bar button {
            background: none;
            border: none;
            color: #2FA084;
            cursor: pointer;
        }
        .search-clear {
            color: #999;
            text-decoration: none;
            font-size: 1.2rem;
        }
        .search-clear:hover { color: #dc3545; }
        
        /* CARTE DE CRÉATION */
        .create-card {
            background: white;
            border-radius: 1rem;
            padding: 1.2rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .post-input-area { display: flex; gap: 1rem; margin-bottom: 1rem; }
        .post-textarea {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 0.8rem;
            padding: 0.8rem 1.2rem;
            font-family: inherit;
            resize: none;
            font-size: 0.95rem;
            outline: none;
        }
        .post-textarea:focus { border-color: #2FA084; box-shadow: 0 0 0 3px rgba(47,160,132,0.1); }
        
        /* APERÇU DES MÉDIAS */
        .media-preview-area {
            margin: 0.8rem 0 0.8rem 3rem;
            max-width: 300px;
            display: none;
            position: relative;
        }
        .media-preview-area img, .media-preview-area video {
            width: 100%;
            border-radius: 0.8rem;
            max-height: 200px;
            object-fit: cover;
        }
        .remove-media-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0,0,0,0.6);
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            cursor: pointer;
        }
        
        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.8rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .media-buttons { display: flex; gap: 0.8rem; }
        
        .btn-outline-media {
            background: #f1f5f9;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.8rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
        }
        .btn-outline-media:hover { background: #e2e8f0; transform: translateY(-2px); }
        .image-icon { color: #8e24aa; }
        .video-icon { color: #fb8c00; }
        
        .btn-publish {
            background: linear-gradient(135deg, #2FA084, #0f3b2c);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-publish:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(47,160,132,0.3); }
        
        .posts-feed { display: flex; flex-direction: column; gap: 1.5rem; }
        
        .post-card {
            background: white;
            border-radius: 1rem;
            padding: 1.2rem 1.5rem;
            position: relative;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        .post-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        
        .post-header { display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1rem; }
        .post-user-name { font-weight: 700; cursor: pointer; }
        .post-user-name:hover { color: #2FA084; }
        .post-time { font-size: 0.7rem; color: #6c757d; }
        .post-text { margin: 0.8rem 0; line-height: 1.5; }
        .post-media { max-width: 100%; border-radius: 0.8rem; margin: 0.8rem 0; max-height: 350px; object-fit: cover; }
        
        .post-actions-buttons {
            position: absolute;
            top: 1.2rem;
            right: 1.5rem;
            display: flex;
            gap: 0.5rem;
        }
        
        .delete-post-btn, .edit-post-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.3rem;
            border-radius: 0.5rem;
            width: 32px;
            height: 32px;
            transition: all 0.3s;
        }
        .delete-post-btn { color: #dc3545; }
        .delete-post-btn:hover { background: #fee2e2; transform: scale(1.1); }
        .edit-post-btn { color: #2FA084; }
        .edit-post-btn:hover { background: #e8f5e9; transform: scale(1.1); }
        
        .action-buttons {
            display: flex;
            gap: 1.5rem;
            margin: 0.8rem 0 1rem;
            border-top: 1px solid #edf2f7;
            padding-top: 0.8rem;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .action-btn {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            padding: 0.4rem 0.8rem;
            border-radius: 0.5rem;
            transition: all 0.3s;
            font-size: 0.9rem;
            position: relative;
        }
        .action-btn:hover { background: #f0f2f5; transform: translateY(-2px); }
        
        .comment-icon { color: #1e88e5; font-size: 1.1rem; }
        .share-icon { color: #43a047; }
        
        .reaction-btn {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            padding: 0.4rem 0.8rem;
            border-radius: 0.5rem;
            transition: all 0.3s;
            font-size: 0.9rem;
            position: relative;
        }
        .reaction-btn:hover { background: #f0f2f5; transform: translateY(-2px); }
        
        .reaction-popup {
            position: absolute;
            bottom: 100%;
            left: 0;
            background: white;
            border-radius: 2rem;
            padding: 0.5rem 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: none;
            gap: 0.5rem;
            z-index: 1000;
            margin-bottom: 10px;
            animation: fadeInUp 0.2s ease;
        }
        .reaction-popup.show { display: flex; }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .reaction-option {
            font-size: 1.8rem;
            cursor: pointer;
            transition: all 0.2s;
            padding: 0.3rem;
            border-radius: 50%;
        }
        .reaction-option:hover { transform: scale(1.3); background: #f0f2f5; }
        
        .reaction-count {
            font-size: 0.8rem;
            color: #6c757d;
            margin-left: 0.3rem;
        }
        
        .reaction-emoji-display { font-size: 1.1rem; }
        
        .comments-section {
            margin-top: 1rem;
            background: #f9fafc;
            border-radius: 0.8rem;
            padding: 0.8rem;
            display: none;
        }
        
        .comment-input { display: flex; gap: 0.6rem; margin-bottom: 1rem; align-items: center; }
        .comment-input input {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 0.8rem;
            padding: 0.5rem 1rem;
            outline: none;
        }
        .comment-input input:focus { border-color: #2FA084; }
        
        .comment-list { display: flex; flex-direction: column; gap: 0.8rem; }
        
        .comment-item {
            display: flex;
            gap: 0.6rem;
            position: relative;
            padding-right: 60px;
        }
        
        .comment-avatar { width: 32px; height: 32px; border-radius: 50%; overflow: hidden; }
        .comment-content {
            background: white;
            padding: 0.5rem 0.8rem;
            border-radius: 0.8rem;
            flex: 1;
        }
        .comment-user-name { font-weight: 600; cursor: pointer; }
        .comment-user-name:hover { color: #2FA084; }
        .comment-text { margin-top: 4px; word-break: break-word; }
        
        .comment-actions {
            position: absolute;
            right: 0;
            top: 0;
            display: flex;
            gap: 0.3rem;
        }
        
        .edit-comment-btn, .delete-comment-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.2rem;
            border-radius: 0.3rem;
        }
        .edit-comment-btn { color: #2FA084; }
        .delete-comment-btn { color: #dc3545; }
        
        .btn-send {
            background: linear-gradient(135deg, #2FA084, #0f3b2c);
            color: white;
            border: none;
            padding: 0.3rem 0.8rem;
            border-radius: 0.6rem;
            cursor: pointer;
        }
        
        .sidebar-right { background: #f8fafc; border-radius: 1rem; }
        
        .trend-card, .alert-card, .ai-card {
            background: white;
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .trend-item {
            margin: 0.8rem 0;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        .trend-item:hover { color: #2FA084; transform: translateX(5px); }
        
        .alert-item {
            background: #fff7e5;
            border-left: 4px solid #e6a017;
            padding: 0.6rem;
            border-radius: 0.6rem;
            margin: 0.5rem 0;
            cursor: pointer;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }
        .modal.show { display: flex; }
        
        .modal-content {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            width: 90%;
            max-width: 500px;
        }
        .modal-content h3 { margin-bottom: 1rem; color: #2FA084; }
        .modal-content input, .modal-content textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.8rem;
            margin-bottom: 1rem;
            font-family: inherit;
        }
        .modal-buttons { display: flex; gap: 1rem; justify-content: flex-end; }
        .modal-save {
            background: linear-gradient(135deg, #2FA084, #0f3b2c);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
        }
        .modal-cancel {
            background: #e2e8f0;
            color: #475569;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
        }
        
        .alert-message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .info-box {
            background: #e8f5e9;
            padding: 0.8rem;
            border-radius: 0.8rem;
            font-size: 0.75rem;
            margin-top: 1rem;
        }
        
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            z-index: 10001;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @media (max-width: 768px) {
            .main-container { flex-direction: column; }
            .top-navbar { flex-direction: column; text-align: center; }
            .nav-links { justify-content: center; }
        }
    </style>
</head>
<body>
    <nav class="top-navbar">
        <div class="logo-area">
            <img class="logo-icon" src="logo.png" alt="Logo" onerror="this.src='https://placehold.co/45x45/2FA084/white?text=SM'">
            <div class="logo-text">
                <div class="smart">Smart Municipality</div>
                <div class="municipality">BLOG CITOYEN</div>
            </div>
        </div>
        
        <div class="nav-links">
            <a href="frontoffice.php" class="nav-link active"><i class="fas fa-blog"></i> Blog</a>
            <a href="/projetweb/views/backoffice.php" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="#" class="nav-link"><i class="fas fa-globe"></i> Services en ligne</a>
            <a href="#" class="nav-link"><i class="fas fa-calendar-check"></i> RDV</a>
            <a href="#" class="nav-link"><i class="fas fa-user"></i> Profil</a>
            <a href="#" class="nav-link"><i class="fas fa-exclamation-triangle"></i> Signalement</a>
            <a href="#" class="nav-link"><i class="fas fa-calendar-alt"></i> Événements</a>
        </div>
        
        <div class="user-info" id="profileBtn">
            <div class="avatar-sm">
                <img src="<?php echo isset($_SESSION['user_avatar']) ? $_SESSION['user_avatar'] : 'https://randomuser.me/api/portraits/lego/1.jpg'; ?>" alt="avatar">
            </div>
            <span><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Connexion'; ?></span>
            <i class="fas fa-chevron-down"></i>
        </div>
    </nav>
    
    <div class="main-container">
        <div class="content-left">
            <?php if ($success_message): ?>
                <div class="alert-message alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert-message alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <!-- BARRE DE RECHERCHE -->
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <form method="GET" action="" style="flex: 1; display: flex;">
                    <input type="text" name="search" placeholder="Rechercher un post ou un utilisateur..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <?php if (!empty($search)): ?>
                    <a href="frontoffice.php" class="search-clear"><i class="fas fa-times-circle"></i></a>
                <?php endif; ?>
            </div>
            
            <!-- FORMULAIRE DE CRÉATION AVEC UPLOAD -->
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="create-card">
                <form method="POST" action="/projetweb/controllers/BlogController.php" id="createPostForm" enctype="multipart/form-data" onsubmit="return validatePost()">
                    <input type="hidden" name="action" value="createPost">
                    <div class="post-input-area">
                        <div class="avatar-sm"><img src="<?php echo isset($_SESSION['user_avatar']) ? $_SESSION['user_avatar'] : 'https://randomuser.me/api/portraits/lego/1.jpg'; ?>" alt="avatar"></div>
                        <textarea class="post-textarea" id="postContent" name="content" rows="2" placeholder="Exprimez-vous..."></textarea>
                    </div>
                    
                    <!-- APERÇU IMAGE/VIDEO -->
                    <div id="mediaPreviewContainer" class="media-preview-area">
                        <img id="previewImg" alt="Aperçu" style="display:none;">
                        <video id="previewVideo" controls style="display:none;"></video>
                        <button type="button" class="remove-media-btn" id="removeMediaBtn" style="display:none;"><i class="fas fa-times"></i></button>
                    </div>
                    
                    <div class="post-actions">
                        <div class="media-buttons">
                            <button type="button" class="btn-outline-media" id="uploadImageBtn"><i class="fas fa-image image-icon"></i> Image</button>
                            <button type="button" class="btn-outline-media" id="uploadVideoBtn"><i class="fas fa-video video-icon"></i> Vidéo</button>
                            <input type="file" id="imageUploadInput" accept="image/*" style="display:none">
                            <input type="file" id="videoUploadInput" accept="video/*" style="display:none">
                            <input type="hidden" id="imageData" name="image">
                            <input type="hidden" id="videoData" name="video">
                        </div>
                        <button type="submit" class="btn-publish"><i class="fas fa-paper-plane"></i> Publier</button>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <div class="create-card" style="text-align: center; background: #f8f9fa;">
                <p style="color: #6c757d; padding: 1rem;"><i class="fas fa-lock"></i> <a href="#" onclick="openLoginModal(); return false;">Connectez-vous</a> pour publier</p>
            </div>
            <?php endif; ?>
            
            <!-- AFFICHAGE DES POSTS -->
            <div class="posts-feed" id="postsFeed">
                <?php if (empty($posts)): ?>
                    <div class="post-card">
                        <p style="text-align: center; color: #6c757d;">
                            <?php if (!empty($search)): ?>
                                Aucun résultat pour "<strong><?php echo htmlspecialchars($search); ?></strong>"
                            <?php else: ?>
                                Aucun post pour le moment.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $postData): $post = $postData['post']; ?>
                    <div class="post-card" data-post-id="<?php echo $post->getId(); ?>">
                        <div class="post-header">
                            <div class="avatar-md"><img src="<?php echo htmlspecialchars($postData['user_avatar']); ?>" alt="avatar"></div>
                            <div>
                                <div class="post-user-name"><?php echo htmlspecialchars($postData['user_name']); ?></div>
                                <div class="post-time"><?php echo date('d/m/Y H:i', strtotime($post->getCreatedAt())); ?></div>
                            </div>
                        </div>
                        <div class="post-text"><?php echo nl2br(htmlspecialchars($post->getContent())); ?></div>
                        
                        <!-- AFFICHAGE IMAGE -->
                        <?php if ($post->getImage() && !empty($post->getImage())): ?>
                            <img class="post-media" src="<?php echo htmlspecialchars($post->getImage()); ?>" alt="Image du post">
                        <?php endif; ?>
                        
                        <!-- AFFICHAGE VIDEO -->
                        <?php if ($post->getVideo() && !empty($post->getVideo())): ?>
                            <video class="post-media" controls src="<?php echo htmlspecialchars($post->getVideo()); ?>"></video>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['user_id']) && $post->getUserId() == $_SESSION['user_id']): ?>
                        <div class="post-actions-buttons">
                            <button type="button" class="edit-post-btn" onclick="openEditModal(<?php echo $post->getId(); ?>, '<?php echo addslashes($post->getContent()); ?>')"><i class="fas fa-edit"></i></button>
                            <form method="POST" action="/projetweb/controllers/BlogController.php" style="display:inline;" onsubmit="return confirm('Supprimer cette publication ?')">
                                <input type="hidden" name="action" value="deletePost">
                                <input type="hidden" name="post_id" value="<?php echo $post->getId(); ?>">
                                <button type="submit" class="delete-post-btn"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                        <?php endif; ?>
                        
                        <div class="action-buttons">
                            <!-- RÉACTION -->
                            <div style="position: relative;">
                                <button class="reaction-btn" id="reactionBtn-<?php echo $post->getId(); ?>" onclick="toggleReactionPopup(<?php echo $post->getId(); ?>)">
                                    <span id="reactionIcon-<?php echo $post->getId(); ?>" class="reaction-emoji-display">
                                        <?php
                                        $reactionMap = ['like' => '👍', 'heart' => '❤️', 'haha' => '😂', 'wow' => '😮', 'sad' => '😢', 'angry' => '😡'];
                                        $userReaction = $postData['user_reaction'] ?? null;
                                        echo $userReaction && isset($reactionMap[$userReaction]) ? $reactionMap[$userReaction] : '👍';
                                        ?>
                                    </span>
                                    <span>Réagir</span>
                                </button>
                                <div class="reaction-popup" id="reactionPopup-<?php echo $post->getId(); ?>">
                                    <span class="reaction-option" onclick="submitReaction(<?php echo $post->getId(); ?>, 'like')">👍</span>
                                    <span class="reaction-option" onclick="submitReaction(<?php echo $post->getId(); ?>, 'heart')">❤️</span>
                                    <span class="reaction-option" onclick="submitReaction(<?php echo $post->getId(); ?>, 'haha')">😂</span>
                                    <span class="reaction-option" onclick="submitReaction(<?php echo $post->getId(); ?>, 'wow')">😮</span>
                                    <span class="reaction-option" onclick="submitReaction(<?php echo $post->getId(); ?>, 'sad')">😢</span>
                                    <span class="reaction-option" onclick="submitReaction(<?php echo $post->getId(); ?>, 'angry')">😡</span>
                                </div>
                                
                            </div>
                            
                            <!-- COMMENTAIRE -->
                            <button type="button" class="action-btn" onclick="toggleComments(<?php echo $post->getId(); ?>)">
                                <i class="fas fa-comment comment-icon"></i> 
                                <span>Commenter</span>
                                <span id="comment-count-<?php echo $post->getId(); ?>">(<?php echo $postData['comments_count']; ?>)</span>
                            </button>
                            
                            <!-- PARTAGER -->
                            <button type="button" class="action-btn" onclick="sharePost()">
                                <i class="fas fa-share-alt share-icon"></i> Partager
                            </button>
                        </div>
                        
                        <div class="comments-section" id="comments-<?php echo $post->getId(); ?>">
                            <div class="comment-list" id="comment-list-<?php echo $post->getId(); ?>">
                                <?php foreach ($postData['comments'] as $comment): ?>
                                <div class="comment-item" data-comment-id="<?php echo $comment['id']; ?>">
                                    <div class="comment-avatar"><img src="<?php echo htmlspecialchars($comment['user_avatar'] ?? 'https://randomuser.me/api/portraits/lego/1.jpg'); ?>" alt="avatar"></div>
                                    <div class="comment-content">
                                        <div class="comment-user-name"><?php echo htmlspecialchars($comment['user_name'] ?? 'Utilisateur'); ?></div>
                                        <div class="comment-text"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></div>
                                    </div>
                                    <?php if (isset($_SESSION['user_id']) && $comment['user_id'] == $_SESSION['user_id']): ?>
                                    <div class="comment-actions">
                                        <button type="button" class="edit-comment-btn" onclick="openEditCommentModal(<?php echo $comment['id']; ?>, '<?php echo addslashes($comment['content']); ?>')"><i class="fas fa-edit"></i></button>
                                        <form method="POST" action="/projetweb/controllers/BlogController.php" style="display:inline;" onsubmit="return confirm('Supprimer ce commentaire ?')">
                                            <input type="hidden" name="action" value="deleteComment">
                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                            <button type="submit" class="delete-comment-btn"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="POST" action="/projetweb/controllers/BlogController.php" class="comment-input" onsubmit="return validateComment(<?php echo $post->getId(); ?>)">
                                <input type="hidden" name="action" value="createComment">
                                <input type="hidden" name="post_id" value="<?php echo $post->getId(); ?>">
                                <input type="text" name="content" id="comment-input-<?php echo $post->getId(); ?>" placeholder="Écrire un commentaire...">
                                <button type="submit" class="btn-send"><i class="fas fa-paper-plane"></i></button>
                            </form>
                            <?php else: ?>
                            <div class="comment-input" style="justify-content: center;">
                                <p style="color: #6c757d; font-size: 0.8rem;"><a href="#" onclick="openLoginModal(); return false;">Connectez-vous</a> pour commenter</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="sidebar-right">
            <div class="trend-card">
                <h4><i class="fas fa-fire" style="color:#2FA084;"></i> Tendances</h4>
                <div class="trend-item">🏛️ Nouveau centre aquatique</div>
                <div class="trend-item">🌿 Plantation participative</div>
                <div class="trend-item">🚴‍♂️ Véloroutes urbaines</div>
            </div>
            <div class="alert-card">
                <h4><i class="fas fa-bullhorn" style="color:#2FA084;"></i> Annonces municipales</h4>
                <div class="alert-item">⚠️ Réunion publique le 25 avril</div>
                <div class="alert-item">♻️ Collecte des déchets modifiée</div>
            </div>
            <div class="ai-card">
                <h4><i class="fas fa-robot" style="color:#2FA084;"></i> AI Insights</h4>
                <p>Catégorie: <strong>Mobilité verte</strong> <span style="background:#e8f5e9; padding:0.2rem 0.5rem; border-radius:0.5rem;">sentiment positif ↗️</span></p>
            </div>
        </div>
    </div>
    
    <!-- MODAL LOGIN -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div style="text-align: center; margin-bottom: 1rem;">
                <img class="logo-icon" src="/projetweb/logo.png" alt="Logo" style="width: 60px;" onerror="this.src='https://placehold.co/60x60/2FA084/white?text=SM'">
                <h3 style="color: #2FA084;">Connexion</h3>
            </div>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <form method="POST" action="/projetweb/controllers/BlogController.php" onsubmit="return validateLogin()">
                    <input type="hidden" name="action" value="login">
                    <input type="email" name="email" id="loginEmail" placeholder="Email" required>
                    <input type="password" name="password" id="loginPassword" placeholder="Mot de passe" required>
                    <div class="modal-buttons">
                        <button type="button" class="modal-cancel" onclick="closeLoginModal()">Annuler</button>
                        <button type="submit" class="modal-save">Se connecter</button>
                    </div>
                </form>
                <div class="info-box">
                    <strong>Comptes test :</strong><br>
                    jean@example.com / password123<br>
                    marie@example.com / password123
                </div>
            <?php else: ?>
                <div style="text-align: center;">
                    <div class="avatar-md" style="margin: 0 auto 1rem; width: 80px; height: 80px;">
                        <img src="<?php echo $_SESSION['user_avatar']; ?>" alt="avatar">
                    </div>
                    <p>Connecté en tant que</p>
                    <h4 style="color: #2FA084;"><?php echo $_SESSION['user_name']; ?></h4>
                    <form method="POST" action="/projetweb/controllers/BlogController.php">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="modal-save" style="background:#dc3545;">Se déconnecter</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- MODAL EDIT POST -->
    <div id="editPostModal" class="modal">
        <div class="modal-content">
            <h3>Modifier la publication</h3>
            <form method="POST" action="/projetweb/controllers/BlogController.php" onsubmit="return validateEditPost()">
                <input type="hidden" name="action" value="updatePost">
                <input type="hidden" name="post_id" id="edit_post_id">
                <textarea id="edit_post_content" name="content" rows="4" placeholder="Nouveau contenu..."></textarea>
                <div class="modal-buttons">
                    <button type="button" class="modal-cancel" onclick="closeEditModal()">Annuler</button>
                    <button type="submit" class="modal-save">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- MODAL EDIT COMMENT -->
    <div id="editCommentModal" class="modal">
        <div class="modal-content">
            <h3>Modifier le commentaire</h3>
            <form method="POST" action="/projetweb/controllers/BlogController.php" onsubmit="return validateEditComment()">
                <input type="hidden" name="action" value="updateComment">
                <input type="hidden" name="comment_id" id="edit_comment_id">
                <textarea id="edit_comment_content" name="content" rows="3" placeholder="Nouveau commentaire..."></textarea>
                <div class="modal-buttons">
                    <button type="button" class="modal-cancel" onclick="closeEditCommentModal()">Annuler</button>
                    <button type="submit" class="modal-save">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function validatePost() {
            let content = document.getElementById('postContent').value.trim();
            if (content === '') {
                alert('Le contenu ne peut pas être vide');
                return false;
            }
            return true;
        }
        
        function validateComment(postId) {
            let content = document.getElementById('comment-input-' + postId).value.trim();
            if (content === '') {
                alert('Le commentaire ne peut pas être vide');
                return false;
            }
            return true;
        }
        
        function validateEditPost() {
            let content = document.getElementById('edit_post_content').value.trim();
            if (content === '') {
                alert('Le contenu ne peut pas être vide');
                return false;
            }
            return true;
        }
        
        function validateEditComment() {
            let content = document.getElementById('edit_comment_content').value.trim();
            if (content === '') {
                alert('Le commentaire ne peut pas être vide');
                return false;
            }
            return true;
        }
        
        function validateLogin() {
            let email = document.getElementById('loginEmail').value.trim();
            let password = document.getElementById('loginPassword').value.trim();
            if (email === '') {
                alert('Email requis');
                return false;
            }
            if (password === '') {
                alert('Mot de passe requis');
                return false;
            }
            return true;
        }
        
        // ============================================
        // MODALS
        
        function openLoginModal() { document.getElementById('loginModal').classList.add('show'); }
        function closeLoginModal() { document.getElementById('loginModal').classList.remove('show'); }
        
        document.getElementById('profileBtn')?.addEventListener('click', function() { openLoginModal(); });
        
        function openEditModal(id, content) {
            document.getElementById('edit_post_id').value = id;
            document.getElementById('edit_post_content').value = content;
            document.getElementById('editPostModal').classList.add('show');
        }
        function closeEditModal() { document.getElementById('editPostModal').classList.remove('show'); }
        
        function openEditCommentModal(id, content) {
            document.getElementById('edit_comment_id').value = id;
            document.getElementById('edit_comment_content').value = content;
            document.getElementById('editCommentModal').classList.add('show');
        }
        function closeEditCommentModal() { document.getElementById('editCommentModal').classList.remove('show'); }
        
        window.onclick = function(e) {
            if (e.target === document.getElementById('loginModal')) closeLoginModal();
            if (e.target === document.getElementById('editPostModal')) closeEditModal();
            if (e.target === document.getElementById('editCommentModal')) closeEditCommentModal();
        }
        
        // ============================================
        // COMMENTAIRES
        // ============================================
        
        function toggleComments(id) {
            let commentsDiv = document.getElementById('comments-' + id);
            if (commentsDiv.style.display === 'none' || commentsDiv.style.display === '') {
                commentsDiv.style.display = 'block';
            } else {
                commentsDiv.style.display = 'none';
            }
        }
        
        // ============================================
        // PARTAGE
        // ============================================
        
        function sharePost() {
            let url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                showToast('Lien copié !');
            });
        }
        
        function showToast(message) {
            let toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = '<i class="fas fa-check-circle"></i> ' + message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        // ============================================
        // RÉACTIONS AJAX
        // ============================================
        
        let currentPopup = null;
        
        function toggleReactionPopup(postId) {
            let popup = document.getElementById('reactionPopup-' + postId);
            if (currentPopup && currentPopup !== popup) {
                currentPopup.classList.remove('show');
            }
            if (popup.classList.contains('show')) {
                popup.classList.remove('show');
                currentPopup = null;
            } else {
                popup.classList.add('show');
                currentPopup = popup;
            }
        }
        
        function submitReaction(postId, type) {
            let popup = document.getElementById('reactionPopup-' + postId);
            popup.classList.remove('show');
            currentPopup = null;
            
            let reactionIcon = document.getElementById('reactionIcon-' + postId);
            let emojiMap = {
                'like': '👍', 'heart': '❤️', 'haha': '😂',
                'wow': '😮', 'sad': '😢', 'angry': '😡'
            };
            if (reactionIcon && emojiMap[type]) {
                reactionIcon.innerHTML = emojiMap[type];
            }
            
            let formData = new FormData();
            formData.append('action', 'reactToPostAjax');
            formData.append('post_id', postId);
            formData.append('type', type);
            
            fetch('/projetweb/controllers/BlogController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let countSpan = document.getElementById('reactionCount-' + postId);
                    if (countSpan) countSpan.innerHTML = data.count;
                    showToast(data.message);
                } else {
                    showToast(data.error || 'Erreur');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Erreur de connexion');
            });
        }
        
        document.addEventListener('click', function(e) {
            if (currentPopup && !currentPopup.contains(e.target) && !e.target.closest('.reaction-btn')) {
                currentPopup.classList.remove('show');
                currentPopup = null;
            }
        });
        
        // ============================================
        // UPLOAD D'IMAGES ET VIDÉOS
        // ============================================
        
        let selectedImage = null;
        let selectedVideo = null;
        
        const uploadImageBtn = document.getElementById('uploadImageBtn');
        const uploadVideoBtn = document.getElementById('uploadVideoBtn');
        const imageUploadInput = document.getElementById('imageUploadInput');
        const videoUploadInput = document.getElementById('videoUploadInput');
        const previewImg = document.getElementById('previewImg');
        const previewVideo = document.getElementById('previewVideo');
        const mediaPreviewContainer = document.getElementById('mediaPreviewContainer');
        const removeMediaBtn = document.getElementById('removeMediaBtn');
        const imageData = document.getElementById('imageData');
        const videoData = document.getElementById('videoData');
        
        if (uploadImageBtn) {
            uploadImageBtn.addEventListener('click', function() {
                imageUploadInput.click();
            });
        }
        
        if (uploadVideoBtn) {
            uploadVideoBtn.addEventListener('click', function() {
                videoUploadInput.click();
            });
        }
        
        if (imageUploadInput) {
            imageUploadInput.addEventListener('change', function(e) {
                let file = e.target.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(event) {
                        selectedImage = event.target.result;
                        previewImg.src = selectedImage;
                        previewImg.style.display = 'block';
                        previewVideo.style.display = 'none';
                        mediaPreviewContainer.style.display = 'block';
                        removeMediaBtn.style.display = 'block';
                        imageData.value = selectedImage;
                        videoData.value = '';
                        selectedVideo = null;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        if (videoUploadInput) {
            videoUploadInput.addEventListener('change', function(e) {
                let file = e.target.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(event) {
                        selectedVideo = event.target.result;
                        previewVideo.src = selectedVideo;
                        previewVideo.style.display = 'block';
                        previewImg.style.display = 'none';
                        mediaPreviewContainer.style.display = 'block';
                        removeMediaBtn.style.display = 'block';
                        videoData.value = selectedVideo;
                        imageData.value = '';
                        selectedImage = null;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        if (removeMediaBtn) {
            removeMediaBtn.addEventListener('click', function() {
                selectedImage = null;
                selectedVideo = null;
                previewImg.style.display = 'none';
                previewVideo.style.display = 'none';
                mediaPreviewContainer.style.display = 'none';
                removeMediaBtn.style.display = 'none';
                imageData.value = '';
                videoData.value = '';
                if (imageUploadInput) imageUploadInput.value = '';
                if (videoUploadInput) videoUploadInput.value = '';
            });
        }
    </script>
</body>
</html>