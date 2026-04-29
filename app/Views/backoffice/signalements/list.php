<div class="card">
	<h1>BackOffice - Gestion des signalements</h1>
	<div class="grid grid-4 admin-stats" style="margin: 0.8rem 0 1rem;">
		<article class="stat-card card">
			<span>Total</span>
			<strong><?php echo (int)($stats['total'] ?? 0); ?></strong>
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
		<article class="stat-card card">
			<span>Priorité IA critique</span>
			<strong><?php echo (int)($stats['ai_critique'] ?? 0); ?></strong>
		</article>
	</div>

	<form class="filter-row" method="get" action="<?php echo BASE_URL; ?>/index.php">
		<input type="hidden" name="route" value="admin/list">
		<select id="filterCategorie" name="categorie">
			<option value="">Toutes catégories</option>
			<?php foreach (['route', 'eclairage', 'eau', 'transport', 'ordures', 'autre'] as $cat): ?>
				<option value="<?php echo $cat; ?>" <?php echo $categorie === $cat ? 'selected' : ''; ?>><?php echo ucfirst($cat); ?></option>
			<?php endforeach; ?>
		</select>
		<select name="statut">
			<option value="">Tous statuts</option>
			<?php foreach (['en_attente', 'en_cours', 'resolu', 'rejete'] as $st): ?>
				<option value="<?php echo $st; ?>" <?php echo $statut === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
			<?php endforeach; ?>
		</select>
		<input id="filterDate" type="text" placeholder="Date (YYYY-MM-DD)">
		<select id="filterZone">
			<option value="">Toute zone</option>
			<option value="centre">Tunis Centre</option>
			<option value="nord">Nord</option>
			<option value="sud">Sud</option>
		</select>
		<button id="btnFiltrer" type="button" class="btn-principal">Filtrer carte</button>
		<button type="submit" class="btn-secondaire">Filtrer tableau</button>
	</form>
</div>

<div class="card">
	<h2>Carte globale des signalements</h2>
	<p>Cliquez sur un point pour ouvrir le detail puis modifier via le lien d edition admin.</p>
	<div id="mapStatus" class="map-status" aria-live="polite">Chargement de la carte...</div>
	<div id="map"></div>
</div>

<div class="card table-wrap">
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Titre</th>
				<th>Utilisateur</th>
				<th>Catégorie</th>
				<th>Priorité IA</th>
				<th>Raison IA</th>
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
					<td><?php echo e(trim(($item['user_prenom'] ?? '') . ' ' . ($item['user_nom'] ?? 'Utilisateur'))); ?></td>
					<td><?php echo e($item['categorie']); ?></td>
					<td><span class="badge triage-<?php echo e($item['triage_level'] ?? 'faible'); ?>"><?php echo e($item['triage_level'] ?? 'faible'); ?></span></td>
					<td><?php echo e($item['triage_reason'] ?? '-'); ?></td>
					<td><span class="badge status-<?php echo e($item['statut']); ?>"><?php echo e($item['statut']); ?></span></td>
					<td><?php echo e($item['date_signalement']); ?></td>
					<td>
						<a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=admin/edit&id=<?php echo (int)$item['id']; ?>">Edit</a>
						<a class="btn-danger" href="<?php echo BASE_URL; ?>/index.php?route=admin/delete&id=<?php echo (int)$item['id']; ?>" onclick="return confirm('Supprimer ce signalement ?');">Delete</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<script>
window.SMART_MAP_CONFIG = {
	apiUrl: '<?php echo BASE_URL; ?>/index.php?route=map/data',
	isAdmin: true
};
</script>
<link id="maplibre-css" rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/maplibre-gl@4.7.1/dist/maplibre-gl.css';" />
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js" onerror="this.onerror=null;var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/maplibre-gl@4.7.1/dist/maplibre-gl.js';document.head.appendChild(s);"></script>
<script src="<?php echo BASE_URL; ?>/public/js/map.js"></script>