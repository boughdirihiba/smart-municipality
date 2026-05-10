<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title ?? APP_NAME); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
</head>
<?php $currentRoute = $_GET['route'] ?? 'home/index'; ?>
<?php $userRole = $_SESSION['user']['role'] ?? 'citoyen'; ?>
<body class="role-<?php echo $userRole; ?>">
<?php if ($userRole !== 'admin'): ?>
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
        <li><a href="<?php echo BASE_URL; ?>/index.php?action=manage" class="<?php echo ($_GET['action'] ?? '') === 'manage' ? 'active' : ''; ?>">Demandes</a></li>
        <li><a href="<?php echo BASE_URL; ?>/index.php?action=rendez_vous" class="<?php echo ($_GET['action'] ?? '') === 'rendez_vous' ? 'active' : ''; ?>">Rendez-vous</a></li>
    </ul>
    <div class="nav-right">
        <div class="nav-search">
            <span class="nav-search-icon">⌕</span>
            <input type="text" placeholder="Rechercher...">
        </div>
    </div>
</nav>
<?php endif; ?>
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

