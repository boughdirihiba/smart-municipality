<?php
// Modern event edit view - form for editing an existing event
$event = $event ?? [];
$categories = $categories ?? [];
$pageTitle = $pageTitle ?? 'Éditer un événement';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <a href="<?php echo BASE_URL; ?>/index.php?route=event/detail&id=<?php echo (int)$event['id']; ?>" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

            <h1><?php echo htmlspecialchars($pageTitle); ?></h1>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="card p-4">
                <input type="hidden" name="id" value="<?php echo (int)$event['id']; ?>">

                <div class="mb-3">
                    <label for="titre" class="form-label">Titre</label>
                    <input type="text" class="form-control" id="titre" name="titre" value="<?php echo htmlspecialchars($event['titre'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($event['description'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="lieu" class="form-label">Lieu</label>
                    <input type="text" class="form-control" id="lieu" name="lieu" value="<?php echo htmlspecialchars($event['lieu'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="date_evenement" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date_evenement" name="date_evenement" value="<?php echo htmlspecialchars($event['date_evenement'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="heure" class="form-label">Heure</label>
                    <input type="time" class="form-control" id="heure" name="heure" value="<?php echo htmlspecialchars($event['heure'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="categorie_id" class="form-label">Catégorie</label>
                    <select class="form-select" id="categorie_id" name="categorie_id" required>
                        <option value="">-- Sélectionner une catégorie --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int)$cat['id']; ?>" <?php echo ((int)$event['categorie_id'] === (int)$cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=event/detail&id=<?php echo (int)$event['id']; ?>" class="btn btn-secondary">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
