<?php
$currentUser = $_SESSION['user'] ?? [];
$displayName = trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? ''));
if ($displayName === '') {
    $displayName = 'Utilisateur';
}
$currentRoute = $_GET['route'] ?? 'home/index';
$avatarName = $currentUser['avatar'] ?? 'sidebar-photo.svg';
$isAdmin = ($currentUser['role'] ?? 'citoyen') === 'admin';
$notifications = get_notifications(8);
$unreadNotificationsCount = get_unread_notifications_count();
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="<?php echo BASE_URL; ?>/public/uploads/sidebar-photo.svg" alt="Logo Smart Municipality">
        <h2>Smart Municipality</h2>
        <p>Gestion urbaine et signalements</p>
    </div>

    <div class="sidebar-user">
        <img src="<?php echo BASE_URL; ?>/public/uploads/<?php echo e($avatarName); ?>" alt="Photo utilisateur">
        <div>
            <strong><?php echo e($displayName); ?></strong>
            <span><?php echo e($currentUser['role'] ?? 'citoyen'); ?></span>
        </div>
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

    <nav class="sidebar-nav">
        <?php if ($isAdmin): ?>
            <a href="<?php echo BASE_URL; ?>/index.php?route=admin/list" class="sidebar-link <?php echo $currentRoute === 'admin/list' ? 'active' : ''; ?>">
                <span class="label">BackOffice</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=admin/list" class="sidebar-link <?php echo $currentRoute === 'admin/list' ? 'active' : ''; ?>">
                <span class="label">Gestion des signalements</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=home/index&role=citoyen" class="sidebar-link">
                <span class="label">Mode citoyen</span>
            </a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>/index.php?route=signalements/list" class="sidebar-link <?php echo $currentRoute === 'signalements/list' ? 'active' : ''; ?>">
                <span class="label">Mes signalements</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=signalements/create" class="sidebar-link <?php echo $currentRoute === 'signalements/create' ? 'active' : ''; ?>">
                <span class="label">Nouveau signalement</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=home/index" class="sidebar-link <?php echo $currentRoute === 'home/index' ? 'active' : ''; ?>">
                <span class="label">Accueil</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=home/index&role=admin" class="sidebar-link">
                <span class="label">Mode admin</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer-links">
        <a href="<?php echo BASE_URL; ?>/index.php?route=home/index" class="sidebar-link">
            <span class="icon" title="Paramètres">⚙️</span>
        </a>
        <?php if ($isAdmin): ?>
            <a href="<?php echo BASE_URL; ?>/index.php?route=home/index&role=citoyen" class="sidebar-link">
                <span class="icon" title="Passer en citoyen">↩️</span>
            </a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>/index.php?route=home/index&role=admin" class="sidebar-link">
                <span class="icon" title="Passer en admin">🛡️</span>
            </a>
        <?php endif; ?>
    </div>
</aside>
