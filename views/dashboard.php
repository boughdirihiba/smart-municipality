<?php
session_start();

// ========== GESTION DES PRÉFÉRENCES ==========
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en', 'ar'])) {
    $_SESSION['app_lang'] = $_GET['lang'];
    $url = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $url);
    exit;
}
if (isset($_GET['theme']) && in_array($_GET['theme'], ['light', 'dark'])) {
    $_SESSION['user_theme'] = $_GET['theme'];
    $url = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $url);
    exit;
}
if (isset($_GET['size']) && is_numeric($_GET['size'])) {
    $size = min(130, max(80, (int)$_GET['size']));
    $_SESSION['font_size'] = $size;
    $url = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $url);
    exit;
}

$current_lang = $_SESSION['app_lang'] ?? 'fr';
$current_theme = $_SESSION['user_theme'] ?? 'light';
$current_font_size = $_SESSION['font_size'] ?? 100;

require_once __DIR__ . '/../controllers/BlogController.php';
$blogController = new BlogController();
$t = fn($key) => $blogController->t($key);
$is_rtl = ($blogController->getCurrentLang() === 'ar');
$adminAvatar = $_SESSION['user_avatar'] ?? 'https://randomuser.me/api/portraits/lego/1.jpg';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "Accès réservé aux administrateurs.";
    header('Location: /smart/smart-municipality/views/frontoffice.php');
    exit();
}

require_once __DIR__ . '/../controllers/DashboardController.php';
$controller = new DashboardController();
$stats = $controller->getStats();
$posts = $controller->getAllPosts();
$comments = $controller->getAllComments();
$success_message = $_SESSION['success'] ?? '';
$error_message = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$BASE_URL = '/smart/smart-municipality';

// Fonction d'avatar fix (identique à frontoffice)
function avatar_fix($avatar, $base_url) {
    if (empty($avatar)) return 'https://randomuser.me/api/portraits/lego/1.jpg';
    if (strpos($avatar, '://') !== false) return $avatar;
    if (strpos($avatar, 'data:') === 0) return $avatar;
    return rtrim($base_url, '/') . '/' . ltrim($avatar, '/');
}
$adminAvatar = avatar_fix($adminAvatar, $BASE_URL);
?>

<!DOCTYPE html>
<html lang="<?= $current_lang ?>" dir="<?= $is_rtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Smart Municipality | <?= $t('dashboard') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if ($is_rtl): ?>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* ========== VARIABLES MODERNES ========== */
        :root {
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --border-light: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --hover-bg: #f1f5f9;
            --accent: #10b981;
            --accent-dark: #059669;
            --accent-light: #34d399;
            --danger: #ef4444;
            --danger-dark: #dc2626;
            --warning: #f59e0b;
            --info: #3b82f6;
            --purple: #8b5cf6;
            --pink: #ec4899;
        }
        body.theme-dark {
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border-light: #334155;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.3);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.4);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.4);
            --hover-bg: #334155;
            --accent: #34d399;
            --accent-dark: #10b981;
        }
        body {
            background: var(--bg-body);
            color: var(--text-primary);
            font-family: <?= $is_rtl ? "'Cairo', 'Inter', sans-serif" : "'Inter', sans-serif" ?>;
            margin: 0;
            padding: 0;
            transition: background 0.3s, color 0.2s;
        }
        <?php if ($is_rtl): ?>
        body { direction: rtl; }
        .sidebar .nav-item i { margin-left: 0.8rem; margin-right: 0; }
        .post-actions-buttons { left: 1rem; right: auto; }
        .comment-actions { right: auto; left: 0; }
        .section-header { border-right: 4px solid var(--accent); border-left: none; padding-right: 0.8rem; padding-left: 0; }
        <?php endif; ?>

        /* ========== SIDEBAR MODERN ========== */
        .dashboard { display: flex; min-height: 100vh; }
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #065f46 0%, #047857 100%);
            backdrop-filter: blur(10px);
            color: #ecfdf5;
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 1.5rem;
            box-shadow: var(--shadow-lg);
            overflow-y: auto;
            border-radius: 0 1.5rem 1.5rem 0;
        }
        .sidebar .logo-area {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .sidebar .logo-icon {
            width: 70px;
            border-radius: 20px;
            background: white;
            padding: 6px;
            box-shadow: var(--shadow-md);
        }
        .sidebar .logo-text .smart {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: white;
        }
        .sidebar .nav-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.7rem 1rem;
            border-radius: 1rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            color: #d1fae5;
            font-weight: 500;
        }
        .sidebar .nav-item i { width: 24px; font-size: 1.2rem; }
        .sidebar .nav-item:hover, .sidebar .nav-item.active {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(<?= $is_rtl ? '-6px' : '6px' ?>);
            backdrop-filter: blur(4px);
        }

        /* ========== MAIN CONTENT ========== */
        .main-content { flex: 1; padding: 1.5rem; overflow-x: auto; }

        /* Top bar avec verre dépoli */
        .top-bar {
            background: var(--bg-card);
            border-radius: 1.2rem;
            padding: 0.8rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-light);
            backdrop-filter: blur(4px);
        }
        .page-title {
            font-size: 1.3rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .control-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            background: var(--hover-bg);
            border-radius: 2rem;
            padding: 0.2rem 0.6rem;
        }
        .control-btn {
            background: transparent;
            border: none;
            padding: 0.3rem 0.7rem;
            border-radius: 1.5rem;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            text-decoration: none;
            font-size: 0.85rem;
            transition: 0.2s;
        }
        .control-btn:hover, .lang-selector .control-btn.active {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: white;
            box-shadow: var(--shadow-sm);
        }
        .font-size-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--hover-bg);
            padding: 0.2rem 0.8rem;
            border-radius: 2rem;
        }
        .font-size-control input {
            width: 100px;
            cursor: pointer;
            background: var(--bg-card);
            border-radius: 1rem;
        }

        /* Cartes statistiques colorées */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--bg-card);
            border-radius: 1.2rem;
            padding: 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        .stat-info h3 {
            font-size: 2rem;
            font-weight: 800;
            margin: 0;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .stat-info p { margin: 0; color: var(--text-secondary); font-weight: 500; }
        .stat-icon {
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, var(--accent-light), var(--accent));
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            box-shadow: var(--shadow-sm);
        }

        /* Graphiques */
        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .chart-card {
            background: var(--bg-card);
            border-radius: 1.2rem;
            padding: 1.2rem;
            border: 1px solid var(--border-light);
            transition: 0.2s;
        }
        .chart-card.full-width { grid-column: 1 / -1; }
        .chart-container { position: relative; height: 280px; width: 100%; }

        /* Sections de données */
        .data-section {
            background: var(--bg-card);
            border-radius: 1.2rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-light);
            transition: 0.2s;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.2rem;
            border-left: 4px solid var(--accent);
            padding-left: 0.8rem;
        }
        .section-header h3 { margin: 0; font-size: 1.1rem; font-weight: 600; }

        /* Grille posts modernes */
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        .post-card {
            background: var(--bg-card);
            border-radius: 1.2rem;
            overflow: hidden;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
        }
        .post-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
        }
        .post-media {
            background: var(--hover-bg);
            text-align: center;
            padding: 1rem;
            border-bottom: 1px solid var(--border-light);
            position: relative;
        }
        .post-media img, .post-media video {
            max-width: 100%;
            max-height: 180px;
            object-fit: contain;
            border-radius: 0.8rem;
        }
        .media-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            border-radius: 2rem;
            padding: 0.2rem 0.6rem;
            font-size: 0.7rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .post-content { padding: 1rem; }
        .post-text {
            cursor: pointer;
            line-height: 1.5;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        .post-text.speaking {
            background: linear-gradient(120deg, var(--accent-light), var(--accent));
            color: white;
            border-radius: 0.5rem;
            padding: 0.2rem;
        }
        .listen-post-btn {
            background: var(--hover-bg);
            border: none;
            color: var(--accent);
            cursor: pointer;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.3rem 0.7rem;
            border-radius: 2rem;
            transition: 0.2s;
        }
        .listen-post-btn:hover {
            background: var(--accent);
            color: white;
        }
        .post-meta {
            display: flex;
            justify-content: space-between;
            padding: 0.6rem 1rem;
            font-size: 0.7rem;
            border-top: 1px solid var(--border-light);
            color: var(--text-secondary);
        }
        .post-actions {
            display: flex;
            gap: 0.5rem;
            padding: 0.8rem 1rem 1rem;
        }
        /* Boutons modernes avec icônes colorées */
        .btn-icon {
            background: transparent;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-edit-card {
            background: rgba(59,130,246,0.1);
            color: #3b82f6;
        }
        .btn-edit-card:hover {
            background: #3b82f6;
            color: white;
            transform: translateY(-2px);
        }
        .btn-delete-card {
            background: rgba(239,68,68,0.1);
            color: #ef4444;
        }
        .btn-delete-card:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 2rem;
            cursor: pointer;
            transition: 0.2s;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Tableau moderne */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            padding: 0.8rem;
            text-align: <?= $is_rtl ? 'right' : 'left' ?>;
            border-bottom: 1px solid var(--border-light);
        }
        .data-table th {
            background: var(--hover-bg);
            color: var(--accent);
            font-weight: 600;
        }
        .data-table tr:hover td {
            background: var(--hover-bg);
        }

        /* Formulaires */
        input, textarea, select {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 1rem;
            padding: 0.7rem 1rem;
            width: 100%;
            margin-bottom: 1rem;
            color: var(--text-primary);
            transition: 0.2s;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
        }
        button[type="submit"] {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 2rem;
            cursor: pointer;
            transition: 0.2s;
            font-weight: 600;
        }
        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Modales */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }
        .modal.show { display: flex; }
        .modal-content {
            background: var(--bg-card);
            border-radius: 1.5rem;
            padding: 1.8rem;
            width: 90%;
            max-width: 600px;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-lg);
        }
        .modal-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        .alert-message {
            padding: 0.8rem 1rem;
            border-radius: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .alert-success {
            background: rgba(16,185,129,0.15);
            color: #10b981;
            border-left: 4px solid #10b981;
        }
        .alert-error {
            background: rgba(239,68,68,0.15);
            color: #ef4444;
            border-left: 4px solid #ef4444;
        }

        @media (max-width: 768px) {
            .dashboard { flex-direction: column; }
            .sidebar { width: 100%; height: auto; border-radius: 0 0 1.5rem 1.5rem; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .charts-section { grid-template-columns: 1fr; }
            .chart-card.full-width { grid-column: auto; }
        }
    </style>
</head>
<body class="theme-<?= $current_theme ?>" style="font-size: <?= $current_font_size ?>%;">
<div class="dashboard">
    <aside class="sidebar">
        <div class="logo-area">
            <img class="logo-icon" src="<?= $BASE_URL ?>/logo.png" alt="Logo" onerror="this.src='https://placehold.co/70x70/10b981/white?text=SM'">
            <div class="logo-text"><div class="smart">Smart</div><div class="municipality">Municipality</div></div>
        </div>
        <div class="nav-menu">
            <div class="nav-item active"><i class="fas fa-user-circle"></i><span><?= $t('dashboard') ?></span></div>
            <div class="nav-item"><i class="fas fa-chart-line"></i><span><?= $t('profile') ?></span></div>
            <div class="nav-item"><i class="fas fa-calendar-alt"></i><span><?= $t('events') ?></span></div>
            <div class="nav-item"><i class="fas fa-map-marked-alt"></i><span><?= $t('map') ?></span></div>
            <div class="nav-item"><i class="fas fa-blog"></i><span><?= $t('blog') ?></span></div>
            <div class="nav-item"><i class="fas fa-concierge-bell"></i><span><?= $t('services') ?></span></div>
            <div class="nav-item"><i class="fas fa-calendar-check"></i><span><?= $t('appointments') ?></span></div>
        </div>
    </aside>
    <main class="main-content">
        <div class="top-bar">
            <div class="page-title"><i class="fas fa-chart-line"></i> <?= $t('dashboard') ?></div>
            <div style="display: flex; gap: 0.8rem; align-items: center; flex-wrap: wrap;">
                <div class="font-size-control">
                    <span>A-</span>
                    <form method="GET" style="display: inline;">
                        <input type="range" name="size" min="80" max="130" value="<?= $current_font_size ?>" step="1" onchange="this.form.submit()">
                    </form>
                    <span>A+</span>
                </div>
                <div class="control-group lang-selector">
                    <a href="?lang=fr" class="control-btn <?= $current_lang === 'fr' ? 'active' : '' ?>">FR</a>
                    <a href="?lang=en" class="control-btn <?= $current_lang === 'en' ? 'active' : '' ?>">EN</a>
                    <a href="?lang=ar" class="control-btn <?= $current_lang === 'ar' ? 'active' : '' ?>">AR</a>
                </div>
                <a href="?theme=<?= $current_theme === 'light' ? 'dark' : 'light' ?>" class="control-btn"><i class="fas <?= $current_theme === 'light' ? 'fa-moon' : 'fa-sun' ?>"></i></a>
                <a href="<?= $BASE_URL ?>/index.php?action=blog" class="control-btn" style="background: var(--accent); color: white;"><i class="fas fa-blog"></i> <?= $t('blog') ?></a>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <img src="<?= htmlspecialchars($adminAvatar) ?>" style="width: 32px; height: 32px; border-radius: 50%; border: 2px solid var(--accent);">
                    <span><?= $_SESSION['user_name'] ?></span>
                </div>
            </div>
        </div>

        <?php if ($success_message): ?><div class="alert-message alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?></div><?php endif; ?>
        <?php if ($error_message): ?><div class="alert-message alert-error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?></div><?php endif; ?>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-info"><h3><?= $stats['totalPosts'] ?></h3><p><?= $t('total_posts') ?></p></div><div class="stat-icon"><i class="fas fa-newspaper"></i></div></div>
            <div class="stat-card"><div class="stat-info"><h3><?= $stats['totalUsers'] ?></h3><p><?= $t('total_users') ?></p></div><div class="stat-icon"><i class="fas fa-users"></i></div></div>
            <div class="stat-card"><div class="stat-info"><h3><?= $stats['totalComments'] ?></h3><p><?= $t('total_comments') ?></p></div><div class="stat-icon"><i class="fas fa-comments"></i></div></div>
            <div class="stat-card"><div class="stat-info"><h3><?= $stats['totalReactions'] ?></h3><p><?= $t('total_reactions') ?></p></div><div class="stat-icon"><i class="fas fa-heart"></i></div></div>
        </div>

        <!-- Graphiques -->
        <div class="charts-section">
            <div class="chart-card"><h3><i class="fas fa-chart-bar" style="color: var(--accent);"></i> <?= $t('posts_by_day') ?></h3><div class="chart-container"><canvas id="barChart"></canvas></div></div>
            <div class="chart-card"><h3><i class="fas fa-chart-pie" style="color: var(--accent);"></i> <?= $t('media_distribution') ?></h3><div class="chart-container"><canvas id="pieChart"></canvas></div></div>
            <div class="chart-card full-width"><h3><i class="fas fa-chart-line" style="color: var(--accent);"></i> <?= $t('activity_30d') ?></h3><div class="chart-container"><canvas id="lineChart"></canvas></div></div>
        </div>

        <!-- Créer un post -->
        <div class="data-section">
            <div class="section-header"><h3><i class="fas fa-plus-circle" style="color: var(--accent);"></i> <?= $t('create_post') ?></h3></div>
            <form id="createPostForm" method="POST" action="<?= $BASE_URL ?>/index.php?action=createPost" enctype="multipart/form-data">
                <input type="hidden" name="action" value="createPost">
                <textarea name="content" rows="3" placeholder="<?= $t('write_comment') ?>" required></textarea>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <input type="file" name="image" accept="image/*">
                    <input type="file" name="video" accept="video/*">
                    <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> <?= $t('publish') ?></button>
                </div>
                <small id="fileError" style="color: var(--danger); display: none;"></small>
            </form>
        </div>

        <!-- Ajouter un commentaire -->
        <div class="data-section">
            <div class="section-header"><h3><i class="fas fa-comment-dots" style="color: var(--accent);"></i> <?= $t('add_comment') ?></h3></div>
            <form id="createCommentForm" method="POST" action="<?= $BASE_URL ?>/index.php?action=createComment">
                <input type="hidden" name="action" value="createComment">
                <select name="post_id" required>
                    <option value=""><?= $t('choose_post') ?></option>
                    <?php foreach ($posts as $post): ?>
                        <option value="<?= $post['id'] ?>"><?= $t('post') ?> #<?= $post['id'] ?> - <?= htmlspecialchars(substr($post['content'],0,50)) ?></option>
                    <?php endforeach; ?>
                </select>
                <textarea name="content" rows="2" placeholder="<?= $t('write_comment') ?>" required></textarea>
                <button type="submit" class="btn-primary"><i class="fas fa-comment"></i> <?= $t('comment') ?></button>
            </form>
        </div>

        <!-- Tous les posts -->
        <div class="data-section">
            <div class="section-header"><h3><i class="fas fa-images" style="color: var(--accent);"></i> <?= $t('all_posts') ?></h3><span><i class="fas fa-database"></i> <?= count($posts) ?> <?= $t('items') ?></span></div>
            <div class="posts-grid">
                <?php foreach ($posts as $post): ?>
                <div class="post-card" data-post-id="<?= $post['id'] ?>">
                    <div class="post-media">
                        <?php if (!empty($post['image']) && strpos($post['image'], 'data:image') === 0): ?>
                            <img src="<?= $post['image'] ?>" alt="Image">
                            <div class="media-badge"><i class="fas fa-image"></i> <?= $t('image') ?></div>
                        <?php elseif (!empty($post['video']) && strpos($post['video'], 'data:video') === 0): ?>
                            <video controls src="<?= $post['video'] ?>"></video>
                            <div class="media-badge"><i class="fas fa-video"></i> <?= $t('video') ?></div>
                        <?php else: ?>
                            <div><i class="fas fa-file-alt" style="color: var(--text-secondary);"></i> <?= $t('text_only') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="post-content">
                        <div class="post-text" data-post-id="<?= $post['id'] ?>"><?= nl2br(htmlspecialchars(substr($post['content'],0,200))) ?><?= strlen($post['content']) > 200 ? '…' : '' ?></div>
                        <button class="listen-post-btn" onclick="speakPost(<?= $post['id'] ?>, document.querySelector('.post-card[data-post-id=\'<?= $post['id'] ?>\'] .post-text'))"><i class="fas fa-headphones"></i> <?= $t('listen') ?></button>
                    </div>
                    <div class="post-meta">
                        <span><i class="fas fa-user-circle"></i> <?= htmlspecialchars($post['user_name']) ?></span>
                        <span><i class="far fa-calendar-alt"></i> <?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></span>
                        <span><i class="fas fa-comment"></i> <?= $post['comments_count'] ?></span>
                    </div>
                    <div class="post-actions">
                        <button class="btn-icon btn-edit-card" onclick="openEditPostModal(<?= $post['id'] ?>, '<?= addslashes($post['content']) ?>', '<?= addslashes($post['image'] ?? '') ?>', '<?= addslashes($post['video'] ?? '') ?>')"><i class="fas fa-edit"></i> <?= $t('edit') ?></button>
                        <form method="POST" action="<?= $BASE_URL ?>/index.php?action=deletePost" onsubmit="return confirm('<?= addslashes($t('confirm_delete_post')) ?>');">
                            <input type="hidden" name="action" value="deletePost">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <button type="submit" class="btn-icon btn-delete-card"><i class="fas fa-trash-alt"></i> <?= $t('delete') ?></button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Commentaires récents -->
        <div class="data-section">
            <div class="section-header"><h3><i class="fas fa-comments" style="color: var(--accent);"></i> <?= $t('recent_comments') ?></h3></div>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead><tr><th>ID</th><th><?= $t('author') ?></th><th><?= $t('post') ?></th><th><?= $t('comment') ?></th><th><?= $t('date') ?></th><th><?= $t('actions') ?></th></tr></thead>
                    <tbody>
                        <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td><?= $comment['id'] ?></td>
                            <td><?= htmlspecialchars($comment['user_name']) ?></td>
                            <td><?= htmlspecialchars(substr($comment['post_content'],0,40)) ?>...</td>
                            <td><?= htmlspecialchars(substr($comment['content'],0,70)) ?>...</td>
                            <td><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></td>
                            <td class="action-btns">
                                <button class="btn-icon" style="background: none; color: #3b82f6;" onclick="openEditCommentModalAdmin(<?= $comment['id'] ?>, '<?= addslashes($comment['content']) ?>')"><i class="fas fa-edit"></i></button>
                                <form method="POST" action="<?= $BASE_URL ?>/index.php?action=deleteComment" style="display:inline;" onsubmit="return confirm('<?= addslashes($t('confirm_delete_comment')) ?>');">
                                    <input type="hidden" name="action" value="deleteComment">
                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                    <button type="submit" class="btn-icon" style="background: none; color: var(--danger);"><i class="fas fa-trash"></i></button>
                                </form>
                             </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Modale édition post -->
<div id="editPostModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-edit" style="color: var(--accent);"></i> <?= $t('edit_post_title') ?></h3>
        <form id="editPostForm" method="POST" action="<?= $BASE_URL ?>/index.php?action=updatePost" enctype="multipart/form-data">
            <input type="hidden" name="action" value="updatePost">
            <input type="hidden" name="post_id" id="edit_post_id">
            <textarea id="edit_post_content" name="content" rows="4" required></textarea>
            <div><strong><?= $t('current_media') ?> :</strong><div id="currentImagePreview"></div><div id="currentVideoPreview"></div></div>
            <input type="file" name="image" accept="image/*" id="edit_image">
            <input type="file" name="video" accept="video/*" id="edit_video">
            <div class="modal-buttons">
                <button type="button" class="btn-icon" onclick="closeEditPostModal()"><?= $t('cancel') ?></button>
                <button type="submit" class="btn-primary"><?= $t('save') ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Modale édition commentaire -->
<div id="editCommentModalAdmin" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-comment-edit" style="color: var(--accent);"></i> <?= $t('edit_comment_title') ?></h3>
        <form id="editCommentForm" method="POST" action="<?= $BASE_URL ?>/index.php?action=updateComment">
            <input type="hidden" name="action" value="updateComment">
            <input type="hidden" name="comment_id" id="edit_comment_id_admin">
            <textarea id="edit_comment_content_admin" name="content" rows="3" required></textarea>
            <div class="modal-buttons">
                <button type="button" class="btn-icon" onclick="closeEditCommentModalAdmin()"><?= $t('cancel') ?></button>
                <button type="submit" class="btn-primary"><?= $t('save') ?></button>
            </div>
        </form>
    </div>
</div>

<script>
    // Graphiques
    const postsByDay = <?= json_encode($stats['postsByDay']) ?>;
    const contentDist = <?= json_encode($stats['contentDistribution']) ?>;
    const activityTimeline = <?= json_encode($stats['activityTimeline']) ?>;

    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: { labels: postsByDay.map(i=>i.date.substring(5)), datasets: [{ label: 'Publications', data: postsByDay.map(i=>i.count), backgroundColor: '#10b981', borderRadius: 8, barPercentage: 0.6 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
    });
    new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: { labels: ['<?= addslashes($t('with_image')) ?>','<?= addslashes($t('with_video')) ?>','<?= addslashes($t('text_only')) ?>'], datasets: [{ data: [contentDist.with_image, contentDist.with_video, contentDist.text_only], backgroundColor: ['#10b981','#f59e0b','#8b5cf6'] }] },
        options: { responsive: true, maintainAspectRatio: false }
    });
    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: { labels: activityTimeline.map(i=>i.date.substring(5)), datasets: [{ label: 'Posts créés', data: activityTimeline.map(i=>i.posts_count), borderColor: '#10b981', tension: 0.3, fill: true, backgroundColor: 'rgba(16,185,129,0.05)' }] },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Synthèse vocale
    let currentUtterance = null, currentElement = null;
    function stopSpeaking() { if (window.speechSynthesis.speaking || window.speechSynthesis.pending) window.speechSynthesis.cancel(); if (currentElement) { currentElement.classList.remove('speaking'); currentElement = null; } currentUtterance = null; }
    async function speakPost(postId, element) {
        if (currentElement === element && (window.speechSynthesis.speaking || window.speechSynthesis.pending)) { stopSpeaking(); return; }
        stopSpeaking();
        try {
            const response = await fetch('<?= $BASE_URL ?>/index.php?action=getSpeakText&post_id=' + postId);
            const data = await response.json();
            if (data.error || !data.text) { alert(data.error || "Texte non disponible"); return; }
            const utterance = new SpeechSynthesisUtterance(data.text);
            utterance.lang = data.lang; utterance.rate = data.rate; utterance.pitch = data.pitch;
            if (data.lang === 'ar-SA') {
                let voices = window.speechSynthesis.getVoices();
                if (voices.length === 0) window.speechSynthesis.addEventListener('voiceschanged', () => { setVoice(utterance, element); });
                else setVoice(utterance, element);
            } else startSpeech(utterance, element);
        } catch(e) { alert("Erreur technique."); }
    }
    function setVoice(utterance, element) {
        let voices = window.speechSynthesis.getVoices();
        let arabicVoice = voices.find(v => v.lang === 'ar-SA' || v.lang.startsWith('ar'));
        if (arabicVoice) utterance.voice = arabicVoice;
        startSpeech(utterance, element);
    }
    function startSpeech(utterance, element) {
        element.classList.add('speaking'); currentElement = element;
        utterance.onend = () => { element.classList.remove('speaking'); currentElement = null; };
        utterance.onerror = () => { element.classList.remove('speaking'); currentElement = null; };
        window.speechSynthesis.speak(utterance);
    }

    // Formulaires et modales
    document.getElementById('createPostForm')?.addEventListener('submit', function(e) {
        const content = this.querySelector('textarea[name="content"]').value.trim();
        const image = this.querySelector('input[name="image"]').files[0];
        const video = this.querySelector('input[name="video"]').files[0];
        const errorSpan = document.getElementById('fileError');
        if (!content) { alert("<?= addslashes($t('error_empty_content')) ?>"); e.preventDefault(); return; }
        if (image && video) { errorSpan.textContent = "<?= addslashes($t('error_image_video_both')) ?>"; errorSpan.style.display = 'block'; e.preventDefault(); return; }
        errorSpan.style.display = 'none';
    });
    document.getElementById('createCommentForm')?.addEventListener('submit', function(e) {
        if (!this.querySelector('textarea[name="content"]').value.trim()) { alert("<?= addslashes($t('error_empty_comment')) ?>"); e.preventDefault(); }
        else if (!this.querySelector('select[name="post_id"]').value) { alert("<?= addslashes($t('error_select_post')) ?>"); e.preventDefault(); }
    });
    function openEditPostModal(id, content, img, vid) {
        document.getElementById('edit_post_id').value = id;
        document.getElementById('edit_post_content').value = content;
        document.getElementById('currentImagePreview').innerHTML = (img && img.startsWith('data:image')) ? `<img src="${img}" style="max-height:100px;"><br><small><i class="fas fa-image"></i> <?= addslashes($t('current_image')) ?></small>` : '<em><i class="fas fa-ban"></i> <?= addslashes($t('no_image')) ?></em>';
        document.getElementById('currentVideoPreview').innerHTML = (vid && vid.startsWith('data:video')) ? `<video controls src="${vid}" style="max-height:100px;"></video><br><small><i class="fas fa-video"></i> <?= addslashes($t('current_video')) ?></small>` : '<em><i class="fas fa-ban"></i> <?= addslashes($t('no_video')) ?></em>';
        document.getElementById('editPostModal').classList.add('show');
    }
    function closeEditPostModal() { document.getElementById('editPostModal').classList.remove('show'); }
    function openEditCommentModalAdmin(id, content) {
        document.getElementById('edit_comment_id_admin').value = id;
        document.getElementById('edit_comment_content_admin').value = content;
        document.getElementById('editCommentModalAdmin').classList.add('show');
    }
    function closeEditCommentModalAdmin() { document.getElementById('editCommentModalAdmin').classList.remove('show'); }
    window.onclick = function(e) {
        if (e.target === document.getElementById('editPostModal')) closeEditPostModal();
        if (e.target === document.getElementById('editCommentModalAdmin')) closeEditCommentModalAdmin();
    };
</script>
</body>
</html>