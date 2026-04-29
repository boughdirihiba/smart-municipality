<?php
$it = $item ?? [];
$isEditMode = !empty($isEdit);
$errorsList = $errors ?? [];
?>

<div class="card">
    <h1><?php echo $isEditMode ? 'Modifier intervention' : 'Nouvelle intervention'; ?></h1>

    <?php if (!empty($errorsList)): ?>
        <div class="alert alert-error">
            <?php foreach ($errorsList as $err): ?>
                <div><?php echo e($err); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo BASE_URL; ?>/index.php?route=<?php echo $isEditMode ? 'interventions/edit' : 'interventions/store'; ?>" novalidate>
        <?php if ($isEditMode): ?>
            <input type="hidden" name="id" value="<?php echo (int)($it['id'] ?? 0); ?>">
        <?php endif; ?>

        <label for="titre">Titre</label>
        <input id="titre" name="titre" type="text" value="<?php echo e((string)($it['titre'] ?? '')); ?>" required>

        <label for="description" style="margin-top:0.8rem;">Description</label>
        <textarea id="description" name="description" required><?php echo e((string)($it['description'] ?? '')); ?></textarea>

        <div class="grid grid-2" style="margin-top:0.8rem;">
            <div>
                <label for="type">Type</label>
                <select id="type" name="type">
                    <?php foreach (['route', 'eclairage', 'eau', 'transport', 'ordures', 'autre'] as $tp): ?>
                        <option value="<?php echo $tp; ?>" <?php echo (($it['type'] ?? 'autre') === $tp) ? 'selected' : ''; ?>><?php echo ucfirst($tp); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="statut">Statut</label>
                <select id="statut" name="statut">
                    <?php foreach (['planifiee', 'en_cours', 'terminee', 'annulee'] as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo (($it['statut'] ?? 'planifiee') === $st) ? 'selected' : ''; ?>><?php echo $st; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-2" style="margin-top:0.8rem;">
            <div>
                <label for="latitude">Latitude</label>
                <input id="latitude" name="latitude" type="text" value="<?php echo e((string)($it['latitude'] ?? '')); ?>" required>
            </div>
            <div>
                <label for="longitude">Longitude</label>
                <input id="longitude" name="longitude" type="text" value="<?php echo e((string)($it['longitude'] ?? '')); ?>" required>
            </div>
        </div>

        <label for="date_intervention" style="margin-top:0.8rem;">Date intervention (optionnelle)</label>
        <input id="date_intervention" name="date_intervention" type="date" value="<?php echo e((string)($it['date_intervention'] ?? '')); ?>">

        <label for="progression" style="margin-top:0.8rem;">Progression des travaux (%)</label>
        <input
            id="progression"
            name="progression"
            type="range"
            min="0"
            max="100"
            step="1"
            value="<?php echo e((string)($it['progression'] ?? '0')); ?>"
            oninput="document.getElementById('progressionValue').textContent = this.value + '%'"
        >
        <div class="progress-input-meta">
            <span id="progressionValue"><?php echo e((string)($it['progression'] ?? '0')); ?>%</span>
        </div>

        <div style="margin-top:1rem; display:flex; gap:8px;">
            <button class="btn-principal" type="submit"><?php echo $isEditMode ? 'Mettre a jour' : 'Creer'; ?></button>
            <a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=interventions/list">Retour</a>
        </div>
    </form>
</div>
