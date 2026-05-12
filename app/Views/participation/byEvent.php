<?php
// Modern participation view - shows all participants for an event (admin)
$event = $event ?? [];
$participations = $participations ?? [];
$pageTitle = $pageTitle ?? 'Participations';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <a href="<?php echo BASE_URL; ?>/index.php?route=event/detail&id=<?php echo (int)$event['id']; ?>" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

            <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
            <p class="text-muted">Événement: <?php echo htmlspecialchars($event['titre'] ?? ''); ?></p>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mt-4">
        <?php if (empty($participations)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Aucune participation pour cet événement.
                </div>
            </div>
        <?php else: ?>
            <div class="col-12">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Participant</th>
                            <th>Email</th>
                            <th>Nombre</th>
                            <th>Statut</th>
                            <th>Date d'inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participations as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['prenom'] ?? '') . ' ' . htmlspecialchars($p['nom'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($p['email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($p['nombre_participants'] ?? '1'); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo ($p['statut_validation'] === 'valide') ? 'success' : 
                                             (($p['statut_validation'] === 'en_attente') ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo htmlspecialchars($p['statut_validation'] ?? ''); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($p['date_participation'] ?? ''); ?></td>
                                <td>
                                    <?php if ($p['statut_validation'] === 'en_attente'): ?>
                                        <form method="POST" action="<?php echo BASE_URL; ?>/index.php?route=participation/validate" class="d-inline">
                                            <input type="hidden" name="participation_id" value="<?php echo (int)$p['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success">Valider</button>
                                        </form>
                                        <form method="POST" action="<?php echo BASE_URL; ?>/index.php?route=participation/reject" class="d-inline">
                                            <input type="hidden" name="participation_id" value="<?php echo (int)$p['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Refuser</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
