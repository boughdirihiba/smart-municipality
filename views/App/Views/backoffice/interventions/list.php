<div class="card">
    <div class="section-head">
        <div>
            <h1>BackOffice - Interventions</h1>
            <p>Deuxieme CRUD geolocalise: gestion des interventions municipales.</p>
        </div>
        <a class="btn-principal" href="<?php echo BASE_URL; ?>/index.php?route=interventions/create">Nouvelle intervention</a>
    </div>

    <form class="filter-row" method="get" action="<?php echo BASE_URL; ?>/index.php">
        <input type="hidden" name="route" value="interventions/list">
        <select name="type">
            <option value="">Tous types</option>
            <?php foreach (['route', 'eclairage', 'eau', 'transport', 'ordures', 'autre'] as $tp): ?>
                <option value="<?php echo $tp; ?>" <?php echo ($type ?? '') === $tp ? 'selected' : ''; ?>><?php echo ucfirst($tp); ?></option>
            <?php endforeach; ?>
        </select>
        <select name="statut">
            <option value="">Tous statuts</option>
            <?php foreach (['planifiee', 'en_cours', 'terminee', 'annulee'] as $st): ?>
                <option value="<?php echo $st; ?>" <?php echo ($statut ?? '') === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-secondaire">Filtrer</button>
    </form>
</div>

<div class="card table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Type</th>
                <th>Statut</th>
                <th>Progression</th>
                <th>Coordonnees</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo (int)$item['id']; ?></td>
                        <td><?php echo e($item['titre']); ?></td>
                        <td><?php echo e($item['type']); ?></td>
                        <td><span class="badge status-<?php echo e($item['statut']); ?>"><?php echo e($item['statut']); ?></span></td>
                        <td>
                            <?php $progression = (int)($item['progression'] ?? 0); ?>
                            <?php $progressClass = $progression <= 30 ? 'is-low' : ($progression <= 70 ? 'is-medium' : 'is-high'); ?>
                            <div class="progress-wrap" aria-label="Progression intervention">
                                <div class="progress-track">
                                    <div class="progress-fill <?php echo $progressClass; ?>" style="width: <?php echo $progression; ?>%;"></div>
                                </div>
                                <span class="progress-label"><?php echo $progression; ?>%</span>
                            </div>
                        </td>
                        <td><?php echo e((string)$item['latitude']); ?>, <?php echo e((string)$item['longitude']); ?></td>
                        <td><?php echo e((string)($item['date_intervention'] ?: $item['created_at'])); ?></td>
                        <td>
                            <a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=interventions/edit&id=<?php echo (int)$item['id']; ?>">Edit</a>
                            <a class="btn-danger" href="<?php echo BASE_URL; ?>/index.php?route=interventions/delete&id=<?php echo (int)$item['id']; ?>" onclick="return confirm('Supprimer cette intervention ?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">Aucune intervention.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
