<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../controllers/BlogController.php';

$controller = new BlogController();
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$posts = $controller->searchPostsInPhp($search);

$success_message = $_SESSION['success'] ?? '';
$error_message = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$current_lang = $controller->getCurrentLang();
$is_rtl = $controller->isRtl();
$t = function($key) use ($controller) { return $controller->t($key); };
$current_theme = $_SESSION['user_theme'] ?? 'light';
$current_font_size = $_SESSION['font_size'] ?? 100;
?>
<!DOCTYPE html>
<html lang="<?= $current_lang ?>" dir="<?= $is_rtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality | <?= $t('blog') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?php if ($is_rtl): ?>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php else: ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php endif; ?>
    <style>
        /* ========== VARIABLES THEMES ========== */
        body {
            --bg-body: #f0f2f5;
            --bg-card: #ffffff;
            --text-primary: #1e2a32;
            --text-secondary: #5b6e8c;
            --border-light: #e4e6eb;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.05);
            --hover-bg: #f0f2f5;
            --accent: #2FA084;
            --accent-dark: #0f3b2c;
            --danger: #dc3545;
            --comment-color: #3b82f6;
            --share-color: #10b981;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glow-like: #ffd700;
            --glow-heart: #ff3366;
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            color: var(--text-primary);
            transition: background 0.2s, color 0.2s;
        }
        body.theme-dark {
            --bg-body: #0a0c10;
            --bg-card: #1e1f24;
            --text-primary: #e4e6eb;
            --text-secondary: #b0b3b8;
            --border-light: #3e4045;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.3);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.2);
            --hover-bg: #2d2f36;
            --comment-color: #60a5fa;
            --share-color: #34d399;
            --glass-bg: rgba(30, 30, 40, 0.85);
            --glass-border: rgba(255, 255, 255, 0.1);
        }
        <?php if ($is_rtl): ?>
        body { direction: rtl; font-family: 'Cairo', 'Inter', sans-serif; }
        .post-actions-buttons { left: 1rem !important; right: auto !important; }
        .comment-actions { right: auto; left: 0; }
        .comment-item { padding-right: 0; padding-left: 60px; }
        .reaction-popup { left: auto; right: 50%; transform: translateX(50%); }
        .reaction-popup::after { left: auto; right: 50%; transform: translateX(50%); }
        <?php endif; ?>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* ========== NAVBAR (inchangée) ========== */
        .top-navbar {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-light);
            padding: 0.6rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-sm);
        }
        .logo-area { display: flex; align-items: center; gap: 0.75rem; }
        .logo-icon { width: 42px; height: 42px; border-radius: 12px; object-fit: cover; }
        .logo-text .smart { font-size: 1.25rem; font-weight: 800; letter-spacing: -0.3px; color: var(--accent); }
        .logo-text .municipality { font-size: 0.65rem; color: var(--text-secondary); }
        .nav-links {
            display: flex;
            gap: 0.3rem;
            flex-wrap: wrap;
            background: var(--hover-bg);
            padding: 0.2rem;
            border-radius: 3rem;
        }
        .nav-link {
            color: var(--text-secondary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .nav-link i { font-size: 1rem; }
        .nav-link:hover, .nav-link.active {
            background: var(--accent);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        .navbar-right {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .control-group {
            display: flex;
            gap: 0.3rem;
            align-items: center;
            background: var(--hover-bg);
            border-radius: 2rem;
            padding: 0.2rem 0.5rem;
        }
        .control-btn {
            background: transparent;
            border: none;
            padding: 0.3rem 0.6rem;
            border-radius: 1.5rem;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .control-btn:hover { background: rgba(0,0,0,0.08); color: var(--accent); }
        .lang-selector .control-btn.active { background: var(--accent); color: white; }
        .font-size-control {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: var(--hover-bg);
            border-radius: 2rem;
            padding: 0.2rem 0.8rem;
        }
        .font-size-control span { font-size: 0.8rem; font-weight: 500; color: var(--text-secondary); }
        .font-size-control input { width: 90px; cursor: pointer; background: var(--bg-card); border-radius: 1rem; height: 4px; }
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--hover-bg);
            padding: 0.2rem 0.8rem 0.2rem 0.5rem;
            border-radius: 2rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .user-info:hover { background: var(--border-light); transform: scale(1.02); }
        .avatar-sm { width: 32px; height: 32px; border-radius: 50%; overflow: hidden; border: 2px solid var(--accent); }
        .avatar-sm img, .avatar-md img { width: 100%; height: 100%; object-fit: cover; }
        .avatar-md { width: 48px; height: 48px; border-radius: 50%; overflow: hidden; }

        /* ========== MAIN LAYOUT ========== */
        .main-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            display: flex;
            gap: 2rem;
        }
        .content-left { flex: 2; }
        .sidebar-right {
            flex: 1;
            position: sticky;
            top: 80px;
            height: fit-content;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .trend-card, .alert-card, .ai-card {
            background: var(--bg-card);
            border-radius: 1rem;
            padding: 1rem;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-sm);
        }
        .trend-item { margin: 0.8rem 0; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; color: var(--text-primary); transition: 0.2s; }
        .trend-item:hover { color: var(--accent); transform: translateX(5px); }
        .alert-item { background: rgba(230,160,23,0.1); border-left: 4px solid #e6a017; padding: 0.6rem; border-radius: 0.6rem; margin: 0.5rem 0; cursor: pointer; }

        /* ========== RECHERCHE ========== */
        .search-container {
            position: relative;
            background: var(--bg-card);
            border-radius: 2rem;
            padding: 0.5rem 1.2rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .search-container i { color: var(--accent); }
        .search-container form { flex: 1; display: flex; gap: 0.5rem; }
        .search-container input { flex: 1; border: none; outline: none; background: transparent; color: var(--text-primary); }
        .search-container button { background: none; border: none; color: var(--accent); cursor: pointer; }
        .search-results {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: var(--bg-card);
            border-radius: 1rem;
            border: 1px solid var(--border-light);
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        .search-results.show { display: block; }
        .search-result-item { display: flex; gap: 0.8rem; padding: 0.8rem; border-bottom: 1px solid var(--border-light); cursor: pointer; align-items: center; }
        .search-result-item:hover { background: var(--hover-bg); }
        .search-result-avatar { width: 40px; height: 40px; border-radius: 50%; overflow: hidden; }
        .search-result-content { flex: 1; }
        .no-result { padding: 1rem; text-align: center; }

        /* ========== CREATE POST ========== */
        .create-card {
            background: var(--bg-card);
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-light);
        }
        .post-input-area { display: flex; gap: 0.8rem; margin-bottom: 1rem; }
        .post-textarea {
            flex: 1;
            border: 1px solid var(--border-light);
            border-radius: 1.5rem;
            padding: 0.8rem 1.2rem;
            font-family: inherit;
            resize: none;
            background: var(--bg-card);
            color: var(--text-primary);
            outline: none;
        }
        .post-textarea:focus { border-color: var(--accent); }
        .media-preview-area { margin: 0.8rem 0; max-width: 300px; position: relative; }
        .media-preview-area img, .media-preview-area video { width: 100%; border-radius: 0.8rem; max-height: 200px; object-fit: cover; }
        .remove-media-btn { position: absolute; top: 5px; right: 5px; background: rgba(0,0,0,0.6); color: white; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; }
        .post-actions { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.8rem; }
        .media-buttons { display: flex; gap: 0.5rem; }
        .btn-outline-media { background: var(--hover-bg); border: none; padding: 0.4rem 1rem; border-radius: 2rem; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-secondary); transition: 0.2s; }
        .btn-outline-media:hover { background: var(--border-light); }
        .btn-publish { background: var(--accent); color: white; border: none; padding: 0.5rem 1.5rem; border-radius: 2rem; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .btn-publish:hover { background: var(--accent-dark); transform: translateY(-2px); }

        /* ========== POST CARD ========== */
        .posts-feed { display: flex; flex-direction: column; gap: 1rem; }
        .post-card {
            background: var(--bg-card);
            border-radius: 1rem;
            padding: 1rem;
            position: relative;
            border: 1px solid var(--border-light);
            transition: 0.2s;
        }
        .post-card:hover { box-shadow: var(--shadow-md); }
        .post-header { display: flex; align-items: center; gap: 0.8rem; margin-bottom: 0.8rem; }
        .post-user-name { font-weight: 700; cursor: pointer; }
        .post-user-name:hover { color: var(--accent); }
        .post-time { font-size: 0.7rem; color: var(--text-secondary); }
        .post-text { margin: 0.8rem 0; line-height: 1.5; cursor: pointer; }
        .post-text.speaking { background-color: rgba(47,160,132,0.2); outline: 2px solid var(--accent); }
        .post-media { max-width: 100%; border-radius: 0.8rem; margin: 0.8rem 0; max-height: 350px; object-fit: cover; }
        .post-actions-buttons { position: absolute; top: 1rem; right: 1rem; display: flex; gap: 0.5rem; }
        .delete-post-btn, .edit-post-btn {
            background: var(--hover-bg); border: none; cursor: pointer; padding: 0.3rem; border-radius: 50%; width: 32px; height: 32px;
            display: inline-flex; align-items: center; justify-content: center; color: var(--text-secondary);
        }
        .delete-post-btn:hover { background: var(--danger); color: white; }
        .edit-post-btn:hover { background: var(--accent); color: white; }

        /* ========== REACTIONS (POPUP MODERNE GLASSMORPHISM) ========== */
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin: 0.8rem 0 0.5rem;
            border-top: 1px solid var(--border-light);
            padding-top: 0.6rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .reaction-area {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: var(--hover-bg);
            border-radius: 2rem;
            padding: 0.3rem 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .reaction-area:hover { background: var(--border-light); transform: scale(1.02); }
        .reaction-emoji { font-size: 1.2rem; cursor: pointer; }
        .reaction-count, .reaction-text { font-weight: 500; color: var(--text-secondary); font-size: 0.85rem; }

        /* ========== GLASSMORPHISM POPUP ========== */
        .reaction-popup {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: var(--glass-bg);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-radius: 48px;
            padding: 10px 18px;
            display: none;
            gap: 14px;
            z-index: 1000;
            margin-bottom: 12px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 20px 35px -10px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.1);
            white-space: nowrap;
            animation: popupFloat 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        .reaction-popup.show { display: flex; }
        @keyframes popupFloat {
            from { opacity: 0; transform: translateX(-50%) scale(0.9); }
            to { opacity: 1; transform: translateX(-50%) scale(1); }
        }

        /* Style des émoticônes + animations pro */
        .reaction-option {
            font-size: 2rem;
            cursor: pointer;
            display: inline-block;
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));
            will-change: transform, filter;
        }
        /* Hover commun : bounce + scale + glow */
        .reaction-option:hover {
            transform: scale(1.25) translateY(-6px);
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3));
        }
        /* Glow spécifique par émotion (optionnel) */
        .reaction-option[data-type="like"]:hover { text-shadow: 0 0 8px #ffd700; }
        .reaction-option[data-type="heart"]:hover { text-shadow: 0 0 12px #ff3366; }
        .reaction-option[data-type="haha"]:hover { text-shadow: 0 0 8px #ffbb00; }
        .reaction-option[data-type="wow"]:hover { text-shadow: 0 0 8px #ffaa44; }
        .reaction-option[data-type="sad"]:hover { text-shadow: 0 0 8px #88aaff; }
        .reaction-option[data-type="angry"]:hover { text-shadow: 0 0 8px #ff4444; }

        .action-btn {
            background: none; border: none; display: inline-flex; align-items: center; gap: 0.5rem;
            cursor: pointer; padding: 0.4rem 0.8rem; border-radius: 2rem; transition: all 0.2s;
            font-size: 0.85rem; font-weight: 500; color: var(--text-secondary);
        }
        .action-btn i.fa-comment { color: var(--comment-color); }
        .action-btn i.fa-share-alt { color: var(--share-color); }
        .action-btn:hover { background: var(--hover-bg); color: var(--accent); }

        /* ========== COMMENTAIRES ========== */
        .comments-section { margin-top: 1rem; background: var(--hover-bg); border-radius: 0.8rem; padding: 0.8rem; display: none; }
        .comment-input { display: flex; gap: 0.6rem; margin-bottom: 1rem; align-items: center; }
        .comment-input input { flex: 1; border: 1px solid var(--border-light); border-radius: 2rem; padding: 0.5rem 1rem; background: var(--bg-card); color: var(--text-primary); outline: none; transition: 0.2s; }
        .comment-input input:focus { border-color: var(--accent); }
        .comment-list { display: flex; flex-direction: column; gap: 0.8rem; }
        .comment-item { display: flex; gap: 0.6rem; position: relative; padding-right: 60px; }
        .comment-avatar { width: 32px; height: 32px; border-radius: 50%; overflow: hidden; }
        .comment-content { background: var(--bg-card); padding: 0.5rem 0.8rem; border-radius: 1rem; flex: 1; }
        .comment-user-name { font-weight: 600; font-size: 0.85rem; }
        .comment-text { margin-top: 4px; font-size: 0.9rem; }
        .comment-actions { position: absolute; right: 0; top: 0; display: flex; gap: 0.3rem; }
        .edit-comment-btn, .delete-comment-btn { background: none; border: none; cursor: pointer; padding: 0.2rem; border-radius: 50%; color: var(--text-secondary); transition: 0.2s; }
        .edit-comment-btn:hover { color: var(--accent); }
        .delete-comment-btn:hover { color: var(--danger); }
        .btn-send { background: var(--accent); color: white; border: none; padding: 0.3rem 0.8rem; border-radius: 2rem; cursor: pointer; transition: 0.2s; }
        .btn-send:hover { background: var(--accent-dark); }

        /* ========== MODALS & TOAST ========== */
        .modal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 10000;
            justify-content: center; align-items: center;
        }
        .modal.show { display: flex; }
        .modal-content {
            background: var(--bg-card); border-radius: 1rem; padding: 1.5rem;
            width: 90%; max-width: 500px; border: 1px solid var(--border-light);
            animation: fadeInModal 0.2s ease;
        }
        @keyframes fadeInModal { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .modal-content h3 { margin-bottom: 1rem; color: var(--accent); }
        .modal-content input, .modal-content textarea { width: 100%; padding: 0.7rem; border: 1px solid var(--border-light); border-radius: 0.8rem; margin-bottom: 1rem; background: var(--bg-card); color: var(--text-primary); outline: none; }
        .modal-buttons { display: flex; gap: 1rem; justify-content: flex-end; }
        .modal-save { background: var(--accent); color: white; padding: 0.5rem 1rem; border: none; border-radius: 0.5rem; cursor: pointer; }
        .modal-cancel { background: var(--hover-bg); color: var(--text-secondary); padding: 0.5rem 1rem; border: none; border-radius: 0.5rem; cursor: pointer; }
        .toast {
            position: fixed; bottom: 20px; right: 20px; background: var(--bg-card); color: var(--text-primary);
            padding: 12px 20px; border-radius: 2rem; box-shadow: var(--shadow-md); z-index: 10001;
            animation: slideIn 0.3s ease; border: 1px solid var(--border-light);
        }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @media (max-width: 768px) {
            .main-container { flex-direction: column; }
            .sidebar-right { position: static; }
            .top-navbar { padding: 0.8rem 1rem; }
            .nav-links { order: 3; width: 100%; justify-content: center; margin-top: 0.5rem; }
            .navbar-right { order: 2; }
            .logo-area { order: 1; }
        }
        .reaction-user-item { display: flex; align-items: center; gap: 0.8rem; padding: 0.5rem; border-bottom: 1px solid var(--border-light); }
        .reaction-user-avatar { width: 40px; height: 40px; border-radius: 50%; overflow: hidden; }
        .reaction-user-name { flex: 1; font-weight: 500; }
        .reaction-emoji-big { font-size: 1.6rem; }
    </style>
</head>
<body class="<?= $is_rtl ? 'rtl' : '' ?> theme-<?= $current_theme ?>" style="font-size: <?= $current_font_size ?>%;">
<nav class="top-navbar">
    <div class="logo-area"><img class="logo-icon" src="logo.png" alt="Logo" onerror="this.src='https://placehold.co/45x45/2FA084/white?text=SM'"><div class="logo-text"><div class="smart">Smart Municipality</div><div class="municipality"><?= $t('blog') ?></div></div></div>
    <div class="nav-links">
        <a href="#" class="nav-link"><i class="fas fa-user"></i> <?= $t('profile') ?></a>
        <a href="#" class="nav-link"><i class="fas fa-calendar-alt"></i> <?= $t('events') ?></a>
        <a href="#" class="nav-link"><i class="fas fa-map"></i> <?= $t('map') ?></a>
        <a href="frontoffice.php" class="nav-link active"><i class="fas fa-blog"></i> <?= $t('blog') ?></a>
        <a href="#" class="nav-link"><i class="fas fa-globe"></i> <?= $t('services') ?></a>
        <a href="#" class="nav-link"><i class="fas fa-calendar-check"></i> <?= $t('appointments') ?></a>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <a href="backoffice.php" class="nav-link"><i class="fas fa-chart-line"></i> <?= $t('dashboard') ?></a>
        <?php endif; ?>
    </div>
    <div class="navbar-right">
        <form method="GET" action="/projetweb/controllers/BlogController.php" style="display: flex; align-items: center; gap: 0.5rem;">
            <input type="hidden" name="action" value="setFontSize">
            <div class="font-size-control"><span>A-</span><input type="range" name="size" min="80" max="130" value="<?= $current_font_size ?>" step="1" onchange="this.form.submit()"><span>A+</span></div>
        </form>
        <div class="control-group lang-selector">
            <a href="?lang=fr" class="control-btn <?= $current_lang === 'fr' ? 'active' : '' ?>">FR</a>
            <a href="?lang=en" class="control-btn <?= $current_lang === 'en' ? 'active' : '' ?>">EN</a>
            <a href="?lang=ar" class="control-btn <?= $current_lang === 'ar' ? 'active' : '' ?>">AR</a>
        </div>
        <a href="/projetweb/controllers/BlogController.php?action=setTheme&theme=<?= $current_theme === 'light' ? 'dark' : 'light' ?>" class="control-btn" style="font-size: 1.2rem;"><i class="fas <?= $current_theme === 'light' ? 'fa-moon' : 'fa-sun' ?>"></i></a>
        <div class="user-info" id="profileBtn"><div class="avatar-sm"><img src="<?= htmlspecialchars($sessionAvatar ?? 'https://randomuser.me/api/portraits/lego/1.jpg') ?>"></div><span><?= isset($_SESSION['user_name']) ? $_SESSION['user_name'] : $t('login_title') ?></span></div>
    </div>
</nav>

<div class="main-container">
    <div class="content-left">
        <div class="search-container"><i class="fas fa-search"></i><form id="searchForm" onsubmit="return false;"><input type="text" id="searchInput" placeholder="<?= $t('search_placeholder') ?>" autocomplete="off"><button type="button" id="searchButton"><i class="fas fa-arrow-right"></i></button></form><div id="searchResults" class="search-results"></div></div>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="create-card">
            <form method="POST" action="/projetweb/controllers/BlogController.php" enctype="multipart/form-data" onsubmit="return validatePost()">
                <input type="hidden" name="action" value="createPost">
                <div class="post-input-area"><div class="avatar-sm"><img src="<?= htmlspecialchars($sessionAvatar ?? 'https://randomuser.me/api/portraits/lego/1.jpg') ?>"></div><textarea class="post-textarea" id="postContent" name="content" rows="2" placeholder="<?= $t('write_comment') ?>"></textarea></div>
                <div id="mediaPreviewContainer" class="media-preview-area" style="display:none;"><img id="previewImg" style="display:none;"><video id="previewVideo" controls style="display:none;"></video><button type="button" id="removeMediaBtn" style="display:none;"><i class="fas fa-times"></i></button></div>
                <div class="post-actions"><div class="media-buttons"><button type="button" class="btn-outline-media" id="uploadImageBtn"><i class="fas fa-image"></i> <?= $t('image') ?></button><button type="button" class="btn-outline-media" id="uploadVideoBtn"><i class="fas fa-video"></i> <?= $t('video') ?></button><input type="file" id="imageUploadInput" name="image" accept="image/*" style="display:none"><input type="file" id="videoUploadInput" name="video" accept="video/*" style="display:none"></div><button type="submit" class="btn-publish"><i class="fas fa-paper-plane"></i> <?= $t('publish') ?></button></div>
            </form>
        </div>
        <?php else: ?>
        <div class="create-card" style="text-align:center;"><p><a href="#" onclick="openLoginModal(); return false;"><?= $t('login_to_post') ?></a></p></div>
        <?php endif; ?>
        
        <div class="posts-feed" id="postsFeed">
            <?php if (empty($posts)): ?>
                <div class="post-card"><p style="text-align:center;"><?= $t('no_posts') ?></p></div>
            <?php else: foreach ($posts as $postData): $post = $postData['post']; ?>
            <?php
            $mapEmoji = ['like'=>'👍', 'heart'=>'❤️', 'haha'=>'😂', 'wow'=>'😮', 'sad'=>'😢', 'angry'=>'😡'];
            $currentReaction = $postData['user_reaction'] ?? null;
            $currentEmoji = $mapEmoji[$currentReaction] ?? '👍';
            ?>
            <div class="post-card" data-post-id="<?= $post->getId() ?>">
                <div class="post-header"><div class="avatar-md"><img src="<?= htmlspecialchars($postData['user_avatar'] ?? 'https://randomuser.me/api/portraits/lego/1.jpg') ?>"></div><div><div class="post-user-name"><?= htmlspecialchars($postData['user_name']) ?></div><div class="post-time"><?= date('d/m/Y H:i', strtotime($post->getCreatedAt())) ?></div></div></div>
                <div class="post-text"><?= nl2br(htmlspecialchars($post->getContent())) ?></div>
                <?php if ($post->getImage()): ?><img class="post-media" src="<?= htmlspecialchars($post->getImage()) ?>"><?php endif; ?>
                <?php if ($post->getVideo()): ?><video class="post-media" controls src="<?= htmlspecialchars($post->getVideo()) ?>"></video><?php endif; ?>
                
                <?php if (isset($_SESSION['user_id']) && $post->getUserId() == $_SESSION['user_id']): ?>
                <div class="post-actions-buttons"><button class="edit-post-btn" onclick="openEditModal(<?= $post->getId() ?>, '<?= addslashes($post->getContent()) ?>')"><i class="fas fa-edit"></i></button><button class="delete-post-btn" onclick="askDeletePost(<?= $post->getId() ?>, this.closest('.post-card'))"><i class="fas fa-trash"></i></button></div>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <div class="reaction-area">
                        <span class="reaction-emoji" data-post-id="<?= $post->getId() ?>"><?= $currentEmoji ?></span>
                        <span class="reaction-count" data-post-id="<?= $post->getId() ?>"><?= $postData['reactions_count'] ?? 0 ?></span>
                        <span class="reaction-text" data-post-id="<?= $post->getId() ?>"><?= $t('react') ?></span>
                        <div class="reaction-popup" id="reactionPopup-<?= $post->getId() ?>">
                            <span class="reaction-option" data-type="like">👍</span>
                            <span class="reaction-option" data-type="heart">❤️</span>
                            <span class="reaction-option" data-type="haha">😂</span>
                            <span class="reaction-option" data-type="wow">😮</span>
                            <span class="reaction-option" data-type="sad">😢</span>
                            <span class="reaction-option" data-type="angry">😡</span>
                        </div>
                    </div>
                    <button class="action-btn" onclick="toggleComments(<?= $post->getId() ?>)"><i class="fas fa-comment"></i> <?= $t('comment') ?> <span id="comment-count-<?= $post->getId() ?>">(<?= $postData['comments_count'] ?? 0 ?>)</span></button>
                    <button class="action-btn" onclick="sharePost()"><i class="fas fa-share-alt"></i> <?= $t('share') ?></button>
                </div>
                
                <div class="comments-section" id="comments-<?= $post->getId() ?>">
                    <div class="comment-list" id="comment-list-<?= $post->getId() ?>">
                        <?php foreach (($postData['comments'] ?? []) as $comment): ?>
                        <div class="comment-item" data-comment-id="<?= $comment['id'] ?>">
                            <div class="comment-avatar"><img src="<?= htmlspecialchars($comment['user_avatar'] ?? 'https://randomuser.me/api/portraits/lego/1.jpg') ?>"></div>
                            <div class="comment-content"><div class="comment-user-name"><?= htmlspecialchars($comment['user_name']) ?></div><div class="comment-text"><?= nl2br(htmlspecialchars($comment['content'])) ?></div></div>
                            <?php if (isset($_SESSION['user_id']) && $comment['user_id'] == $_SESSION['user_id']): ?>
                            <div class="comment-actions"><button class="edit-comment-btn" onclick="editCommentAjax(<?= $comment['id'] ?>, '<?= addslashes($comment['content']) ?>')"><i class="fas fa-edit"></i></button><button class="delete-comment-btn" onclick="askDeleteComment(<?= $comment['id'] ?>, <?= $post->getId() ?>)"><i class="fas fa-trash"></i></button></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="comment-input"><input type="text" id="comment-input-<?= $post->getId() ?>" placeholder="<?= $t('write_comment') ?>"><button class="btn-send" onclick="addCommentAjax(<?= $post->getId() ?>)"><i class="fas fa-paper-plane"></i></button></div>
                    <?php else: ?>
                    <div class="comment-input"><p><a href="#" onclick="openLoginModal(); return false;"><?= $t('login_to_comment') ?></a></p></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
    <aside class="sidebar-right">
        <div class="trend-card"><h4><i class="fas fa-fire" style="color: var(--accent);"></i> <?= $t('trends') ?></h4><div class="trend-item"><i class="fas fa-swimmer"></i> 🏛️ <?= $t('trend1') ?></div><div class="trend-item"><i class="fas fa-tree"></i> 🌿 <?= $t('trend2') ?></div><div class="trend-item"><i class="fas fa-bicycle"></i> 🚴‍♂️ <?= $t('trend3') ?></div></div>
        <div class="alert-card"><h4><i class="fas fa-bullhorn" style="color: var(--accent);"></i> <?= $t('announcements') ?></h4><div class="alert-item"><i class="fas fa-calendar-alt"></i> ⚠️ <?= $t('alert1') ?></div><div class="alert-item"><i class="fas fa-trash-alt"></i> ♻️ <?= $t('alert2') ?></div></div>
        <div class="ai-card"><h4><i class="fas fa-robot" style="color: var(--accent);"></i> <?= $t('ai_insights') ?></h4><p><?= $t('category') ?>: <strong><?= $t('mobility') ?></strong> <span style="background:rgba(47,160,132,0.2); padding:0.2rem 0.5rem; border-radius:0.5rem;"><?= $t('sentiment') ?> ↗️</span></p></div>
    </aside>
</div>

<!-- MODALES -->
<div id="loginModal" class="modal"><div class="modal-content"><div style="text-align:center; margin-bottom:1rem;"><img class="logo-icon" src="logo.png" alt="Logo" style="width:60px;"><h3 style="color:var(--accent);"><?= $t('login_title') ?></h3></div><?php if (!isset($_SESSION['user_id'])): ?><form method="POST" action="/projetweb/controllers/BlogController.php" onsubmit="return validateLogin()"><input type="hidden" name="action" value="login"><input type="email" name="email" id="loginEmail" placeholder="Email" required><input type="password" name="password" id="loginPassword" placeholder="<?= $t('login_title') ?>" required><div class="modal-buttons"><button type="button" class="modal-cancel" onclick="closeLoginModal()"><?= $t('cancel') ?></button><button type="submit" class="modal-save"><?= $t('login_btn') ?></button></div></form><?php else: ?><div style="text-align:center;"><div class="avatar-md" style="margin:0 auto 1rem; width:80px; height:80px;"><img src="<?= htmlspecialchars($sessionAvatar ?? 'https://randomuser.me/api/portraits/lego/1.jpg') ?>"></div><p><?= $t('logged_as') ?> <strong><?= $_SESSION['user_name'] ?></strong></p><form method="POST" action="/projetweb/controllers/BlogController.php"><input type="hidden" name="action" value="logout"><button type="submit" class="modal-save" style="background:var(--danger);"><?= $t('logout') ?></button></form></div><?php endif; ?></div></div>
<div id="editPostModal" class="modal"><div class="modal-content"><h3><?= $t('edit_post_title') ?></h3><form method="POST" action="/projetweb/controllers/BlogController.php"><input type="hidden" name="action" value="updatePost"><input type="hidden" name="post_id" id="edit_post_id"><textarea id="edit_post_content" name="content" rows="4" placeholder="<?= $t('write_comment') ?>"></textarea><div class="modal-buttons"><button type="button" class="modal-cancel" onclick="closeEditModal()"><?= $t('cancel') ?></button><button type="submit" class="modal-save"><?= $t('save') ?></button></div></form></div></div>
<div id="editCommentModal" class="modal"><div class="modal-content"><h3><?= $t('edit_comment_title') ?></h3><form method="POST" action="/projetweb/controllers/BlogController.php"><input type="hidden" name="action" value="updateComment"><input type="hidden" name="comment_id" id="edit_comment_id"><textarea id="edit_comment_content" name="content" rows="3" placeholder="<?= $t('write_comment') ?>"></textarea><div class="modal-buttons"><button type="button" class="modal-cancel" onclick="closeEditCommentModal()"><?= $t('cancel') ?></button><button type="submit" class="modal-save"><?= $t('save') ?></button></div></form></div></div>
<div id="confirmDeleteModal" class="modal"><div class="modal-content"><h3 id="confirmDeleteTitle"><?= $t('confirm_delete_title') ?></h3><p id="confirmDeleteMessage"><?= $t('confirm_delete_post') ?></p><div class="modal-buttons"><button class="modal-cancel" id="confirmDeleteCancel"><?= $t('cancel') ?></button><button class="modal-save" id="confirmDeleteConfirm" style="background:var(--danger);"><?= $t('delete') ?></button></div></div></div>
<div id="messageModal" class="modal"><div class="modal-content"><h3 id="messageModalTitle"><?= $t('ok') ?></h3><p id="messageModalText"></p><div class="modal-buttons"><button class="modal-save" id="messageModalOk"><?= $t('ok') ?></button></div></div></div>
<div id="reactionsModal" class="modal"><div class="modal-content" style="max-width:500px;"><h3><i class="fas fa-smile"></i> <?= $t('reactions_list') ?></h3><div id="reactionsListContainer" style="max-height:400px;overflow-y:auto;"><div style="text-align:center;"><i class="fas fa-spinner fa-pulse"></i> Chargement...</div></div><div class="modal-buttons"><button class="modal-cancel" onclick="closeReactionsModal()"><?= $t('close') ?></button></div></div></div>

<script>
// ========== UTILS ==========
let pendingDelete = { type: null, id: null, postId: null, cardElement: null };
function escapeHtml(str) { if (!str) return ''; return str.replace(/[&<>]/g, m => m === '&' ? '&amp;' : m === '<' ? '&lt;' : '&gt;'); }
function showToast(msg) { let t = document.createElement('div'); t.className = 'toast'; t.innerHTML = '<i class="fas fa-check-circle"></i> ' + msg; document.body.appendChild(t); setTimeout(() => t.remove(), 3000); }

// ========== RECHERCHE DYNAMIQUE ==========
const searchInput = document.getElementById('searchInput');
const searchResultsDiv = document.getElementById('searchResults');
let debounceTimer;
function performSearch() {
    const query = searchInput.value.trim();
    if (query.length < 2) { searchResultsDiv.classList.remove('show'); searchResultsDiv.innerHTML = ''; return; }
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetch(`/projetweb/controllers/BlogController.php?action=searchAjax&q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) { searchResultsDiv.innerHTML = '<div class="no-result">Aucun résultat</div>'; searchResultsDiv.classList.add('show'); return; }
                let html = '';
                data.forEach(post => { html += `<div class="search-result-item" onclick="window.location.href='#post-${post.id}'"><div class="search-result-avatar"><img src="${escapeHtml(post.avatar)}" style="width:100%;height:100%;object-fit:cover;"></div><div class="search-result-content"><div class="search-result-author">${escapeHtml(post.author)}</div><div class="search-result-text">${escapeHtml(post.content)}</div><div class="search-result-date">${post.created_at}</div></div></div>`; });
                searchResultsDiv.innerHTML = html;
                searchResultsDiv.classList.add('show');
            }).catch(err => console.error(err));
    }, 300);
}
searchInput.addEventListener('input', performSearch);
document.addEventListener('click', e => { if (!searchResultsDiv.contains(e.target) && e.target !== searchInput) searchResultsDiv.classList.remove('show'); });
document.getElementById('searchButton').addEventListener('click', () => { const q = searchInput.value.trim(); if (q !== '') window.location.href = `?search=${encodeURIComponent(q)}`; });
searchInput.addEventListener('keypress', e => { if (e.key === 'Enter') { e.preventDefault(); document.getElementById('searchButton').click(); } });

// ========== RÉACTIONS (POPUP MODERNE) ==========
function submitReaction(postId, type) {
    fetch('/projetweb/controllers/BlogController.php', {
        method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=ajaxReactToPost&post_id=${postId}&type=${type}`
    }).then(res => res.json()).then(data => {
        if (data.error) { showToast(data.error); return; }
        document.querySelector(`.reaction-count[data-post-id="${postId}"]`).innerText = data.total;
        document.querySelector(`.reaction-emoji[data-post-id="${postId}"]`).innerText = data.current_emoji;
        showToast('Réaction mise à jour');
    }).catch(err => console.error(err));
}
document.addEventListener('DOMContentLoaded', function() {
    // Ouvrir le popup
    document.querySelectorAll('.reaction-emoji').forEach(emoji => {
        emoji.addEventListener('click', e => {
            e.stopPropagation();
            const postId = emoji.getAttribute('data-post-id');
            const popup = document.getElementById(`reactionPopup-${postId}`);
            document.querySelectorAll('.reaction-popup.show').forEach(p => p.classList.remove('show'));
            popup.classList.toggle('show');
        });
    });
    // Afficher liste des réactions
    document.querySelectorAll('.reaction-count, .reaction-text').forEach(el => {
        el.addEventListener('click', e => {
            e.stopPropagation();
            const postId = el.getAttribute('data-post-id');
            showReactionsList(postId);
        });
    });
    // Sélection emoji avec animation press
    document.querySelectorAll('.reaction-option').forEach(opt => {
        opt.addEventListener('click', e => {
            e.stopPropagation();
            opt.style.transform = 'scale(0.8)';
            setTimeout(() => { opt.style.transform = ''; }, 150);
            const popup = opt.closest('.reaction-popup');
            const postId = popup.id.replace('reactionPopup-', '');
            const type = opt.getAttribute('data-type');
            submitReaction(postId, type);
            popup.classList.remove('show');
        });
    });
    // Fermer popup clic extérieur
    document.addEventListener('click', () => { document.querySelectorAll('.reaction-popup.show').forEach(p => p.classList.remove('show')); });
});

// ========== COMMENTAIRES AJAX ==========
function addCommentAjax(postId) {
    const input = document.getElementById(`comment-input-${postId}`);
    const content = input.value.trim();
    if (!content) { showToast('Le commentaire ne peut pas être vide'); return; }
    fetch('/projetweb/controllers/BlogController.php', {
        method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=ajaxCreateComment&post_id=${postId}&content=${encodeURIComponent(content)}`
    }).then(res => res.json()).then(data => {
        if (data.error) { showToast(data.error); return; }
        const commentList = document.getElementById(`comment-list-${postId}`);
        const newCommentHtml = `<div class="comment-item" data-comment-id="${data.comment_id}"><div class="comment-avatar"><img src="${escapeHtml(data.user_avatar)}"></div><div class="comment-content"><div class="comment-user-name">${escapeHtml(data.user_name)}</div><div class="comment-text">${data.content}</div></div><div class="comment-actions"><button class="edit-comment-btn" onclick="editCommentAjax(${data.comment_id}, '${escapeHtml(data.content.replace(/<br\s*\/?>/gi, '\n'))}')"><i class="fas fa-edit"></i></button><button class="delete-comment-btn" onclick="askDeleteComment(${data.comment_id}, ${postId})"><i class="fas fa-trash"></i></button></div></div>`;
        commentList.insertAdjacentHTML('beforeend', newCommentHtml);
        document.getElementById(`comment-count-${postId}`).innerText = `(${data.total_comments})`;
        input.value = '';
        showToast('Commentaire ajouté');
    }).catch(err => console.error(err));
}
function editCommentAjax(commentId, oldContent) {
    const newContent = prompt('Modifier votre commentaire :', oldContent);
    if (!newContent || newContent === oldContent) return;
    fetch('/projetweb/controllers/BlogController.php', {
        method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=ajaxUpdateComment&comment_id=${commentId}&content=${encodeURIComponent(newContent)}`
    }).then(res => res.json()).then(data => {
        if (data.error) { showToast(data.error); return; }
        const commentItem = document.querySelector(`.comment-item[data-comment-id="${commentId}"]`);
        commentItem.querySelector('.comment-text').innerHTML = data.content;
        showToast('Commentaire modifié');
    }).catch(err => console.error(err));
}

// ========== SUPPRESSION ==========
function askDeletePost(postId, cardElement) {
    pendingDelete = { type: 'post', id: postId, cardElement: cardElement };
    document.getElementById('confirmDeleteTitle').innerText = '<?= $t('confirm_delete_title') ?>';
    document.getElementById('confirmDeleteMessage').innerHTML = '<?= $t('confirm_delete_post') ?>';
    document.getElementById('confirmDeleteModal').classList.add('show');
}
function askDeleteComment(commentId, postId) {
    pendingDelete = { type: 'comment', id: commentId, postId: postId };
    document.getElementById('confirmDeleteTitle').innerText = '<?= $t('confirm_delete_title') ?>';
    document.getElementById('confirmDeleteMessage').innerHTML = '<?= $t('confirm_delete_comment') ?>';
    document.getElementById('confirmDeleteModal').classList.add('show');
}
function executeDelete() {
    const { type, id, postId, cardElement } = pendingDelete;
    if (!type || !id) return;
    if (type === 'post') {
        fetch('/projetweb/controllers/BlogController.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `action=ajaxDeletePost&post_id=${id}` })
        .then(res => res.json()).then(data => { if (data.error) showToast(data.error); else { if (cardElement) cardElement.remove(); showToast('Publication supprimée'); } closeConfirmModal(); });
    } else if (type === 'comment') {
        fetch('/projetweb/controllers/BlogController.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `action=ajaxDeleteComment&comment_id=${id}` })
        .then(res => res.json()).then(data => { if (data.error) showToast(data.error); else { document.querySelector(`.comment-item[data-comment-id="${id}"]`).remove(); document.getElementById(`comment-count-${postId}`).innerText = `(${data.total_comments})`; showToast('Commentaire supprimé'); } closeConfirmModal(); });
    }
}
function closeConfirmModal() { document.getElementById('confirmDeleteModal').classList.remove('show'); pendingDelete = {}; }
document.getElementById('confirmDeleteCancel').onclick = closeConfirmModal;
document.getElementById('confirmDeleteConfirm').onclick = executeDelete;

// ========== MODALE LISTE DES RÉACTIONS ==========
function showReactionsList(postId) {
    const modal = document.getElementById('reactionsModal');
    const container = document.getElementById('reactionsListContainer');
    container.innerHTML = '<div style="text-align:center;"><i class="fas fa-spinner fa-pulse"></i> Chargement...</div>';
    modal.classList.add('show');
    fetch(`/projetweb/controllers/BlogController.php?action=getReactionsList&post_id=${postId}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) { container.innerHTML = `<div class="no-result">${data.error}</div>`; return; }
            if (data.length === 0) { container.innerHTML = '<div class="no-result">Aucune réaction pour le moment.</div>'; return; }
            let html = '<div style="display:flex;flex-direction:column;gap:0.5rem;">';
            data.forEach(item => { html += `<div class="reaction-user-item"><div class="reaction-user-avatar"><img src="${escapeHtml(item.avatar)}" style="width:100%;height:100%;object-fit:cover;"></div><div class="reaction-user-name">${escapeHtml(item.name)}</div><div class="reaction-emoji-big">${item.emoji}</div></div>`; });
            html += '</div>';
            container.innerHTML = html;
        }).catch(err => { container.innerHTML = '<div class="no-result">Erreur de chargement.</div>'; console.error(err); });
}
function closeReactionsModal() { document.getElementById('reactionsModal').classList.remove('show'); }

// ========== AUTRES FONCTIONS ==========
function showMessageModal(title, message, isError = false) {
    const modal = document.getElementById('messageModal');
    document.getElementById('messageModalTitle').innerText = title;
    document.getElementById('messageModalText').innerText = message;
    document.getElementById('messageModalTitle').style.color = isError ? 'var(--danger)' : 'var(--accent)';
    modal.classList.add('show');
}
document.getElementById('messageModalOk').onclick = () => document.getElementById('messageModal').classList.remove('show');
function validatePost() { if (document.getElementById('postContent').value.trim() === '') { showMessageModal('<?= $t('ok') ?>', '<?= addslashes($t('error_empty_content')) ?>', true); return false; } return true; }
function validateLogin() { let email = document.getElementById('loginEmail').value.trim(), pwd = document.getElementById('loginPassword').value.trim(); if (email === '' || pwd === '') { showMessageModal('<?= $t('ok') ?>', '<?= addslashes($t('error_empty_fields')) ?>', true); return false; } return true; }
function openLoginModal() { document.getElementById('loginModal').classList.add('show'); }
function closeLoginModal() { document.getElementById('loginModal').classList.remove('show'); }
document.getElementById('profileBtn')?.addEventListener('click', openLoginModal);
function openEditModal(id, content) { document.getElementById('edit_post_id').value = id; document.getElementById('edit_post_content').value = content; document.getElementById('editPostModal').classList.add('show'); }
function closeEditModal() { document.getElementById('editPostModal').classList.remove('show'); }
function openEditCommentModal(id, content) { document.getElementById('edit_comment_id').value = id; document.getElementById('edit_comment_content').value = content; document.getElementById('editCommentModal').classList.add('show'); }
function closeEditCommentModal() { document.getElementById('editCommentModal').classList.remove('show'); }
function toggleComments(id) { let div = document.getElementById('comments-' + id); div.style.display = (div.style.display === 'none' || div.style.display === '') ? 'block' : 'none'; }
function sharePost() { navigator.clipboard.writeText(window.location.href); showToast('<?= addslashes($t('link_copied')) ?>'); }

// Gestion médias
let uploadImageBtn = document.getElementById('uploadImageBtn'), uploadVideoBtn = document.getElementById('uploadVideoBtn'), imageInput = document.getElementById('imageUploadInput'), videoInput = document.getElementById('videoUploadInput'), previewImg = document.getElementById('previewImg'), previewVideo = document.getElementById('previewVideo'), previewContainer = document.getElementById('mediaPreviewContainer'), removeBtn = document.getElementById('removeMediaBtn');
if (uploadImageBtn) uploadImageBtn.onclick = () => imageInput.click();
if (uploadVideoBtn) uploadVideoBtn.onclick = () => videoInput.click();
if (imageInput) imageInput.onchange = function(e) { let file = e.target.files[0]; if (file) { let reader = new FileReader(); reader.onload = ev => { previewImg.src = ev.target.result; previewImg.style.display = 'block'; previewVideo.style.display = 'none'; previewContainer.style.display = 'block'; removeBtn.style.display = 'block'; }; reader.readAsDataURL(file); } };
if (videoInput) videoInput.onchange = function(e) { let file = e.target.files[0]; if (file) { let reader = new FileReader(); reader.onload = ev => { previewVideo.src = ev.target.result; previewVideo.style.display = 'block'; previewImg.style.display = 'none'; previewContainer.style.display = 'block'; removeBtn.style.display = 'block'; }; reader.readAsDataURL(file); } };
if (removeBtn) removeBtn.onclick = function() { previewImg.style.display = 'none'; previewVideo.style.display = 'none'; previewContainer.style.display = 'none'; removeBtn.style.display = 'none'; if (imageInput) imageInput.value = ''; if (videoInput) videoInput.value = ''; };

// Synthèse vocale
let currentUtterance = null, currentElement = null;
function stopSpeaking() { if (window.speechSynthesis.speaking || window.speechSynthesis.pending) window.speechSynthesis.cancel(); if (currentElement) { currentElement.classList.remove('speaking'); currentElement = null; } currentUtterance = null; }
async function speakPostFromServer(postId, element) {
    if (currentElement === element && (window.speechSynthesis.speaking || window.speechSynthesis.pending)) { stopSpeaking(); return; }
    stopSpeaking();
    try {
        const response = await fetch(`/projetweb/controllers/BlogController.php?action=getSpeakText&post_id=${postId}`);
        const data = await response.json();
        if (data.error || !data.text) { showMessageModal("<?= addslashes($t('error')) ?>", data.error || "Texte non disponible", true); return; }
        const utterance = new SpeechSynthesisUtterance(data.text);
        utterance.lang = data.lang; utterance.rate = data.rate; utterance.pitch = data.pitch;
        element.classList.add('speaking');
        currentElement = element;
        currentUtterance = utterance;
        utterance.onend = () => { element.classList.remove('speaking'); currentElement = null; currentUtterance = null; };
        utterance.onerror = () => { element.classList.remove('speaking'); currentElement = null; currentUtterance = null; showMessageModal("<?= addslashes($t('error')) ?>", "Synthèse vocale non disponible.", true); };
        window.speechSynthesis.speak(utterance);
    } catch (err) { showMessageModal("<?= addslashes($t('error')) ?>", "Erreur technique.", true); }
}
document.querySelectorAll('.post-card').forEach(card => { const postId = card.getAttribute('data-post-id'); const postText = card.querySelector('.post-text'); if (postText && postId) { postText.style.cursor = 'pointer'; postText.addEventListener('click', (e) => { e.stopPropagation(); speakPostFromServer(postId, postText); }); } });

window.onclick = function(e) {
    if (e.target === document.getElementById('confirmDeleteModal')) closeConfirmModal();
    if (e.target === document.getElementById('messageModal')) document.getElementById('messageModal').classList.remove('show');
    if (e.target === document.getElementById('loginModal')) closeLoginModal();
    if (e.target === document.getElementById('editPostModal')) closeEditModal();
    if (e.target === document.getElementById('editCommentModal')) closeEditCommentModal();
    if (e.target === document.getElementById('reactionsModal')) closeReactionsModal();
};
</script>
</body>
</html>