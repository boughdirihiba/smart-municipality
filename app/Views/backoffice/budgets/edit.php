<div class="card">
	<h1>Éditer le Budget: <?php echo e($budget['titre']); ?></h1>

	<div class="grid grid-3 admin-stats" style="margin: 0.8rem 0 1rem;">
		<article class="stat-card card">
			<span>Montant Alloué</span>
			<strong><?php echo number_format($budget['montant_alloue'], 2, ',', ' '); ?> TND</strong>
		</article>
		<article class="stat-card card">
			<span>Montant Dépensé</span>
			<strong><?php echo number_format($budget['montant_depense'], 2, ',', ' '); ?> TND</strong>
		</article>
		<article class="stat-card card">
			<span>Disponible</span>
			<strong><?php echo number_format($budget['montant_alloue'] - $budget['montant_depense'], 2, ',', ' '); ?> TND</strong>
		</article>
	</div>

	<form method="post" action="<?php echo BASE_URL; ?>/index.php?route=budget/edit&id=<?php echo $budget['id']; ?>">
		<div class="form-group">
			<label for="titre">Titre du Budget</label>
			<input type="text" id="titre" name="titre" value="<?php echo e($budget['titre']); ?>">
		</div>

		<div class="form-row">
			<div class="form-group">
				<label for="montant_alloue">Montant Alloué (TND)</label>
				<input type="number" id="montant_alloue" name="montant_alloue" step="0.01" value="<?php echo $budget['montant_alloue']; ?>">
			</div>

			<div class="form-group">
				<label for="montant_reserve">Montant Réservé (TND)</label>
				<input type="number" id="montant_reserve" name="montant_reserve" step="0.01" value="<?php echo $budget['montant_reserve']; ?>">
			</div>

			<div class="form-group">
				<label for="statut">Statut</label>
				<select id="statut" name="statut">
					<option value="planifie" <?php echo $budget['statut'] === 'planifie' ? 'selected' : ''; ?>>Planifié</option>
					<option value="en_cours" <?php echo $budget['statut'] === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
					<option value="termine" <?php echo $budget['statut'] === 'termine' ? 'selected' : ''; ?>>Terminé</option>
					<option value="depassement" <?php echo $budget['statut'] === 'depassement' ? 'selected' : ''; ?>>Dépassement</option>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label for="description">Description</label>
			<textarea id="description" name="description" rows="4"><?php echo e($budget['description'] ?? ''); ?></textarea>
		</div>

		<div class="form-actions">
			<button type="submit" class="btn-principal">Mettre à jour</button>
			<a href="<?php echo BASE_URL; ?>/index.php?route=budget/detail&id=<?php echo $budget['id']; ?>" class="btn-secondaire">Voir détail</a>
		</div>
	</form>
</div>

<div class="card table-wrap">
	<h2>Historique des Transactions</h2>
	<table>
		<thead>
			<tr>
				<th>Date</th>
				<th>Type</th>
				<th>Montant</th>
				<th>Intervention</th>
				<th>Description</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($transactions as $trans): ?>
				<tr>
					<td><?php echo $trans['created_at']; ?></td>
					<td><span class="badge status-<?php echo $trans['type']; ?>"><?php echo ucfirst($trans['type']); ?></span></td>
					<td><?php echo number_format($trans['montant'], 2, ',', ' '); ?> TND</td>
					<td><?php echo e($trans['intervention_titre'] ?? '-'); ?></td>
					<td><?php echo e($trans['description'] ?? '-'); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
