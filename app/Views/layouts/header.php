<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title ?? APP_NAME); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
</head>
<body>
<div class="app-shell">
    <?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>
    <main class="app-content">
    <?php $notifications = get_notifications(8); ?>
    <?php $unreadNotificationsCount = get_unread_notifications_count(); ?>
    <header class="topbar card">
        <div class="topbar-title">
            <h2><?php echo e($title ?? APP_NAME); ?></h2>
            <p>Espace de suivi urbain</p>
        </div>
        <div class="topbar-right">
            <div class="topbar-shortcuts" aria-label="Raccourcis">
                <a class="topbar-map-link" href="<?php echo BASE_URL; ?>/index.php?route=signalements/create" title="Evenement" aria-label="Evenement">📌</a>
                <a class="topbar-map-link" href="<?php echo BASE_URL; ?>/index.php?route=signalements/list" title="Mes signalements" aria-label="Mes signalements">📋</a>
                <a class="topbar-map-link" href="<?php echo BASE_URL; ?>/index.php?route=admin/list" title="Dashboard" aria-label="Dashboard">🧰</a>
                <a class="topbar-map-link" href="<?php echo BASE_URL; ?>/index.php?route=home/index" title="Carte intelligente" aria-label="Carte intelligente">🗺️</a>
            </div>
            <details class="notifications-box" id="notificationsBox">
                <summary class="notifications-trigger" aria-label="Notifications">
                    <span class="notif-icon">🔔</span>
                    <?php if ($unreadNotificationsCount > 0): ?>
                        <span class="notif-count" id="notifCountBadge"><?php echo $unreadNotificationsCount; ?></span>
                    <?php endif; ?>
                </summary>
                <div class="notifications-panel">
                    <h4>Notifications</h4>
                    <?php if (empty($notifications)): ?>
                        <p class="notif-empty">Aucune notification pour le moment.</p>
                    <?php else: ?>
                        <ul class="notif-list">
                            <?php foreach ($notifications as $notif): ?>
                                <li class="notif-item notif-<?php echo e($notif['type'] ?? 'info'); ?>">
                                    <span class="notif-dot"></span>
                                    <div>
                                        <p><?php echo e($notif['message'] ?? 'Notification'); ?></p>
                                        <small><?php echo e($notif['created_at'] ?? ''); ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </details>

            <div class="user-box">
                <img src="<?php echo BASE_URL; ?>/public/uploads/sidebar-photo.svg" alt="Photo utilisateur">
                <div class="user-box-info">
                    <strong><?php echo e($_SESSION['user']['nom'] ?? 'Utilisateur'); ?></strong>
                    <span><?php echo e($_SESSION['user']['role'] ?? 'citoyen'); ?></span>
                </div>
            </div>
        </div>
    </header>
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
