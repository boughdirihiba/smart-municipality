<?php
// Modern event list view - uses data from EventController
$events = $events ?? [];
$pageTitle = $pageTitle ?? 'Événements';
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

            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                <a href="<?php echo BASE_URL; ?>/index.php?route=event/create" class="btn btn-primary mb-3">
                    <i class="fas fa-plus"></i> Créer un événement
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mt-4">
        <?php if (empty($events)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Aucun événement disponible pour le moment.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <?php if (!empty($event['categorie_image'])): ?>
                            <img src="<?php echo htmlspecialchars($event['categorie_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($event['titre']); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($event['titre']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($event['description'] ?? '', 0, 100)); ?>...</p>
                            
                            <div class="event-details">
                                <small class="text-muted">
                                    <i class="far fa-calendar"></i> <?php echo htmlspecialchars($event['date_evenement'] ?? ''); ?>
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['lieu'] ?? ''); ?>
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-users"></i> <?php echo htmlspecialchars($event['max_participants'] ?? ''); ?> participants max
                                </small>
                            </div>

                            <div class="mt-3">
                                <a href="<?php echo BASE_URL; ?>/index.php?route=event/detail&id=<?php echo (int)$event['id']; ?>" class="btn btn-sm btn-info">
                                    Détails
                                </a>
                                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                                    <a href="<?php echo BASE_URL; ?>/index.php?route=event/edit&id=<?php echo (int)$event['id']; ?>" class="btn btn-sm btn-warning">
                                        Éditer
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/index.php?route=event/delete&id=<?php echo (int)$event['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr?')">
                                        Supprimer
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
