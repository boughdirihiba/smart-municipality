<div class="card">
	<h1>Détail du Budget: <?php echo e($budget['titre']); ?></h1>

	<div class="grid grid-4 admin-stats" style="margin: 0.8rem 0 1rem;">
		<article class="stat-card card">
			<span>Alloué</span>
			<strong><?php echo number_format($budget['montant_alloue'], 2, ',', ' '); ?> TND</strong>
		</article>
		<article class="stat-card card">
			<span>Dépensé</span>
			<strong><?php echo number_format($budget['montant_depense'], 2, ',', ' '); ?> TND</strong>
		</article>
		<article class="stat-card card">
			<span>Réservé</span>
			<strong><?php echo number_format($budget['montant_reserve'], 2, ',', ' '); ?> TND</strong>
		</article>
		<article class="stat-card card">
			<span>Disponible</span>
			<strong><?php echo number_format($budget['montant_alloue'] - $budget['montant_depense'] - $budget['montant_reserve'], 2, ',', ' '); ?> TND</strong>
		</article>
	</div>

	<div class="grid grid-2">
		<div class="card">
			<h3>Informations</h3>
			<p><strong>Année:</strong> <?php echo $budget['annee']; ?></p>
			<p><strong>Catégorie:</strong> <?php echo ucfirst($budget['categorie']); ?></p>
			<p><strong>Zone:</strong> <?php echo $budget['zone'] ?? 'N/A'; ?></p>
			<p><strong>Statut:</strong> <span class="badge status-<?php echo $budget['statut']; ?>"><?php echo ucfirst($budget['statut']); ?></span></p>
			<p><strong>Responsable:</strong> <?php echo ($budget['responsable_nom'] ?? '') . ' ' . ($budget['responsable_prenom'] ?? 'N/A'); ?></p>
			<p><strong>Description:</strong> <?php echo e($budget['description'] ?? 'N/A'); ?></p>
		</div>

		<div class="card">
			<h3>Actions</h3>
			<a href="<?php echo BASE_URL; ?>/index.php?route=budget/edit&id=<?php echo $budget['id']; ?>" class="btn-secondaire">✎ Éditer</a>
			<a href="<?php echo BASE_URL; ?>/index.php?route=budget/generateForecast&id=<?php echo $budget['id']; ?>" class="btn-principal">🔮 Générer Prévisions</a>
			<a href="<?php echo BASE_URL; ?>/index.php?route=budget/index" class="btn-secondaire">← Retour</a>
		</div>
	</div>

	<div class="card" style="margin-top: 1rem;">
		<h3>Ajouter une dépense</h3>
		<p>Cette action met à jour le montant dépensé du budget.</p>
		<form method="post" action="<?php echo BASE_URL; ?>/index.php?route=budget/addTransaction">
			<input type="hidden" name="budget_id" value="<?php echo (int)$budget['id']; ?>">
			<div class="form-row">
				<div class="form-group">
					<label for="montant">Montant dépensé (TND)</label>
					<input type="number" id="montant" name="montant" step="0.01" min="0.01" required>
				</div>
				<div class="form-group">
					<label for="type">Type</label>
					<select id="type" name="type">
						<option value="debit">Dépense</option>
						<option value="credit">Crédit / ajustement</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label for="description_tx">Description</label>
				<input type="text" id="description_tx" name="description" placeholder="Ex: Réparation éclairage rue principale">
			</div>
			<div class="form-actions">
				<button type="submit" class="btn-principal">Enregistrer la dépense</button>
			</div>
		</form>
	</div>
</div>

<?php if (!empty($forecasts)): ?>
<div class="card">
	<h2>Prévisions Mensuelles</h2>

	<?php if (!empty($accuracy['avg_accuracy'])): ?>
	<div class="grid grid-4 admin-stats" style="margin: 0.8rem 0 1rem;">
		<article class="stat-card card">
			<span>Précision moyenne</span>
			<strong><?php echo number_format($accuracy['avg_accuracy'], 1, ',', ' '); ?>%</strong>
		</article>
		<article class="stat-card card">
			<span>Mois avec réals</span>
			<strong><?php echo (int)($accuracy['months_with_actuals'] ?? 0); ?></strong>
		</article>
		<article class="stat-card card">
			<span>Total estimé</span>
			<strong><?php echo number_format($accuracy['total_estimated'] ?? 0, 2, ',', ' '); ?> TND</strong>
		</article>
		<article class="stat-card card">
			<span>Total réel</span>
			<strong><?php echo number_format($accuracy['total_actual'] ?? 0, 2, ',', ' '); ?> TND</strong>
		</article>
	</div>
	<?php endif; ?>

	<table>
		<thead>
			<tr>
				<th>Mois</th>
				<th>Estimé</th>
				<th>Réel</th>
				<th>Précision</th>
				<th>Écart</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			$months = ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'];
			foreach ($forecasts as $forecast): 
				$ecart = $forecast['depenses_reelles'] > 0 
					? $forecast['depenses_estimees'] - $forecast['depenses_reelles']
					: 0;
				$ecartPct = ($forecast['depenses_estimees'] > 0 && $forecast['depenses_reelles'] > 0)
					? round(($ecart / $forecast['depenses_estimees']) * 100, 1)
					: 0;
			?>
				<tr>
					<td><strong><?php echo $months[$forecast['mois'] - 1]; ?></strong></td>
					<td><?php echo number_format($forecast['depenses_estimees'], 2, ',', ' '); ?> TND</td>
					<td><?php echo number_format($forecast['depenses_reelles'], 2, ',', ' '); ?> TND</td>
					<td>
						<?php if ($forecast['precision_score'] > 0): ?>
							<span class="badge status-resolu"><?php echo number_format($forecast['precision_score'], 1, ',', ' '); ?>%</span>
						<?php else: ?>
							<span class="badge">-</span>
						<?php endif; ?>
					</td>
					<td>
						<?php if ($ecart != 0): ?>
							<span class="badge status-<?php echo $ecart > 0 ? 'rejete' : 'en_cours'; ?>">
								<?php echo $ecart > 0 ? '+' : ''; ?><?php echo $ecartPct; ?>%
							</span>
						<?php else: ?>
							-
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php else: ?>
<div class="card">
	<p>Aucune prévision générée. <a href="<?php echo BASE_URL; ?>/index.php?route=budget/generateForecast&id=<?php echo $budget['id']; ?>">Générer les prévisions</a></p>
</div>
<?php endif; ?>

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
