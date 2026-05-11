<?php
$currentUser = $_SESSION['user'] ?? [];
$displayName = trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? ''));
if ($displayName === '') {
    $displayName = 'Utilisateur';
}
$currentRoute = $_GET['route'] ?? 'home/index';
$avatarName = $currentUser['avatar'] ?? 'sidebar-photo.svg';
$isAdmin = ($currentUser['role'] ?? 'citoyen') === 'admin';
$isAdminSignalementsRoute = str_starts_with((string)$currentRoute, 'admin/');
$isInterventionsRoute = str_starts_with((string)$currentRoute, 'interventions/');
$isBudgetRoute = str_starts_with((string)$currentRoute, 'budget/');
$notifications = get_notifications(8);
$unreadNotificationsCount = get_unread_notifications_count();
?>
<style>
/* ============================================================
   HOVER SIDEBAR
   Collapsed: 58px wide — icons only, no text
   Expanded:  240px wide — on hover, smooth transition
   Page content: pushed right by 58px, never covered
============================================================ */

.sidebar {
    position: fixed;
    top: 0; left: 0;
    height: 100vh;
    width: 58px;
    overflow: hidden;
    z-index: 1000;
    transition: width 0.30s cubic-bezier(0.4, 0, 0.2, 1),
                box-shadow 0.30s ease;
    will-change: width;
    display: flex;
    flex-direction: column;
    background: linear-gradient(135deg, #0f3b2c 0%, #1b6a53 100%);
    color: #f8fffc;
    box-shadow: 2px 0 12px rgba(0,0,0,0.12);
}

.sidebar:hover {
    width: 240px;
    box-shadow: 4px 0 20px rgba(0,0,0,0.15);
}

/* All children need min-width so they don't wrap when collapsed */
.sidebar-logo,
.sidebar-user,
.notifications-trigger,
.sidebar-link,
.sidebar-footer-links a {
    min-width: 240px;
    white-space: nowrap;
}

/* ---- Labels + text: hidden when collapsed, visible on hover ---- */
.sidebar-logo h2,
.sidebar-logo p,
.sidebar-user div,
.sidebar-link .label,
.sidebar-footer-links .label,
.notif-label {
    opacity: 0;
    transform: translateX(-10px);
    transition: opacity 0.18s ease 0.08s,
                transform 0.18s ease 0.08s;
    display: inline-block;
}

.sidebar:hover .sidebar-logo h2,
.sidebar:hover .sidebar-logo p,
.sidebar:hover .sidebar-user div,
.sidebar:hover .sidebar-link .label,
.sidebar:hover .sidebar-footer-links .label,
.sidebar:hover .notif-label {
    opacity: 1;
    transform: translateX(0);
}

/* ---- Logo ---- */
.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 14px 14px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    flex-shrink: 0;
}

.sidebar-logo img {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    flex-shrink: 0;
}

.sidebar-logo h2 {
    font-size: 14px;
    font-weight: 600;
    margin: 0;
    line-height: 1.2;
}

.sidebar-logo p {
    font-size: 10px;
    margin: 2px 0 0;
    opacity: 0;
}

/* ---- User ---- */
.sidebar-user {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    flex-shrink: 0;
}

.sidebar-user > img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    flex-shrink: 0;
    border: 2px solid rgba(255,255,255,0.2);
    object-fit: cover;
}

.sidebar-user div strong {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 160px;
}

.sidebar-user div span {
    font-size: 10.5px;
    opacity: 0.6;
    text-transform: capitalize;
}

/* ---- Notifications ---- */
.notifications-box {
    border-bottom: 1px solid rgba(255,255,255,0.08);
    flex-shrink: 0;
}

.notifications-trigger {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 11px 16px;
    cursor: pointer;
    list-style: none;
    position: relative;
    transition: background 0.2s;
    background: transparent;
    border: none;
    color: #f8fffc;
    font: inherit;
}

.notifications-trigger::-webkit-details-marker { display: none; }
.notifications-trigger:hover { background: rgba(255,255,255,0.08); }

.notif-icon {
    font-size: 16px;
    flex-shrink: 0;
    width: 26px;
    text-align: center;
    position: relative;
}

.notif-count {
    position: absolute;
    top: -5px; right: -5px;
    background: #e74c3c;
    color: white;
    font-size: 9px;
    font-weight: 700;
    min-width: 16px;
    height: 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 2px;
}

.notif-label {
    font-size: 13px;
    font-weight: 500;
}

.notifications-panel {
    background: rgba(0,0,0,0.2);
    border-top: 1px solid rgba(255,255,255,0.06);
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

details.notifications-box[open] .notifications-panel {
    max-height: 240px;
    overflow-y: auto;
}

.notifications-panel h4 {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    opacity: 0.5;
    padding: 10px 16px 6px;
}

.notif-empty {
    font-size: 11.5px;
    opacity: 0.45;
    padding: 6px 16px 12px;
    font-style: italic;
}

.notif-list { list-style: none; padding: 0 0 8px; }

.notif-item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 7px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
}

.notif-item:hover { background: rgba(255,255,255,0.05); }

.notif-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #2FA084;
    margin-top: 5px;
    flex-shrink: 0;
}

.notif-item p { font-size: 11.5px; opacity: 0.8; margin: 0; line-height: 1.4; }
.notif-item small { font-size: 10px; opacity: 0.4; display: block; margin-top: 2px; }

/* ---- Nav links ---- */
.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 6px 0;
}

.sidebar-nav::-webkit-scrollbar { width: 0; }

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 10px 16px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: background 0.18s;
    position: relative;
    color: #f8fffc;
}

.sidebar-link:hover { background: rgba(255,255,255,0.09); }

.sidebar-link.active {
    background: rgba(47,160,132,0.25);
    border-left: 3px solid #2FA084;
    padding-left: 13px;
}

.sidebar-link .icon {
    font-size: 16px;
    width: 26px;
    text-align: center;
    flex-shrink: 0;
}

/* ---- Footer ---- */
.sidebar-footer-links {
    border-top: 1px solid rgba(255,255,255,0.08);
    padding: 4px 0;
    flex-shrink: 0;
}

.sidebar-footer-links a {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 9px 16px;
    text-decoration: none;
    font-size: 13px;
    transition: background 0.18s;
    color: #f8fffc;
}

.sidebar-footer-links a:hover { background: rgba(255,255,255,0.09); }

/* Hide the toggle button — no longer needed */
.sidebar-toggle { display: none; }

/* Tooltip on icon when collapsed */
.sidebar:not(:hover) .sidebar-link[title]:hover::after,
.sidebar:not(:hover) .sidebar-footer-links a[title]:hover::after {
    content: attr(title);
    position: absolute;
    left: 64px;
    top: 50%;
    transform: translateY(-50%);
    background: #0F3B2C;
    color: white;
    font-size: 12px;
    font-weight: 500;
    padding: 5px 10px;
    border-radius: 6px;
    white-space: nowrap;
    pointer-events: none;
    z-index: 9999;
    border: 1px solid rgba(47,160,132,0.3);
}

/* Push app-content right when sidebar expands on hover */
.app-content {
    transition: margin-left 0.30s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar:hover ~ .app-content {
    margin-left: 240px;
}
</style>

<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="<?php echo BASE_URL; ?>/public/uploads/sidebar-photo.svg" alt="Logo Smart Municipality">
        <div>
            <h2>Smart Municipality</h2>
            <p>Gestion urbaine et signalements</p>
        </div>
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
            <span class="notif-icon" style="position:relative;">
                🔔
                <?php if ($unreadNotificationsCount > 0): ?>
                    <span class="notif-count" id="notifCountBadge"><?php echo $unreadNotificationsCount; ?></span>
                <?php endif; ?>
            </span>
            <span class="notif-label">
                Notifications<?php echo $unreadNotificationsCount > 0 ? " ($unreadNotificationsCount)" : ''; ?>
            </span>
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
            <a href="<?php echo BASE_URL; ?>/index.php?route=admin/list"
               class="sidebar-link <?php echo $isAdminSignalementsRoute ? 'active' : ''; ?>"
               title="BackOffice">
                <span class="icon">🏛️</span>
                <span class="label">BackOffice</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=admin/list"
               class="sidebar-link <?php echo $isAdminSignalementsRoute ? 'active' : ''; ?>"
               title="Gestion des signalements">
                <span class="icon">⚠️</span>
                <span class="label">Gestion des signalements</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=interventions/list"
               class="sidebar-link <?php echo $isInterventionsRoute ? 'active' : ''; ?>"
               title="Gestion interventions">
                <span class="icon">🔧</span>
                <span class="label">Gestion interventions</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=budget/index"
               class="sidebar-link <?php echo $isBudgetRoute ? 'active' : ''; ?>"
               title="Gestion des budgets">
                <span class="icon">💰</span>
                <span class="label">Gestion des budgets</span>
            </a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>/index.php?route=signalements/list"
               class="sidebar-link <?php echo $currentRoute === 'signalements/list' ? 'active' : ''; ?>"
               title="Mes signalements">
                <span class="icon">📋</span>
                <span class="label">Mes signalements</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=signalements/create"
               class="sidebar-link <?php echo $currentRoute === 'signalements/create' ? 'active' : ''; ?>"
               title="Nouveau signalement">
                <span class="icon">➕</span>
                <span class="label">Nouveau signalement</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=home/index"
               class="sidebar-link <?php echo $currentRoute === 'home/index' ? 'active' : ''; ?>"
               title="Accueil">
                <span class="icon">🏠</span>
                <span class="label">Accueil</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer-links">
        <a href="<?php echo BASE_URL; ?>/index.php?route=home/index"
           class="sidebar-link"
           title="Paramètres">
            <span class="icon">⚙️</span>
            <span class="label">Paramètres</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/index.php?route=auth/logout"
           class="sidebar-link"
           title="Déconnexion">
            <span class="icon">⎋</span>
            <span class="label">Déconnexion</span>
        </a>
    </div>
</aside>

<!-- Push page content right of the collapsed sidebar — never covered -->
<style>
    /* Only apply margin-left in admin mode where sidebar is visible */
    body.role-admin { 
        margin-left: 58px; 
        transition: margin-left 0.30s cubic-bezier(0.4,0,0.2,1); 
    }
</style>
