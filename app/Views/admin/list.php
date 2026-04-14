<div class="card">
    <h1>BackOffice - Gestion des signalements</h1>
    <form class="filter-row" method="get" action="<?php echo BASE_URL; ?>/index.php">
        <input type="hidden" name="route" value="admin/list">
        <select name="categorie">
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
        <div></div>
        <button type="submit" class="btn-principal">Filtrer</button>
    </form>
</div>

<div class="card table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Catégorie</th>
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
