<?php
// RendezVous - edit view
$appointment = $appointment ?? [];
$pageTitle = $pageTitle ?? 'Éditer rendez-vous';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <a href="<?php echo BASE_URL; ?>/index.php?route=rendez_vous/detail&id=<?php echo (int)$appointment['id']; ?>" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

            <h1><?php echo htmlspecialchars($pageTitle); ?></h1>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="card p-4">
                <input type="hidden" name="id" value="<?php echo (int)$appointment['id']; ?>">

                <div class="mb-3">
                    <label for="categorie_id" class="form-label">Service</label>
                    <select class="form-select" id="categorie_id" name="categorie_id" required>
                        <option value="<?php echo (int)$appointment['categorie_id']; ?>">
                            <?php echo htmlspecialchars($appointment['service_nom'] ?? ''); ?>
                        </option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="date_rdv" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date_rdv" name="date_rdv" value="<?php echo htmlspecialchars($appointment['date_rdv'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="heure" class="form-label">Heure</label>
                    <select class="form-select" id="heure" name="heure" required>
                        <option value="<?php echo htmlspecialchars($appointment['heure'] ?? ''); ?>" selected>
                            <?php echo htmlspecialchars($appointment['heure'] ?? ''); ?>
                        </option>
                        <option value="09:00">09:00</option>
                        <option value="10:00">10:00</option>
                        <option value="11:00">11:00</option>
                        <option value="14:00">14:00</option>
                        <option value="15:00">15:00</option>
                        <option value="16:00">16:00</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="statut" class="form-label">Statut</label>
                    <select class="form-select" id="statut" name="statut">
                        <option value="confirme" <?php echo ($appointment['statut'] === 'confirme') ? 'selected' : ''; ?>>Confirmé</option>
                        <option value="annule" <?php echo ($appointment['statut'] === 'annule') ? 'selected' : ''; ?>>Annulé</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=rendez_vous/detail&id=<?php echo (int)$appointment['id']; ?>" class="btn btn-secondary">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
