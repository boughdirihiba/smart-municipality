<?php
session_start();
require_once __DIR__ . '/../../controller/ParticipationC.php';
require_once __DIR__ . '/../../controller/EvenementC.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php?error=Vous devez être connecté');
    exit();
}

$participationC = new ParticipationC();
$evenementC = new EvenementC();
$participations = $participationC->afficherParticipationsParUser($_SESSION['user_id']);
$userName = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];
$userAvatar = strtoupper(substr($_SESSION['prenom'][0], 0, 1) . substr($_SESSION['nom'][0], 0, 1));

// Statistiques
$totalParticipations = count($participations);
$evenementsPasses = 0;
$evenementsAVenir = 0;
$dateActuelle = date('Y-m-d');

foreach ($participations as $part) {
    if ($part['date_evenement'] < $dateActuelle) {
        $evenementsPasses++;
    } else {
        $evenementsAVenir++;
    }
}

// Gestion des messages
$message = '';
$messageType = '';
if (isset($_GET['success']) && $_GET['success'] == 'annule') {
    $message = 'Votre participation a été annulée avec succès.';
    $messageType = 'success';
}
if (isset($_GET['error'])) {
    $message = htmlspecialchars($_GET['error']);
    $messageType = 'danger';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes participations - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1a5e2a;
            --primary-dark-hover: #0d3b1a;
            --secondary-dark: #2e7d32;
            --secondary-light: #4caf50;
            --accent-green: #66bb6a;
            --light-green: #e8f5e9;
            --bg-dark: #f0f4f0;
            --text-dark: #1a2e1a;
            --card-shadow: 0 10px 30px rgba(0,0,0,0.08);
            --hover-shadow: 0 15px 40px rgba(26, 94, 42, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--light-green) 0%, var(--bg-dark) 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        /* ========== NAVBAR ========== */
        .navbar {
            background: white;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-dark) !important;
        }

        .navbar-brand i {
            color: var(--primary-dark);
        }

        /* ========== HERO SECTION ========== */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
            color: white;
            padding: 40px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 15px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.3);
        }

        /* ========== STATS CARDS ========== */
        .stats-container {
            margin-top: -30px;
            position: relative;
            z-index: 2;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            border: 1px solid rgba(26, 94, 42, 0.1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: var(--light-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
            color: var(--primary-dark);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-dark);
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        /* ========== CONTENT CONTAINER ========== */
        .content-container {
            background: white;
            border-radius: 24px;
            padding: 0;
            margin: 30px auto;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            max-width: 1300px;
        }

        .section-header {
            background: linear-gradient(135deg, var(--light-green), white);
            padding: 25px 30px;
            border-bottom: 2px solid var(--secondary-light);
        }

        .section-header h2 {
            color: var(--primary-dark);
            font-weight: 600;
            margin: 0;
        }

        /* ========== CARDS LIST ========== */
        .participations-list {
            padding: 25px;
        }

        .participation-card {
            background: white;
            border-radius: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 1px solid #e8f5e9;
            overflow: hidden;
        }

        .participation-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 20px rgba(26, 94, 42, 0.1);
            border-color: var(--secondary-light);
        }

        .card-header-custom {
            background: linear-gradient(135deg, #f8fdf8, white);
            padding: 18px 25px;
            border-bottom: 1px solid #e8f5e9;
            cursor: pointer;
            transition: all 0.3s;
        }

        .card-header-custom:hover {
            background: linear-gradient(135deg, #f0f8f0, white);
        }

        .event-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-dark);
            margin: 0;
        }

        .event-category {
            display: inline-block;
            padding: 4px 12px;
            background: var(--light-green);
            color: var(--primary-dark);
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .event-date-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            background: #fff3e0;
            color: #e65100;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .event-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            background: var(--secondary-dark);
            color: white;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .event-status.passed {
            background: #9e9e9e;
        }

        .card-body-custom {
            padding: 20px 25px;
            background: #fafdfa;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #555;
            font-size: 0.9rem;
        }

        .info-item i {
            width: 20px;
            color: var(--primary-dark);
            font-size: 1rem;
        }

        .event-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #e0e0e0;
        }

        .btn-cancel {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            padding: 8px 20px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }

        .btn-back {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark));
            border: none;
            padding: 10px 25px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 94, 42, 0.3);
        }

        /* ========== EMPTY STATE ========== */
        .empty-state {
            text-align: center;
            padding: 60px 40px;
        }

        .empty-state-icon {
            width: 120px;
            height: 120px;
            background: var(--light-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
        }

        .empty-state-icon i {
            font-size: 3.5rem;
            color: var(--primary-dark);
        }

        .empty-state h3 {
            color: var(--primary-dark);
            margin-bottom: 10px;
        }

        /* ========== TOAST ========== */
        .toast-message {
            position: fixed;
            top: 90px;
            right: 20px;
            z-index: 9999;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            .card-header-custom {
                flex-direction: column;
                gap: 10px;
            }
            .stats-container .row {
                gap: 15px;
            }
        }
    </style>
</head>
<body>

    <!-- ========== TOAST NOTIFICATION ========== -->
    <?php if ($message): ?>
    <div class="toast-message">
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show shadow-lg border-0 rounded-3" role="alert">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- ========== NAVBAR ========== -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
     <!-- Dans la navbar, remplacer par -->
<a class="navbar-brand" href="../../index.php">
    <img src="../../logo.jpeg" alt="Smart Municipality" height="35" style="border-radius: 8px; margin-right: 10px;">
    Smart Municipality
</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../../index.php">
                            <i class="fas fa-calendar-alt me-1"></i> Événements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="mes_participations.php">
                            <i class="fas fa-ticket-alt me-1"></i> Mes participations
                        </a>
                    </li>
                </ul>
                <div class="user-info">
                    <span class="text-muted">
                        <i class="fas fa-user-circle me-1" style="color: var(--primary-dark);"></i>
                        <?php echo htmlspecialchars($userName); ?>
                    </span>
                    <a href="../../logout.php" class="btn btn-sm btn-outline-custom" style="border-color: var(--primary-dark); color: var(--primary-dark);">
                        <i class="fas fa-sign-out-alt me-1"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ========== HERO SECTION ========== -->
    <section class="hero-section">
        <div class="container hero-content">
            <div class="text-center">
                <div class="user-avatar">
                    <?php echo $userAvatar; ?>
                </div>
                <h1 class="display-5 fw-bold">Mes participations</h1>
                <p class="lead">Retrouvez tous les événements auxquels vous êtes inscrit</p>
            </div>
        </div>
    </section>

    <!-- ========== STATISTIQUES ========== -->
    <div class="container stats-container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalParticipations; ?></div>
                    <div class="stat-label">Total participations</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?php echo $evenementsAVenir; ?></div>
                    <div class="stat-label">Événements à venir</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="stat-value"><?php echo $evenementsPasses; ?></div>
                    <div class="stat-label">Événements passés</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== CONTENU PRINCIPAL ========== -->
    <div class="container">
        <div class="content-container">
            <div class="section-header d-flex justify-content-between align-items-center">
                <h2>
                    <i class="fas fa-ticket-alt me-2" style="color: var(--secondary-dark);"></i>
                    Mes inscriptions
                </h2>
                <a href="../../index.php" class="btn btn-back">
                    <i class="fas fa-arrow-left me-2"></i>Découvrir d'autres événements
                </a>
            </div>

            <?php if (empty($participations)): ?>
            <!-- ========== ÉTAT VIDE ========== -->
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h3>Aucune participation pour le moment</h3>
                <p class="text-muted">Vous n'êtes inscrit à aucun événement.</p>
                <a href="../../index.php" class="btn btn-back mt-3">
                    <i class="fas fa-calendar-alt me-2"></i>Découvrir les événements
                </a>
            </div>
            <?php else: ?>
            <!-- ========== LISTE DES PARTICIPATIONS ========== -->
            <div class="participations-list">
                <?php foreach($participations as $index => $part): 
                    $isPassed = $part['date_evenement'] < date('Y-m-d');
                ?>
                <div class="participation-card">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="event-title mb-2"><?php echo htmlspecialchars($part['titre']); ?></h4>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="event-category">
                                    <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($part['categorie_nom'] ?? 'Non catégorisé'); ?>
                                </span>
                                <span class="event-date-badge">
                                    <i class="fas fa-calendar-alt"></i> Inscrit le <?php echo date('d/m/Y', strtotime($part['date_participation'])); ?>
                                </span>
                                <span class="event-status <?php echo $isPassed ? 'passed' : ''; ?>">
                                    <i class="fas <?php echo $isPassed ? 'fa-check-double' : 'fa-clock'; ?>"></i>
                                    <?php echo $isPassed ? 'Événement passé' : 'À venir'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="mb-2">
                                <span class="badge bg-success fs-6 p-2">
                                    <i class="fas fa-check-circle me-1"></i> <?php echo $part['statut']; ?>
                                </span>
                            </div>
                            <?php if (!$isPassed): ?>
                            <button type="button" class="btn btn-cancel" data-bs-toggle="modal" data-bs-target="#cancelModal<?php echo $index; ?>">
                                <i class="fas fa-times me-2"></i>Annuler
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body-custom">
                        <div class="info-row">
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($part['lieu']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-calendar-day"></i>
                                <span><?php echo date('l d F Y', strtotime($part['date_evenement'])); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-clock"></i>
                                <span><?php echo $part['heure']; ?></span>
                            </div>
                        </div>
                        <?php if (!empty($part['description'])): ?>
                        <div class="event-description">
                            <i class="fas fa-align-left me-2" style="color: var(--primary-dark);"></i>
                            <?php echo nl2br(htmlspecialchars(substr($part['description'], 0, 150))); ?>
                            <?php if (strlen($part['description']) > 150): ?>...<?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Modal de confirmation d'annulation -->
                <div class="modal fade" id="cancelModal<?php echo $index; ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header" style="border-bottom: 2px solid #dc3545;">
                                <h5 class="modal-title" style="color: #dc3545;">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Annuler ma participation
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Êtes-vous sûr de vouloir annuler votre participation à :</p>
                                <div class="alert alert-light border">
                                    <strong><i class="fas fa-calendar-alt me-2"></i><?php echo htmlspecialchars($part['titre']); ?></strong><br>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($part['lieu']); ?> |
                                        <i class="fas fa-calendar me-1"></i><?php echo date('d/m/Y', strtotime($part['date_evenement'])); ?>
                                    </small>
                                </div>
                                <p class="text-danger mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Cette action est irréversible.
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-arrow-left me-1"></i>Fermer
                                </button>
                                <a href="annuler.php?event_id=<?php echo $part['event_id']; ?>" class="btn btn-danger">
                                    <i class="fas fa-trash me-1"></i>Confirmer l'annulation
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========== FOOTER ========== -->
    <footer class="text-center py-4 mt-4" style="background: white; border-top: 1px solid #e0e0e0;">
        <div class="container">
            <p class="text-muted mb-0">&copy; 2024 Smart Municipality - Tous droits réservés</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide toast after 5 seconds
        setTimeout(function() {
            var toast = document.querySelector('.toast-message');
            if (toast) {
                toast.style.opacity = '0';
                setTimeout(function() { toast.remove(); }, 300);
            }
        }, 5000);
    </script>
</body>
</html>