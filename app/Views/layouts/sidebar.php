<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="<?php echo BASE_URL; ?>/public/uploads/sidebar-photo.svg" alt="Logo Smart Municipality">
        <h2>Smart Municipality</h2>
        <p>Gestion urbaine et signalements</p>
    </div>

    <?php $currentRoute = $_GET['route'] ?? 'home/index'; ?>

    <nav class="sidebar-nav">
        <a href="<?php echo BASE_URL; ?>/index.php?route=home/index" class="sidebar-link <?php echo $currentRoute === 'home/index' ? 'active' : ''; ?>">
            <span class="label">Profile</span>
        </a>
        <div class="sidebar-link">
            <span class="label">Événement</span>
        </div>
        <a href="<?php echo BASE_URL; ?>/index.php?route=home/index" class="sidebar-link <?php echo $currentRoute === 'home/index' ? 'active' : ''; ?>">
            <span class="label">Carte intelligente</span>
        </a>
        <div class="sidebar-link">
            <span class="label">Blog</span>
        </div>
        <div class="sidebar-link">
            <span class="label">Services en ligne</span>
        </div>
        <div class="sidebar-link">
            <span class="label">Rendez-vous</span>
        </div>
    </nav>

    <div class="sidebar-footer-links">
        <a href="<?php echo BASE_URL; ?>/index.php?route=home/index" class="sidebar-link">
            <span class="icon">⚙️</span>
            <span class="label">Paramètres</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/index.php?route=home/index" class="sidebar-link">
            <span class="icon">↩️</span>
            <span class="label">Déconnexion</span>
        </a>
    </div>
</aside>
