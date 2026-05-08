<?php
// RendezVous - detail view
$appointment = $appointment ?? [];
$pageTitle = $pageTitle ?? 'Détail rendez-vous';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <a href="<?php echo BASE_URL; ?>/index.php?route=rendez_vous/myAppointments" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

            <div class="card">
                <div class="card-header">
                    <h5><?php echo htmlspecialchars($pageTitle); ?></h5>
                </div>
                <div class="card-body">
                    <p>
                        <strong>Service:</strong> <?php echo htmlspecialchars($appointment['service_nom'] ?? ''); ?>
                    </p>
                    <p>
                        <strong>Date:</strong> <?php echo htmlspecialchars($appointment['date_rdv'] ?? ''); ?>
                    </p>
                    <p>
                        <strong>Heure:</strong> <?php echo htmlspecialchars($appointment['heure'] ?? ''); ?>
                    </p>
                    <p>
                        <strong>Statut:</strong>
                        <span class="badge bg-<?php echo ($appointment['statut'] === 'confirme') ? 'success' : 'danger'; ?>">
                            <?php echo htmlspecialchars($appointment['statut'] ?? ''); ?>
                        </span>
                    </p>
                </div>
                <div class="card-footer">
                    <a href="<?php echo BASE_URL; ?>/index.php?route=rendez_vous/edit&id=<?php echo (int)$appointment['id']; ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Éditer
                    </a>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=rendez_vous/cancel&id=<?php echo (int)$appointment['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr?')">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
