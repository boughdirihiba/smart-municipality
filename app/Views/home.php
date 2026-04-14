<section class="card hero">
    <h1>Carte Intelligente</h1>
    <p>Module Smart Map avec filtres, adresses, quartiers et zones critiques (mode admin).</p>
    <p>
        Mode: <strong><?php echo e($_SESSION['user']['role']); ?></strong>
        <a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=home/index&role=citoyen">Mode citoyen</a>
        <a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=home/index&role=admin">Mode admin</a>
    </p>
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
