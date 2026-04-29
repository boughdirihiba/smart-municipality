<div class="card">
	<h1>Mes signalements</h1>
	<div class="grid grid-4 admin-stats" style="margin: 0.8rem 0 1rem;">
		<article class="stat-card card">
			<span>Total</span>
			<strong><?php echo (int)($stats['total'] ?? count($items)); ?></strong>
		</article>
		<article class="stat-card card">
			<span>En attente</span>
			<strong><?php echo (int)($stats['en_attente'] ?? 0); ?></strong>
		</article>
		<article class="stat-card card">
			<span>En cours</span>
			<strong><?php echo (int)($stats['en_cours'] ?? 0); ?></strong>
		</article>
		<article class="stat-card card">
			<span>Résolus</span>
			<strong><?php echo (int)($stats['resolu'] ?? 0); ?></strong>
		</article>
	</div>
</div>

<div class="card">
	<h2>Carte de mes signalements</h2>
	<p>Cliquez sur un point pour ouvrir le detail.</p>
	<div id="mapStatus" class="map-status" aria-live="polite">Chargement de la carte...</div>
	<div id="map"></div>
</div>

<div class="card table-wrap">
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Titre</th>
				<th>Catégorie</th>
				<th>Priorité IA</th>
				<th>Statut</th>
				<th>Date</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($items as $item): ?>
				<tr>
					<td><?php echo (int)$item['id']; ?></td>
					<td><?php echo e($item['titre']); ?></td>
					<td><?php echo e($item['categorie']); ?></td>
					<td><span class="badge triage-<?php echo e($item['triage_level'] ?? 'faible'); ?>"><?php echo e($item['triage_level'] ?? 'faible'); ?></span></td>
					<td><span class="badge status-<?php echo e($item['statut']); ?>"><?php echo e($item['statut']); ?></span></td>
					<td><?php echo e($item['date_signalement']); ?></td>
					<td>
						<a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=signalements/detail&id=<?php echo (int)$item['id']; ?>">Voir</a>
						<a class="btn-principal" href="<?php echo BASE_URL; ?>/index.php?route=signalements/create">Nouveau</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<script>
window.SMART_MAP_CONFIG = {
	apiUrl: '<?php echo BASE_URL; ?>/index.php?route=map/data',
	isAdmin: false
};
</script>
<link id="maplibre-css" rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/maplibre-gl@4.7.1/dist/maplibre-gl.css';" />
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js" onerror="this.onerror=null;var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/maplibre-gl@4.7.1/dist/maplibre-gl.js';document.head.appendChild(s);"></script>
<script src="<?php echo BASE_URL; ?>/public/js/map.js"></script>