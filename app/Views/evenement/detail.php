<?php
// Modern event detail view - displays a single event with participation option
$event = $event ?? [];
$pageTitle = $pageTitle ?? 'Détail Événement';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <a href="<?php echo BASE_URL; ?>/index.php?route=event/index" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

            <div class="card">
                <?php if (!empty($event['categorie_image'])): ?>
                    <img src="<?php echo htmlspecialchars($event['categorie_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($event['titre']); ?>">
                <?php endif; ?>
                <div class="card-body">
                    <h1 class="card-title"><?php echo htmlspecialchars($event['titre']); ?></h1>
                    
                    <div class="event-meta mb-3">
                        <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($event['categorie_nom'] ?? 'Non spécifiée'); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($event['date_evenement'] ?? ''); ?> à <?php echo htmlspecialchars($event['heure'] ?? ''); ?></p>
                        <p><strong>Lieu:</strong> <?php echo htmlspecialchars($event['lieu'] ?? ''); ?></p>
                        <p><strong>Places disponibles:</strong> <?php echo htmlspecialchars($event['max_participants'] ?? ''); ?></p>
                    </div>

                    <div class="event-description mb-4">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($event['description'] ?? '')); ?></p>
                    </div>

                    <div class="event-actions">
                        <?php if (isset($_SESSION['user'])): ?>
                            <form method="POST" action="<?php echo BASE_URL; ?>/index.php?route=participation/register" class="d-inline">
                                <input type="hidden" name="event_id" value="<?php echo (int)$event['id']; ?>">
                                <input type="hidden" name="nombre_participants" value="1">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> S'inscrire
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/index.php?route=login" class="btn btn-primary">
                                Se connecter pour s'inscrire
                            </a>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                            <a href="<?php echo BASE_URL; ?>/index.php?route=event/edit&id=<?php echo (int)$event['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Éditer
                            </a>
                            <a href="<?php echo BASE_URL; ?>/index.php?route=participation/byEvent&event_id=<?php echo (int)$event['id']; ?>" class="btn btn-info">
                                <i class="fas fa-users"></i> Voir les participations
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
