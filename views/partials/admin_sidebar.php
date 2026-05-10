<?php
$currentUser = $_SESSION ?? [];
$displayName = trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? ''));
if ($displayName === '') {
    $displayName = 'Utilisateur';
}
$userRole = $currentUser['role'] ?? 'citoyen';
$avatarName = $avatarName ?? 'sidebar-photo.svg';
$currentRoute = $currentRoute ?? 'dashboard';
$baseUrl = $baseUrl ?? '../../';
$notifications = function_exists('get_notifications') ? get_notifications(8) : [];
$unreadNotificationsCount = function_exists('get_unread_notifications_count') ? get_unread_notifications_count() : 0;
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="<?php echo BASE_URL; ?>/logo.jpeg" alt="Logo Smart Municipality">
        <h2>Smart Municipality</h2>
    </div>




    <nav class="sidebar-nav">
        <a href="<?php echo $baseUrl; ?>views/dashboard/admin.php" class="sidebar-link <?php echo $currentRoute === 'dashboard' ? 'active' : ''; ?>">
            <span class="icon">📊</span><span class="label">Dashboard</span>
        </a>
        <a href="<?php echo $baseUrl; ?>index.php" class="sidebar-link <?php echo $currentRoute === 'evenements' ? 'active' : ''; ?>">
            <span class="icon">📅</span><span class="label">Événements</span>
        </a>
        <a href="<?php echo $baseUrl; ?>views/dashboard/categorie/liste.php" class="sidebar-link <?php echo $currentRoute === 'categories' ? 'active' : ''; ?>">
            <span class="icon">🏷️</span><span class="label">Catégories</span>
        </a>
        <a href="<?php echo $baseUrl; ?>views/participation/mes_participations.php" class="sidebar-link <?php echo $currentRoute === 'participations' ? 'active' : ''; ?>">
            <span class="icon">👥</span><span class="label">Participations</span>
        </a>
        <a href="<?php echo $baseUrl; ?>views/calendrier/index.php" class="sidebar-link <?php echo $currentRoute === 'calendrier' ? 'active' : ''; ?>">
            <span class="icon">🗓️</span><span class="label">Calendrier</span>
        </a>
        <a href="<?php echo $baseUrl; ?>index.php#blog" class="sidebar-link">
            <span class="icon">📰</span><span class="label">Blog</span>
        </a>
        <a href="<?php echo $baseUrl; ?>index.php#services" class="sidebar-link">
            <span class="icon">🛎️</span><span class="label">Services en ligne</span>
        </a>
    </nav>

    <div class="sidebar-footer-links">
        <a href="<?php echo $baseUrl; ?>index.php" class="sidebar-link"><span class="icon" title="Accueil">🏠</span></a>
        <a href="<?php echo $baseUrl; ?>logout.php" class="sidebar-link"><span class="icon" title="Déconnexion">🚪</span></a>
    </div>
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Basculer la barre" title="Basculer la barre">
        <span id="sidebarToggleIcon">❮</span>
    </button>
</aside>
