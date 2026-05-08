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
        <li><a href="<?php echo BASE_URL; ?>/index.php?route=home/index" class="<?php echo strpos($currentRoute, 'home') !== false ? 'active' : ''; ?>">Accueil</a></li>
        <li><a href="<?php echo BASE_URL; ?>/index.php?route=signalements/list" class="<?php echo strpos($currentRoute, 'signalement') !== false ? 'active' : ''; ?>">Signalements</a></li>
        <li><a href="<?php echo BASE_URL; ?>/index.php?route=event/index" class="<?php echo strpos($currentRoute, 'event') !== false ? 'active' : ''; ?>">Événements</a></li>
        <li><a href="<?php echo BASE_URL; ?>/index.php?route=blog/index" class="<?php echo strpos($currentRoute, 'blog') !== false ? 'active' : ''; ?>">Blog</a></li>
        <li><a href="<?php echo BASE_URL; ?>/index.php?route=rendez_vous/myAppointments" class="<?php echo strpos($currentRoute, 'rendez_vous') !== false ? 'active' : ''; ?>">Rendez-vous</a></li>
        <?php if (isset($_SESSION['user'])): ?>
            <li class="nav-divider"></li>
            <li><a href="<?php echo BASE_URL; ?>/index.php?route=login/logout">Déconnexion</a></li>
        <?php else: ?>
            <li class="nav-divider"></li>
            <li><a href="<?php echo BASE_URL; ?>/index.php?route=login/index" class="nav-btn-login">Connexion</a></li>
        <?php endif; ?>
    </ul>
    <div class="nav-right">
        <?php if (isset($_SESSION['user'])): ?>
            <div class="nav-user">
                <span class="nav-user-name"><?php echo htmlspecialchars($_SESSION['user']['prenom'] ?? 'User'); ?></span>
                <img src="<?php echo BASE_URL; ?>/public/uploads/<?php echo htmlspecialchars($_SESSION['user']['avatar'] ?? 'sidebar-photo.svg'); ?>" alt="Avatar" class="nav-user-avatar">
            </div>
        <?php endif; ?>
        <div class="nav-search">
            <span class="nav-search-icon">⌕</span>
            <input type="text" placeholder="Rechercher...">
        </div>
    </div>
</nav>
<?php endif; ?>
<div class="app-shell">
    <?php if ($userRole !== 'citoyen'): ?>
        <?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>
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
