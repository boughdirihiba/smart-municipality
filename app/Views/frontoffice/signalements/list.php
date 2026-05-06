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
	<div class="map-filter-panel" style="margin-bottom: 1rem; display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; padding: 1rem; background: #f9fafb; border-radius: 4px;">
		<label style="display: flex; align-items: center; gap: 0.5rem;">
			<span style="font-weight: 600;">Trier par :</span>
			<select id="sortBy" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
				<option value="date-desc">Date (récent)</option>
				<option value="date-asc">Date (ancien)</option>
				<option value="priority-desc">Priorité (urgent d'abord)</option>
				<option value="priority-asc">Priorité (faible d'abord)</option>
				<option value="statut">Statut</option>
				<option value="categorie">Catégorie</option>
			</select>
		</label>
		<label style="display: flex; align-items: center; gap: 0.5rem;">
			<span style="font-weight: 600;">Catégorie :</span>
			<select id="filterCategorie" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
				<option value="">Toutes</option>
				<option value="route">Route</option>
				<option value="eclairage">Éclairage</option>
				<option value="eau">Eau</option>
				<option value="transport">Transport</option>
				<option value="ordures">Ordures</option>
				<option value="autre">Autre</option>
			</select>
		</label>
		<label style="display: flex; align-items: center; gap: 0.5rem;">
			<span style="font-weight: 600;">Zone :</span>
			<input type="text" id="filterZone" placeholder="Quartier..." style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; width: 150px;">
		</label>
	</div>
	<div id="mapStatus" class="map-status" aria-live="polite">Chargement de la carte...</div>
	<div id="map"></div>
</div>

<div class="card table-wrap">
	<div class="table-controls" style="margin-bottom: 1rem; display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
		<label style="display: flex; align-items: center; gap: 0.5rem;">
			<span style="font-weight: 600;">Trier par :</span>
			<select id="sortSelect" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
				<option value="date-desc">Date (récent)</option>
				<option value="date-asc">Date (ancien)</option>
				<option value="triage-desc">Priorité (critique d'abord)</option>
				<option value="triage-asc">Priorité (faible d'abord)</option>
				<option value="statut">Statut</option>
				<option value="categorie">Catégorie</option>
				<option value="progression-desc">Progression (complètes)</option>
			</select>
		</label>
		<label style="display: flex; align-items: center; gap: 0.5rem;">
			<span style="font-weight: 600;">Filtrer par statut :</span>
			<select id="filterSelect" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
				<option value="">Tous les statuts</option>
				<option value="en_attente">En attente</option>
				<option value="en_cours">En cours</option>
				<option value="resolu">Résolu</option>
				<option value="rejete">Rejeté</option>
			</select>
		</label>
	</div>
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Titre</th>
				<th>Catégorie</th>
				<th>Priorité IA</th>
				<th>Statut</th>
				<th>Progression</th>
				<th>Date</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($items as $item): ?>
				<?php $progression = max(0, min(100, (int)($item['progression'] ?? 0))); ?>
				<?php $progressClass = $progression <= 30 ? 'progress-line--danger' : ($progression <= 70 ? 'progress-line--warning' : 'progress-line--success'); ?>
				<tr>
					<td><?php echo (int)$item['id']; ?></td>
					<td><?php echo e($item['titre']); ?></td>
					<td><?php echo e($item['categorie']); ?></td>
					<td><span class="badge triage-<?php echo e($item['triage_level'] ?? 'faible'); ?>"><?php echo e($item['triage_level'] ?? 'faible'); ?></span></td>
					<td><span class="badge status-<?php echo e($item['statut']); ?>"><?php echo e($item['statut']); ?></span></td>
					<td>
						<div class="progress-line <?php echo e($progressClass); ?>">
							<div class="progress-line__label"><span><?php echo $progression; ?>%</span></div>
							<div class="progress-line__track"><div class="progress-line__fill" style="width: <?php echo $progression; ?>%;"></div></div>
						</div>
					</td>
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
(function() {
  const sortSelect = document.getElementById('sortSelect');
  const filterSelect = document.getElementById('filterSelect');
  const table = document.querySelector('table tbody');

  if (!sortSelect || !filterSelect || !table) return;

  function triageLevel(text) {
    const levels = { critique: 3, eleve: 2, moyen: 1, faible: 0 };
    return levels[text] || 0;
  }

  function sortAndFilterRows() {
    const rows = Array.from(table.querySelectorAll('tr'));
    const sortValue = sortSelect.value;
    const filterValue = filterSelect.value;

    // Filtrer d'abord
    const filtered = rows.filter(row => {
      if (!filterValue) return true;
      const statusCell = row.cells[4];
      return statusCell && statusCell.textContent.toLowerCase().includes(filterValue);
    });

    // Trier
    filtered.sort((a, b) => {
      switch (sortValue) {
        case 'date-desc':
          return new Date(b.cells[6].textContent) - new Date(a.cells[6].textContent);
        case 'date-asc':
          return new Date(a.cells[6].textContent) - new Date(b.cells[6].textContent);
        case 'triage-desc':
          return triageLevel(b.cells[3].textContent) - triageLevel(a.cells[3].textContent);
        case 'triage-asc':
          return triageLevel(a.cells[3].textContent) - triageLevel(b.cells[3].textContent);
        case 'statut':
          return a.cells[4].textContent.localeCompare(b.cells[4].textContent);
        case 'categorie':
          return a.cells[2].textContent.localeCompare(b.cells[2].textContent);
        case 'progression-desc':
          const progA = parseInt(b.cells[5].textContent) || 0;
          const progB = parseInt(a.cells[5].textContent) || 0;
          return progA - progB;
        default:
          return 0;
      }
    });

    // Réappliquer l'ordre au tableau
    filtered.forEach(row => table.appendChild(row));

    // Afficher/masquer les lignes
    rows.forEach(row => {
      row.style.display = filtered.includes(row) ? '' : 'none';
    });
  }

  sortSelect.addEventListener('change', sortAndFilterRows);
  filterSelect.addEventListener('change', sortAndFilterRows);
})();
</script>

<script>
window.SMART_MAP_CONFIG = {
	apiUrl: '<?php echo BASE_URL; ?>/index.php?route=map/data',
	isAdmin: false
};
</script>
<link id="maplibre-css" rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/maplibre-gl@4.7.1/dist/maplibre-gl.css';" />
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js" onerror="this.onerror=null;var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/maplibre-gl@4.7.1/dist/maplibre-gl.js';document.head.appendChild(s);"></script>
<script src="<?php echo BASE_URL; ?>/public/js/map.js"></script>