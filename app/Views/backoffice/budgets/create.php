<div class="card">
	<h1>Créer un nouveau Budget</h1>

	<form method="post" action="<?php echo BASE_URL; ?>/index.php?route=budget/create">
		<div class="form-group">
			<label for="titre">Titre du Budget</label>
			<input type="text" id="titre" name="titre" placeholder="Ex: Budget 2026 - Éclairage Public" required>
		</div>

		<div class="form-row">
			<div class="form-group">
				<label for="annee">Année</label>
				<input type="number" id="annee" name="annee" min="2020" max="2040" value="<?php echo date('Y'); ?>" required>
			</div>

			<div class="form-group">
				<label for="categorie">Catégorie</label>
				<select id="categorie" name="categorie" required>
					<option value="">Sélectionner une catégorie</option>
					<?php foreach ($categories as $cat): ?>
						<option value="<?php echo $cat; ?>"><?php echo ucfirst($cat); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="form-group">
				<label for="zone">Zone (optionnel)</label>
				<select id="zone" name="zone">
					<option value="">Tous les quartiers</option>
					<?php foreach ($zones as $z): ?>
						<option value="<?php echo $z; ?>"><?php echo $z; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="form-row">
			<div class="form-group">
				<label for="montant_alloue">Montant Alloué (TND)</label>
				<input type="number" id="montant_alloue" name="montant_alloue" step="0.01" min="0" placeholder="0.00" required>
			</div>

			<div class="form-group">
				<label for="montant_reserve">Montant Réservé (TND)</label>
				<input type="number" id="montant_reserve" name="montant_reserve" step="0.01" min="0" placeholder="0.00">
			</div>
		</div>

		<div class="form-group">
			<label for="description">Description</label>
			<textarea id="description" name="description" rows="4" placeholder="Détails sur ce budget..."></textarea>
		</div>

		<div class="form-group">
			<label for="responsable_id">Responsable (optionnel)</label>
			<input type="number" id="responsable_id" name="responsable_id" placeholder="ID de l'utilisateur responsable">
		</div>

		<div class="form-actions">
			<button type="submit" class="btn-principal">Créer le Budget</button>
			<a href="<?php echo BASE_URL; ?>/index.php?route=budget/index" class="btn-secondaire">Annuler</a>
		</div>
	</form>
</div>
