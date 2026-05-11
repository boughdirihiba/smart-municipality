<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title ?? APP_NAME); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <style>
        .accessibility-panel {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            align-items: center;
            justify-content: flex-end;
            margin-bottom: 0.4rem;
        }
        .accessibility-group {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            flex-wrap: wrap;
        }
        .accessibility-label {
            color: #fff;
            font-size: 0.82rem;
            opacity: 0.8;
        }
        .accessibility-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 0.7rem;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.35);
            color: #fff;
            text-decoration: none;
            font-size: 0.85rem;
            background: rgba(255,255,255,0.08);
            transition: background 0.2s, transform 0.2s;
        }
        .accessibility-btn:hover,
        .accessibility-btn.active {
            background: rgba(255,255,255,0.2);
            transform: translateY(-1px);
        }
        @media (max-width: 900px) {
            .accessibility-panel {
                justify-content: center;
                width: 100%;
            }
            .accessibility-group {
                justify-content: center;
            }
        }
    </style>
</head>
<?php $currentRoute = $_GET['route'] ?? 'home/index'; ?>
<?php $currentUser = $_SESSION['user'] ?? []; ?>
<?php $userRole = $currentUser['role'] ?? 'citoyen'; ?>
<?php $displayName = trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? '')); ?>
<?php if ($displayName === '') { $displayName = (string)($currentUser['name'] ?? ($_SESSION['user_name'] ?? 'Utilisateur')); } ?>
<?php $logoutUrl = BASE_URL . '/index.php?route=auth/logout'; ?>
<?php $currentTheme = $_SESSION['user_theme'] ?? 'light'; ?>
<?php $currentFontSize = $_SESSION['font_size'] ?? 100; ?>
<?php $redirectUrl = rawurlencode($_SERVER['REQUEST_URI'] ?? '/index.php?action=blog'); ?>
<?php $darkModeClass = ($currentTheme === 'dark') ? 'dark-mode' : ''; ?>
<body class="role-<?php echo $userRole; ?> theme-<?php echo $currentTheme; ?> <?php echo $darkModeClass; ?>" style="font-size: <?php echo $currentFontSize; ?>%;">
<nav class="navbar" id="navbar">
    <a class="nav-brand" href="<?php echo BASE_URL; ?>/index.php?route=home/index">
        <img src="<?php echo BASE_URL; ?>/public/uploads/sidebar-photo.svg" alt="Logo Smart Municipality">
        <span class="nav-brand-text">Smart <span>Municipality</span></span>
    </a>
    <button class="mobile-toggle" type="button" aria-label="Ouvrir le menu" onclick="document.querySelector('.nav-links').classList.toggle('open')">
        <span></span><span></span><span></span>
    </button>
    <ul class="nav-links">
        <li><a href="<?php echo BASE_URL; ?>/index.php?route=profile" class="<?php echo $currentRoute === 'profile' ? 'active' : ''; ?>">Profil</a></li>
        <li><a href="<?php echo BASE_URL; ?>/index.php?action=evenements" class="<?php echo ($_GET['action'] ?? '') === 'evenements' ? 'active' : ''; ?>">Événements</a></li>
        <li><a href="<?php echo BASE_URL; ?>/index.php?route=home/index" class="<?php echo $currentRoute === 'home/index' && empty($_GET['action']) ? 'active' : ''; ?>">Carte</a></li>
        <li><a href="<?php echo BASE_URL; ?>/index.php?action=blog" class="<?php echo ($_GET['action'] ?? '') === 'blog' ? 'active' : ''; ?>">Blog</a></li>
        <?php if ($userRole === 'admin'): ?>
            <li><a href="<?php echo BASE_URL; ?>/index.php?action=dashboard" class="<?php echo ($_GET['action'] ?? '') === 'dashboard' ? 'active' : ''; ?>">Dashboard</a></li>
        <?php endif; ?>
        <li><a href="<?php echo BASE_URL; ?>/index.php?action=manage" class="<?php echo ($_GET['action'] ?? '') === 'manage' ? 'active' : ''; ?>">Demandes</a></li>
        <li><a href="<?php echo BASE_URL; ?>/index.php?action=rendez_vous" class="<?php echo ($_GET['action'] ?? '') === 'rendez_vous' ? 'active' : ''; ?>">Rendez-vous</a></li>
    </ul>
    <div class="nav-right">
        <div class="accessibility-panel">
            <div class="accessibility-group">
                <span class="accessibility-label">Lang</span>
                <a class="accessibility-btn <?php echo (($_GET['lang'] ?? $_SESSION['app_lang'] ?? 'fr') === 'fr') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/index.php?action=setLanguage&lang=fr&amp;redirect=<?php echo $redirectUrl; ?>">FR</a>
                <a class="accessibility-btn <?php echo (($_GET['lang'] ?? $_SESSION['app_lang'] ?? 'fr') === 'en') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/index.php?action=setLanguage&lang=en&amp;redirect=<?php echo $redirectUrl; ?>">EN</a>
                <a class="accessibility-btn <?php echo (($_GET['lang'] ?? $_SESSION['app_lang'] ?? 'fr') === 'ar') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/index.php?action=setLanguage&lang=ar&amp;redirect=<?php echo $redirectUrl; ?>">AR</a>
            </div>
            <div class="accessibility-group">
                <span class="accessibility-label">Taille</span>
                <a class="accessibility-btn" href="<?php echo BASE_URL; ?>/index.php?action=setFontSize&size=<?php echo min(130, ($_SESSION['font_size'] ?? 100) + 10); ?>&amp;redirect=<?php echo $redirectUrl; ?>">A+</a>
                <a class="accessibility-btn" href="<?php echo BASE_URL; ?>/index.php?action=setFontSize&size=<?php echo max(80, ($_SESSION['font_size'] ?? 100) - 10); ?>&amp;redirect=<?php echo $redirectUrl; ?>">A-</a>
            </div>
            <div class="accessibility-group">
                <a class="accessibility-btn <?php echo $currentTheme === 'light' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/index.php?action=setTheme&theme=light&amp;redirect=<?php echo $redirectUrl; ?>">☀️</a>
                <a class="accessibility-btn <?php echo $currentTheme === 'dark' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/index.php?action=setTheme&theme=dark&amp;redirect=<?php echo $redirectUrl; ?>">🌙</a>
            </div>
            <?php if ($userRole === 'admin'): ?>
            <div class="accessibility-group">
                <a class="accessibility-btn" href="<?php echo BASE_URL; ?>/index.php?action=dashboard&amp;redirect=<?php echo $redirectUrl; ?>" title="Accès au tableau de bord">📊</a>
            </div>
            <?php endif; ?>
        </div>
        <div class="nav-search">
            <span class="nav-search-icon">⌕</span>
            <input type="text" placeholder="Rechercher...">
        </div>
        <div class="accessibility-group" style="margin-left:12px; gap:0.5rem;">
            <div style="display:flex; flex-direction:column; align-items:flex-end; line-height:1.1;">
                <strong style="color:#fff; font-size:0.92rem;"><?php echo e($displayName); ?></strong>
                <span class="accessibility-label" style="opacity:0.75;"><?php echo e($userRole); ?></span>
            </div>
            <a class="accessibility-btn" href="<?php echo e($logoutUrl); ?>">Déconnexion</a>
        </div>
    </div>
</nav>
<div class="app-shell">
    <?php if ($userRole !== 'citoyen'): ?>
        <?php require BASE_PATH . '/views/App/Views/layouts/sidebar.php'; ?>
    <?php endif; ?>
    <main class="app-content">
    <script>
    (function () {
        const box = document.getElementById('notificationsBox');
        if (!box) return;

        let synced = false;

        box.addEventListener('toggle', function () {
            if (!box.open || synced) return;

            const badge = document.getElementById('notifCountBadge');
            if (badge) {
                badge.remove();
            }

            synced = true;

            fetch('<?php echo BASE_URL; ?>/index.php?route=home/notifications-seen', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).catch(function () {
                synced = false;
            });
        });
    })();
    </script>
    <?php if (!empty($flash)): ?>
        <div class="alert <?php echo $flash['type'] === 'success' ? 'alert-success' : 'alert-error'; ?>">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>

