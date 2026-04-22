<?php
$currentUser = $_SESSION['user'] ?? [];
$displayName = trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? ''));
if ($displayName === '') {
    $displayName = 'Utilisateur';
}
$avatarName = $currentUser['avatar'] ?? 'sidebar-photo.svg';
?>

<section class="card hero hero-split">
    <div>
        <p class="hero-kicker">Votre espace personnel</p>
        <h1>Carte Intelligente</h1>
        <p>Suivi des signalements, carte urbaine et gestion rapide de vos demandes.</p>
        <div class="hero-actions">
            <a class="btn-principal" href="<?php echo BASE_URL; ?>/index.php?route=signalements/create">Créer un signalement</a>
            <a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=signalements/list">Voir mes signalements</a>
        </div>
    </div>
    <div class="hero-profile">
        <img src="<?php echo BASE_URL; ?>/public/uploads/<?php echo e($avatarName); ?>" alt="Photo utilisateur">
        <div>
            <strong><?php echo e($displayName); ?></strong>
            <span><?php echo e($currentUser['role'] ?? 'citoyen'); ?></span>
        </div>
        <p><?php echo e($currentUser['email'] ?? ''); ?></p>
    </div>
</section>

<section class="grid grid-3 dashboard-stats">
    <article class="card stat-card">
        <span>Mes signalements</span>
        <strong><?php echo (int)($userSignalementCount ?? 0); ?></strong>
    </article>
    <article class="card stat-card">
        <span>En attente</span>
        <strong><?php echo (int)($userPendingCount ?? 0); ?></strong>
    </article>
    <article class="card stat-card">
        <span>Résolus</span>
        <strong><?php echo (int)($userResolvedCount ?? 0); ?></strong>
    </article>
</section>

<section class="card">
    <div class="section-head">
        <div>
            <h2>Mes signalements récents</h2>
            <p>Les derniers éléments liés à votre compte.</p>
        </div>
        <a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=signalements/list">Tout voir</a>
    </div>
    <div class="recent-list">
        <?php if (!empty($latestSignalements)): ?>
            <?php foreach ($latestSignalements as $item): ?>
                <article class="recent-item">
                    <div>
                        <strong><?php echo e($item['titre']); ?></strong>
                        <p><?php echo e($item['adresse'] ?? 'Adresse non renseignée'); ?></p>
                    </div>
                    <span class="badge status-<?php echo e($item['statut']); ?>"><?php echo e($item['statut']); ?></span>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-state">Aucun signalement pour le moment.</p>
        <?php endif; ?>
    </div>
</section>

<section class="card hero-note">
    <p><strong>Mode actuel :</strong> <?php echo e($_SESSION['user']['role'] ?? 'citoyen'); ?></p>
    <div class="hero-actions">
        <a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=home/index&role=citoyen">Mode citoyen</a>
        <a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=home/index&role=admin">Mode admin</a>
    </div>
</section>

<section class="card">
    <h2>Filtres</h2>
    <div class="filter-row">
        <select id="filterCategorie">
            <option value="">Toutes catégories</option>
            <option value="route">Route</option>
            <option value="eclairage">Eclairage</option>
            <option value="eau">Eau</option>
            <option value="transport">Transport</option>
            <option value="ordures">Ordures</option>
            <option value="autre">Autre</option>
        </select>
        <input id="filterDate" type="text" placeholder="Date (YYYY-MM-DD)">
        <select id="filterZone">
            <option value="">Toute zone</option>
            <option value="centre">Tunis Centre</option>
            <option value="nord">Nord</option>
            <option value="sud">Sud</option>
        </select>
        <button id="btnFiltrer" class="btn-principal" type="button">Filtrer</button>
    </div>
</section>

<section class="card">
    <div id="mapStatus" class="map-status" aria-live="polite">Chargement de la carte...</div>
    <div id="map"></div>
</section>

<script>
window.SMART_MAP_CONFIG = {
    apiUrl: '<?php echo BASE_URL; ?>/index.php?route=map/data',
    isAdmin: <?php echo $_SESSION['user']['role'] === 'admin' ? 'true' : 'false'; ?>
};
</script>
<link id="maplibre-css" rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/maplibre-gl@4.7.1/dist/maplibre-gl.css';" />
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js" onerror="this.onerror=null;var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/maplibre-gl@4.7.1/dist/maplibre-gl.js';document.head.appendChild(s);"></script>
<script src="<?php echo BASE_URL; ?>/public/js/map.js"></script>
