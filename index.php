<?php
session_start();
require_once __DIR__ . '/controller/EvenementC.php';
require_once __DIR__ . '/controller/ParticipationC.php';
require_once __DIR__ . '/controller/CategorieEvenementC.php';

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$userName = isset($_SESSION['prenom']) ? $_SESSION['prenom'] . ' ' . $_SESSION['nom'] : 'Invité';

$evenementC = new EvenementC();
$participationC = new ParticipationC();
$categorieC = new CategorieEvenementC();

$evenements = $evenementC->afficherEvenementsAVenir();
$categories = $categorieC->afficherCategories();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categorie_id = isset($_GET['categorie_id']) ? $_GET['categorie_id'] : '';
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';

if (!empty($search) || !empty($categorie_id) || !empty($filter_date)) {
    $evenements = array_filter($evenements, function($event) use ($search, $categorie_id, $filter_date) {
        if (!empty($search) && stripos($event['titre'], $search) === false && stripos($event['description'], $search) === false) {
            return false;
        }
        if (!empty($categorie_id) && $event['categorie_id'] != $categorie_id) {
            return false;
        }
        if (!empty($filter_date)) {
            $eventDate = date('Y-m-d', strtotime($event['date_evenement']));
            if ($filter_date == 'today' && $eventDate != date('Y-m-d')) return false;
            if ($filter_date == 'week' && $eventDate > date('Y-m-d', strtotime('+7 days'))) return false;
            if ($filter_date == 'month' && $eventDate > date('Y-m-d', strtotime('+30 days'))) return false;
        }
        return true;
    });
}

$message = '';
$messageType = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'inscrit') {
        $message = '✅ Vous êtes inscrit à l\'événement avec succès !';
        $messageType = 'success';
    } elseif ($_GET['success'] == 'annule') {
        $message = 'ℹ️ Votre participation a été annulée.';
        $messageType = 'info';
    } elseif ($_GET['success'] == 'ajout') {
        $message = '✅ Événement ajouté avec succès !';
        $messageType = 'success';
    } elseif ($_GET['success'] == 'modif') {
        $message = '✅ Événement modifié avec succès !';
        $messageType = 'success';
    } elseif ($_GET['success'] == 'suppr') {
        $message = '✅ Événement supprimé avec succès !';
        $messageType = 'success';
    }
}
if (isset($_GET['error'])) {
    $message = '❌ ' . htmlspecialchars($_GET['error']);
    $messageType = 'danger';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality - Événements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1a5e2a;
            --primary-dark-hover: #0d3b1a;
            --secondary-dark: #2e7d32;
            --bg-dark: #e8f3e8;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-dark);
            min-height: 100vh;
        }
        .navbar {
            background: white;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            padding: 0.8rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-brand {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--primary-dark) !important;
            display: flex;
            align-items: center;
        }
        .navbar-brand img {
            border-radius: 10px;
            margin-right: 12px;
            transition: transform 0.3s ease;
        }
        .navbar-brand:hover img {
            transform: scale(1.05);
        }
        .nav-link {
            color: #4a5568;
            font-weight: 500;
            transition: all 0.3s;
            margin: 0 5px;
        }
        .nav-link:hover {
            color: var(--primary-dark);
            transform: translateY(-2px);
        }
        .nav-link.active {
            color: var(--primary-dark);
            border-bottom: 2px solid var(--primary-dark);
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .hero {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        .event-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(26, 94, 42, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }
        .event-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(26, 94, 42, 0.15);
        }
        .event-image {
            height: 180px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark));
        }
        .event-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .event-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(26, 94, 42, 0.9);
            backdrop-filter: blur(5px);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .event-content {
            padding: 20px;
        }
        .event-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #0d3b1a;
        }
        .event-info {
            color: #718096;
            font-size: 0.875rem;
            margin-bottom: 8px;
        }
        .event-info i {
            width: 20px;
            color: var(--primary-dark);
        }
        .btn-primary-custom {
            background: var(--primary-dark);
            border: none;
            transition: all 0.3s;
        }
        .btn-primary-custom:hover {
            background: var(--primary-dark-hover);
            transform: translateY(-2px);
        }
        .btn-outline-custom {
            border: 2px solid var(--primary-dark);
            color: var(--primary-dark);
            background: transparent;
        }
        .btn-outline-custom:hover {
            background: var(--primary-dark);
            color: white;
        }
        .btn-inscrit {
            background-color: var(--secondary-dark);
            color: white;
            cursor: default;
        }
        .filter-section {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .toast-message {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
        }
        .empty-state i {
            font-size: 4rem;
            color: var(--primary-dark);
            margin-bottom: 20px;
        }
        .footer {
            background: white;
            text-align: center;
            padding: 30px;
            margin-top: 50px;
            color: #718096;
        }
        .logo-footer {
            height: 30px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Toast Notification -->
    <?php if ($message): ?>
    <div class="toast-message">
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show shadow-lg border-0 rounded-3" role="alert">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : ($messageType == 'danger' ? 'exclamation-triangle' : 'info-circle'); ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- ========== NAVBAR AVEC LOGO ========== -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="logo.jpeg" alt="Smart Municipality" height="40" width="40" style="object-fit: cover;">
                Smart Municipality
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-calendar-alt me-1"></i> Événements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-users me-1"></i> Utilisateurs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-calendar-check me-1"></i> Rendez-vous
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-blog me-1"></i> Blog
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-exclamation-triangle me-1"></i> Signalements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-headset me-1"></i> Services
                        </a>
                    </li>
                    <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="views/participation/mes_participations.php">
                            <i class="fas fa-ticket-alt me-1"></i> Mes participations
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <div class="user-info">
                    <?php if ($isLoggedIn): ?>
                        <span class="text-muted">
                            <i class="fas fa-user-circle me-1" style="color: var(--primary-dark);"></i>
                            <?php echo htmlspecialchars($userName); ?>
                        </span>
                        <?php if ($isAdmin): ?>
                        <a href="views/dashboard/admin.php" class="btn btn-sm btn-primary-custom">
                            <i class="fas fa-chart-line me-1"></i> Dashboard
                        </a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn btn-sm btn-outline-custom">
                            <i class="fas fa-sign-out-alt me-1"></i> Déconnexion
                        </a>
                    <?php else: ?>
                        <a href="views/auth/login.php" class="btn btn-sm btn-outline-custom">
                            <i class="fas fa-sign-in-alt me-1"></i> Connexion
                        </a>
                        <a href="views/auth/register.php" class="btn btn-sm btn-primary-custom">
                            <i class="fas fa-user-plus me-1"></i> Inscription
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1><i class="fas fa-calendar-alt me-3"></i>Événements Municipaux</h1>
            <p class="lead">Découvrez et participez aux événements de votre ville</p>
            <?php if ($isLoggedIn): ?>
            <div class="mt-3">
                <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                    <i class="fas fa-user me-1" style="color: var(--primary-dark);"></i> 
                    Bienvenue, <?php echo htmlspecialchars($userName); ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="container mt-4">
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-color: var(--primary-dark);">
                            <i class="fas fa-search" style="color: var(--primary-dark);"></i>
                        </span>
                        <input type="text" name="search" class="form-control" style="border-color: var(--primary-dark);" 
                               placeholder="Rechercher un événement..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="categorie_id" class="form-select" style="border-color: var(--primary-dark);">
                        <option value="">Toutes les catégories</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $categorie_id == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nom']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="filter_date" class="form-select" style="border-color: var(--primary-dark);">
                        <option value="">Toutes les dates</option>
                        <option value="today" <?php echo $filter_date == 'today' ? 'selected' : ''; ?>>Aujourd'hui</option>
                        <option value="week" <?php echo $filter_date == 'week' ? 'selected' : ''; ?>>Cette semaine</option>
                        <option value="month" <?php echo $filter_date == 'month' ? 'selected' : ''; ?>>Ce mois</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary-custom w-100">
                        <i class="fas fa-filter me-1"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>

        <!-- Admin Add Button -->
        <?php if ($isAdmin): ?>
        <div class="text-end mb-3">
            <a href="views/evenement/ajouter.php" class="btn btn-primary-custom">
                <i class="fas fa-plus me-2"></i>Ajouter un événement
            </a>
        </div>
        <?php endif; ?>

        <!-- Events List -->
        <div class="row">
            <?php if (empty($evenements)): ?>
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Aucun événement trouvé</h3>
                    <p class="text-muted">Aucun événement ne correspond à vos critères.</p>
                    <a href="index.php" class="btn btn-primary-custom mt-3">
                        <i class="fas fa-sync-alt me-1"></i> Voir tous les événements
                    </a>
                </div>
            </div>
            <?php else: ?>
                <?php foreach($evenements as $event): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="event-card">
                        <div class="event-image">
                            <?php 
                            $categorieImage = !empty($event['categorie_image']) ? $event['categorie_image'] : null;
                            $imagePath = !empty($categorieImage) ? __DIR__ . '/' . $categorieImage : null;
                            
                            if ($categorieImage && file_exists($imagePath)): 
                            ?>
                                <img src="<?php echo $categorieImage; ?>" alt="<?php echo htmlspecialchars($event['categorie_nom'] ?? 'Catégorie'); ?>">
                            <?php else: ?>
                                <i class="fas fa-calendar-week" style="font-size: 3rem; color: rgba(255,255,255,0.7);"></i>
                            <?php endif; ?>
                            <span class="event-badge"><?php echo htmlspecialchars($event['categorie_nom'] ?? 'Non catégorisé'); ?></span>
                        </div>
                        <div class="event-content">
                            <h5 class="event-title"><?php echo htmlspecialchars($event['titre']); ?></h5>
                            <div class="event-info">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['lieu']); ?>
                            </div>
                            <div class="event-info">
                                <i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?>
                            </div>
                            <div class="event-info">
                                <i class="fas fa-clock"></i> <?php echo $event['heure']; ?>
                            </div>
                            <p class="event-description">
                                <?php echo substr(htmlspecialchars($event['description']), 0, 100); ?>...
                            </p>
                            
                            <?php if ($isLoggedIn): ?>
                                <?php if ($isAdmin): ?>
                                <div class="d-flex gap-2 mt-3">
                                    <a href="views/evenement/modifier.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="views/evenement/supprimer.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </div>
                                <?php else: ?>
                                    <?php 
                                    $inscrit = $participationC->estInscrit($_SESSION['user_id'], $event['id']);
                                    ?>
                                    <?php if ($inscrit): ?>
                                    <button class="btn btn-inscrit w-100 mt-3" disabled>
                                        <i class="fas fa-check-circle me-2"></i> Vous êtes inscrit
                                    </button>
                                    <?php else: ?>
                                    <a href="views/participation/ajouter.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary-custom w-100 mt-3">
                                        <i class="fas fa-ticket-alt me-2"></i> S'inscrire
                                    </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php else: ?>
                            <a href="views/auth/login.php" class="btn btn-outline-custom w-100 mt-3">
                                <i class="fas fa-sign-in-alt me-2"></i> Connectez-vous pour participer
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer avec logo -->
    <footer class="footer">
        <div class="container">
            <img src="logo.jpeg" alt="Smart Municipality" height="40" style="border-radius: 10px; margin-bottom: 15px;">
            <p>&copy; 2024 Smart Municipality - Tous droits réservés</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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