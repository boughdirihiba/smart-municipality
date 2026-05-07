<?php
// Modern participation view - shows user's registered events
$participations = $participations ?? [];
$pageTitle = $pageTitle ?? 'Mes participations';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <h1><?php echo htmlspecialchars($pageTitle); ?></h1>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close"></button>
                </div>
            <?php endif; ?>

            <a href="<?php echo BASE_URL; ?>/index.php?route=event/index" class="btn btn-primary mb-3">
                <i class="fas fa-calendar"></i> Voir tous les événements
            </a>
        </div>
    </div>

    <div class="row mt-4">
        <?php if (empty($participations)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Vous n'êtes inscrit à aucun événement pour le moment.
                </div>
            </div>
        <?php else: ?>
            <div class="col-12">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Événement</th>
                            <th>Date</th>
                            <th>Lieu</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participations as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['titre'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($p['date_evenement'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($p['lieu'] ?? ''); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo ($p['statut_validation'] === 'valide') ? 'success' : 
                                             (($p['statut_validation'] === 'en_attente') ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo htmlspecialchars($p['statut_validation'] ?? ''); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/index.php?route=event/detail&id=<?php echo (int)$p['event_id']; ?>" class="btn btn-sm btn-info">
                                        Détails
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/index.php?route=participation/cancel&event_id=<?php echo (int)$p['event_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Annuler cette participation?')">
                                        Annuler
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
