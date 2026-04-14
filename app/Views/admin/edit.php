<div class="card">
    <h1>Modifier le statut</h1>
    <p><strong>ID:</strong> <?php echo (int)$item['id']; ?></p>
    <p><strong>Titre:</strong> <?php echo e($item['titre']); ?></p>
    <p><strong>Coordonnées actuelles:</strong> <?php echo e($item['latitude']); ?>, <?php echo e($item['longitude']); ?></p>

    <form method="post" action="<?php echo BASE_URL; ?>/index.php?route=admin/edit" novalidate>
        <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>">
        <label for="statut">Nouveau statut</label>
        <select id="statut" name="statut">
            <?php foreach (['en_attente', 'en_cours', 'resolu', 'rejete'] as $st): ?>
                <option value="<?php echo $st; ?>" <?php echo $item['statut'] === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
            <?php endforeach; ?>
        </select>

        <div class="grid grid-2" style="margin-top: 0.9rem;">
            <div>
                <label for="latitude">Nouvelle latitude (optionnel)</label>
                <input id="latitude" name="latitude" type="text" value="" placeholder="<?php echo e((string)$item['latitude']); ?>">
            </div>
            <div>
                <label for="longitude">Nouvelle longitude (optionnel)</label>
                <input id="longitude" name="longitude" type="text" value="" placeholder="<?php echo e((string)$item['longitude']); ?>">
            </div>
        </div>

        <label for="commentaire_position" style="margin-top: 0.9rem;">Commentaire de changement de position</label>
        <input id="commentaire_position" name="commentaire_position" type="text" value="" placeholder="Ex: correction de geolocalisation sur site">

        <div style="margin-top:1rem; display:flex; gap:8px;">
            <button class="btn-principal" type="submit">Mettre à jour</button>
            <a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=admin/list">Retour</a>
        </div>
    </form>
</div>
