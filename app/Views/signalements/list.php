<div class="card" style="display:flex; justify-content:space-between; align-items:center;">
    <h1>Mes signalements</h1>
    <a class="btn-principal" href="<?php echo BASE_URL; ?>/index.php?route=signalements/create">Nouveau</a>
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
            <th>Action</th>
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
                    <a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=signalements/detail&id=<?php echo (int)$item['id']; ?>">Détail</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
