<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../controllers/BlogController.php';
$blogC = new BlogController();

$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$postData = $blogC->getPostById($postId);
$currentLang = $blogC->getCurrentLang();
$is_rtl = $blogC->isRtl();
$t = function($key) use ($blogC) { return $blogC->t($key); };
$current_theme = $_SESSION['user_theme'] ?? 'light';
$current_font_size = $_SESSION['font_size'] ?? 100;
$sessionAvatar = $_SESSION['user_avatar'] ?? 'https://randomuser.me/api/portraits/lego/1.jpg';
$BASE_URL = '/smart/smart-municipality';
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $is_rtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality - <?= $t('blog') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Mêmes styles que frontoffice (tu peux les copier) */
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; }
        .blog-single-page { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        .blog-back-link { color: #2FA084; text-decoration: none; margin-bottom: 1.5rem; display: inline-block; }
        .blog-single-card { background: white; border-radius: 1rem; padding: 1.8rem; box-shadow: 0 12px 34px rgba(0,0,0,0.08); }
        .post-header { display: flex; gap: 1rem; margin-bottom: 1rem; }
        .avatar-md { width: 48px; height: 48px; border-radius: 50%; overflow: hidden; }
        .post-user-name { font-weight: 700; }
        .post-time { font-size: 0.8rem; color: #7d8a99; }
        .blog-single-content { line-height: 1.8; white-space: pre-wrap; }
        .single-post-media { max-width: 100%; border-radius: 1rem; margin: 1rem 0; }
    </style>
</head>
<body class="theme-<?= $current_theme ?>" style="font-size: <?= $current_font_size ?>%">
    <div class="blog-single-page">
        <a class="blog-back-link" href="<?= $BASE_URL ?>/index.php?action=blog">← <?= $t('back_to_blog') ?></a>
        <?php if (empty($postData)): ?>
            <div class="empty-state"><?= $t('article_not_found') ?></div>
        <?php else: $post = $postData['post']; 
            $avatar = $postData['user_avatar'];
            if ($avatar && strpos($avatar, '://') === false && strpos($avatar, 'data:') !== 0) {
                $avatar = $BASE_URL . '/' . ltrim($avatar, '/');
            }
        ?>
            <article class="blog-single-card">
                <div class="post-header">
                    <div class="avatar-md"><img src="<?= htmlspecialchars($avatar) ?>"></div>
                    <div>
                        <div class="post-user-name"><?= htmlspecialchars($postData['user_name']) ?></div>
                        <div class="post-time"><?= date('d/m/Y H:i', strtotime($post->getCreatedAt())) ?></div>
                    </div>
                </div>
                <div class="blog-single-content"><?= nl2br(htmlspecialchars_decode(htmlspecialchars($post->getContent(), ENT_QUOTES, 'UTF-8', false))) ?></div>
                <?php if ($post->getImage()): ?>
                    <img class="single-post-media" src="<?= htmlspecialchars($post->getImage()) ?>">
                <?php endif; ?>
                <?php if ($post->getVideo()): ?>
                    <video class="single-post-media" controls src="<?= htmlspecialchars($post->getVideo()) ?>"></video>
                <?php endif; ?>
                <!-- Ici tu peux ajouter les réactions et commentaires si besoin, mais ce fichier est souvent simple -->
            </article>
        <?php endif; ?>
    </div>
</body>
</html>