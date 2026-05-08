<div class="card">
	<h1>Gestion des Budgets - <?php echo $annee; ?></h1>

	<div class="grid grid-4 admin-stats" style="margin: 0.8rem 0 1rem;">
		<article class="stat-card card">
			<span>Total alloué</span>
			<strong><?php echo number_format($stats['total_alloue'], 2, ',', ' '); ?> TND</strong>
		</article>
		<article class="stat-card card">
			<span>Total dépensé</span>
			<strong><?php echo number_format($stats['total_depense'], 2, ',', ' '); ?> TND</strong>
		</article>
		<article class="stat-card card">
			<span>Total réservé</span>
			<strong><?php echo number_format($stats['total_reserve'], 2, ',', ' '); ?> TND</strong>
		</article>
		<article class="stat-card card">
			<span>Taux d'utilisation</span>
			<strong><?php echo $stats['taux_utilisation']; ?>%</strong>
		</article>
	</div>

	<form class="filter-row" method="get" action="<?php echo BASE_URL; ?>/index.php">
		<input type="hidden" name="route" value="budget/index">
		<select name="annee">
			<?php foreach ([date('Y')-2, date('Y')-1, date('Y'), date('Y')+1] as $y): ?>
				<option value="<?php echo $y; ?>" <?php echo $annee == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
			<?php endforeach; ?>
		</select>
		<select name="categorie">
			<option value="">Toutes catégories</option>
			<?php foreach ($categories as $cat): ?>
				<option value="<?php echo $cat; ?>" <?php echo $categorie === $cat ? 'selected' : ''; ?>><?php echo ucfirst($cat); ?></option>
			<?php endforeach; ?>
		</select>
		<select name="zone">
			<option value="">Toutes zones</option>
			<?php foreach ($zones as $z): ?>
				<option value="<?php echo $z; ?>" <?php echo $zone === $z ? 'selected' : ''; ?>><?php echo $z; ?></option>
			<?php endforeach; ?>
		</select>
		<button type="submit" class="btn-principal">Filtrer</button>
		<a href="<?php echo BASE_URL; ?>/index.php?route=budget/create" class="btn-secondaire">+ Nouveau Budget</a>
	</form>
</div>

<div class="card">
	<h2>Résumé par Catégorie</h2>
	<table>
		<thead>
			<tr>
				<th>Catégorie</th>
				<th>Alloué</th>
				<th>Dépensé</th>
				<th>Réservé</th>
				<th>% Utilisation</th>
				<th>Budgets</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($summaryByCategory as $row): ?>
				<?php $util = $row['total_alloue'] > 0 ? round(($row['total_depense'] / $row['total_alloue']) * 100) : 0; ?>
				<tr>
					<td><strong><?php echo ucfirst($row['categorie']); ?></strong></td>
					<td><?php echo number_format($row['total_alloue'], 2, ',', ' '); ?> TND</td>
					<td><?php echo number_format($row['total_depense'], 2, ',', ' '); ?> TND</td>
					<td><?php echo number_format($row['total_reserve'], 2, ',', ' '); ?> TND</td>
					<td>
						<div class="progress-line">
							<div class="progress-line__label"><span><?php echo $util; ?>%</span></div>
							<div class="progress-line__track"><div class="progress-line__fill" style="width: <?php echo $util; ?>%;"></div></div>
						</div>
					</td>
					<td><?php echo $row['count']; ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<div class="card">
	<h2>Résumé par Zone</h2>
	<table>
		<thead>
			<tr>
				<th>Zone</th>
				<th>Alloué</th>
				<th>Dépensé</th>
				<th>Réservé</th>
				<th>% Utilisation</th>
				<th>Budgets</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($summaryByZone as $row): ?>
				<?php $util = $row['total_alloue'] > 0 ? round(($row['total_depense'] / $row['total_alloue']) * 100) : 0; ?>
				<tr>
					<td><strong><?php echo $row['zone'] ?? 'Non spécifiée'; ?></strong></td>
					<td><?php echo number_format($row['total_alloue'], 2, ',', ' '); ?> TND</td>
					<td><?php echo number_format($row['total_depense'], 2, ',', ' '); ?> TND</td>
					<td><?php echo number_format($row['total_reserve'], 2, ',', ' '); ?> TND</td>
					<td>
						<div class="progress-line">
							<div class="progress-line__label"><span><?php echo $util; ?>%</span></div>
							<div class="progress-line__track"><div class="progress-line__fill" style="width: <?php echo $util; ?>%;"></div></div>
						</div>
					</td>
					<td><?php echo $row['count']; ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<div class="card table-wrap">
	<h2>Tous les Budgets</h2>
	<table>
		<thead>
			<tr>
				<th>Titre</th>
				<th>Année</th>
				<th>Catégorie</th>
				<th>Zone</th>
				<th>Alloué</th>
				<th>Dépensé</th>
				<th>Réservé</th>
				<th>Statut</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($budgets as $budget): ?>
				<tr>
					<td><?php echo e($budget['titre']); ?></td>
					<td><?php echo $budget['annee']; ?></td>
					<td><?php echo ucfirst($budget['categorie']); ?></td>
					<td><?php echo $budget['zone'] ?? '-'; ?></td>
					<td><?php echo number_format($budget['montant_alloue'], 2, ',', ' '); ?> TND</td>
					<td><?php echo number_format($budget['montant_depense'], 2, ',', ' '); ?> TND</td>
					<td><?php echo number_format($budget['montant_reserve'], 2, ',', ' '); ?> TND</td>
					<td><span class="badge status-<?php echo e($budget['statut']); ?>"><?php echo ucfirst($budget['statut']); ?></span></td>
					<td>
						<a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=budget/detail&id=<?php echo $budget['id']; ?>">Détail</a>
						<a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=budget/edit&id=<?php echo $budget['id']; ?>">Éditer</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
