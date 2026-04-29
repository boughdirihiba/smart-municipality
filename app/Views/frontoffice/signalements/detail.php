<div class="card">
	<h1>Détail du signalement #<?php echo (int)($item['id'] ?? 0); ?></h1>
	<p><strong><?php echo e($item['titre'] ?? ''); ?></strong></p>
	<p><?php echo e($item['description'] ?? ''); ?></p>
	<p><strong>Catégorie:</strong> <?php echo e($item['categorie'] ?? '-'); ?></p>
	<p><strong>Statut:</strong> <span class="badge status-<?php echo e($item['statut'] ?? 'en_attente'); ?>"><?php echo e($item['statut'] ?? 'en_attente'); ?></span></p>
	<p><strong>Date:</strong> <?php echo e($item['date_signalement'] ?? ''); ?></p>
</div>

<div class="card">
	<h2>Localisation</h2>
	<div id="mapDetail" style="height: 320px;"></div>
</div>

<?php if (!empty($history)): ?>
	<div class="card">
		<h3>Historique de positions</h3>
		<ul>
			<?php foreach ($history as $h): ?>
				<li><?php echo e($h['lat'] ?? ''); ?>, <?php echo e($h['lng'] ?? ''); ?> — <?php echo e($h['created_at'] ?? ''); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<script>
window.SMART_MAP_DETAIL = {
	lat: <?php echo json_encode($item['latitude'] ?? 0); ?>,
	lng: <?php echo json_encode($item['longitude'] ?? 0); ?>
};
</script>
<link id="maplibre-css" rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/maplibre-gl@4.7.1/dist/maplibre-gl.css';" />
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js" onerror="this.onerror=null;var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/maplibre-gl@4.7.1/dist/maplibre-gl.js';document.head.appendChild(s);"></script>
<script src="<?php echo BASE_URL; ?>/public/js/map.js"></script>