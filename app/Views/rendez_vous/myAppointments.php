<?php
// RendezVous - user's appointments
$appointments = $appointments ?? [];
$pageTitle = $pageTitle ?? 'Mes rendez-vous';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
                <a href="<?php echo BASE_URL; ?>/index.php?route=rendez_vous/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouveau rendez-vous
                </a>
            </div>

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
        <?php if (empty($appointments)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Vous n'avez aucun rendez-vous. <a href="<?php echo BASE_URL; ?>/index.php?route=rendez_vous/create">Cliquez ici</a> pour en créer un.
                </div>
            </div>
        <?php else: ?>
            <div class="col-12">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appt): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appt['service_nom'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($appt['date_rdv'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($appt['heure'] ?? ''); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($appt['statut'] === 'confirme') ? 'success' : 'danger'; ?>">
                                        <?php echo htmlspecialchars($appt['statut']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/index.php?route=rendez_vous/detail&id=<?php echo (int)$appt['id']; ?>" class="btn btn-sm btn-info">
                                        Détails
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/index.php?route=rendez_vous/edit&id=<?php echo (int)$appt['id']; ?>" class="btn btn-sm btn-warning">
                                        Éditer
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/index.php?route=rendez_vous/cancel&id=<?php echo (int)$appt['id']; ?>" class="btn btn-sm btn-secondary">
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
