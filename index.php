<?php
session_start();
require_once __DIR__ . '/controller/EvenementC.php';
require_once __DIR__ . '/controller/ParticipationC.php';
require_once __DIR__ . '/controller/CategorieEvenementC.php';

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$userName = isset($_SESSION['prenom']) ? $_SESSION['prenom'] . ' ' . $_SESSION['nom'] : 'Invité';
$userEmail = isset($_SESSION['email']) ? $_SESSION['email'] : '';

$evenementC = new EvenementC();
$participationC = new ParticipationC();
$categorieC = new CategorieEvenementC();

$evenements = $evenementC->afficherEvenementsAVenir();
$tousEvenements = $evenementC->afficherEvenements();
$categories = $categorieC->afficherCategories();

// Trouver l'événement le plus proche
$evenementProche = null;
if (!empty($evenements)) {
    $evenementProche = $evenements[0];
    foreach ($evenements as $e) {
        if (strtotime($e['date_evenement']) < strtotime($evenementProche['date_evenement'])) {
            $evenementProche = $e;
        }
    }
}

// Filtres
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categorie_id = isset($_GET['categorie_id']) ? $_GET['categorie_id'] : '';
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'date';
$sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] === 'asc' ? 'asc' : 'desc';

$evenementsFiltres = $evenements;

// Appliquer les filtres
if (!empty($search) || !empty($categorie_id) || !empty($filter_date)) {
    $evenementsFiltres = array_filter($evenements, function($event) use ($search, $categorie_id, $filter_date) {
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

// Appliquer le tri
usort($evenementsFiltres, function($a, $b) use ($sort_by, $sort_order) {
    $val1 = $a[$sort_by] ?? '';
    $val2 = $b[$sort_by] ?? '';
    
    if ($sort_by == 'places') {
        $db = config::getConnexion();
        $query = $db->prepare('SELECT COALESCE(SUM(nombre_participants), 0) as total FROM participations WHERE event_id = :id AND statut_validation = "valide"');
        $query->execute(['id' => $a['id']]);
        $placesA = $a['max_participants'] - $query->fetch(PDO::FETCH_ASSOC)['total'];
        $query->execute(['id' => $b['id']]);
        $placesB = $b['max_participants'] - $query->fetch(PDO::FETCH_ASSOC)['total'];
        $val1 = $placesA;
        $val2 = $placesB;
    } elseif ($sort_by == 'date') {
        $val1 = strtotime($a['date_evenement']);
        $val2 = strtotime($b['date_evenement']);
    }
    
    if ($sort_order === 'asc') {
        return $val1 <=> $val2;
    } else {
        return $val2 <=> $val1;
    }
});

$message = '';
$messageType = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'inscrit') {
        $message = '✅ Votre inscription a été envoyée ! En attente de validation.';
        $messageType = 'success';
    } elseif ($_GET['success'] == 'annule') {
        $message = 'ℹ️ Votre participation a été annulée.';
        $messageType = 'info';
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js'></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
        :root {
            --primary: #1a5e2a;
            --primary-dark: #0d3b1a;
            --primary-light: #2e7d32;
            --secondary: #4caf50;
            --gradient: linear-gradient(135deg, #1a5e2a, #4caf50);
            --shadow: 0 5px 15px rgba(0,0,0,0.05);
            --shadow-hover: 0 10px 25px rgba(26,94,42,0.15);
            --radius: 16px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #f0f4f0 100%);
            min-height: 100vh;
        }
        .navbar {
            background: white;
            box-shadow: var(--shadow);
            padding: 0.75rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.35rem;
            color: var(--primary) !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-brand img { border-radius: 10px; }
        .nav-link {
            font-weight: 500;
            color: #4a5568;
            transition: all 0.2s;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--primary);
            background: #e8f5e9;
        }
        /* Hero Section */
        .hero-proche {
            background: var(--gradient);
            padding: 30px 0;
            margin-bottom: 30px;
        }
        .hero-proche .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }
        .hero-proche .info { color: white; }
        .hero-proche .info h2 {
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            opacity: 0.8;
            margin-bottom: 5px;
        }
        .hero-proche .info h1 { font-size: 1.8rem; font-weight: 700; margin-bottom: 10px; }
        .hero-proche .info p { margin-bottom: 5px; opacity: 0.9; }
        .hero-proche .info i { margin-right: 8px; }
        .hero-proche .date-box {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 15px 25px;
            text-align: center;
            color: white;
            min-width: 120px;
        }
        .hero-proche .date-box .jour { font-size: 2.5rem; font-weight: 700; line-height: 1; }
        .hero-proche .date-box .mois { font-size: 1rem; text-transform: uppercase; }
        /* Filter */
        .filter-bar {
            background: white;
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
        }
        /* Sort buttons */
        .sort-buttons {
            background: white;
            border-radius: var(--radius);
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .sort-buttons .label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--primary);
            margin-right: 0.5rem;
        }
        .sort-btn {
            background: #f0f4f0;
            border: none;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            color: #555;
            transition: all 0.2s;
            cursor: pointer;
        }
        .sort-btn:hover { background: var(--primary-light); color: white; }
        .sort-btn.active { background: var(--primary); color: white; }
        .sort-btn i { margin-right: 4px; }
        /* Event Card */
        .event-card {
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(0,0,0,0.05);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .event-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-hover); }
        .event-image {
            height: 160px;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .event-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        .event-card:hover .event-image img { transform: scale(1.05); }
        .event-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(26,94,42,0.9);
            backdrop-filter: blur(5px);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .event-content { padding: 1rem; flex: 1; }
        .event-title { font-weight: 700; color: var(--primary); margin-bottom: 0.5rem; font-size: 1rem; }
        .event-info { color: #666; font-size: 0.75rem; margin-bottom: 0.3rem; }
        .event-info i { width: 18px; color: var(--primary); font-size: 0.7rem; }
        .progress-bar-custom {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin: 8px 0;
        }
        .btn-primary-custom {
            background: var(--gradient);
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.7rem;
            transition: all 0.2s;
            color: white;
        }
        .btn-primary-custom:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); }
        .btn-outline-custom {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
            padding: 5px 12px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.7rem;
        }
        .btn-outline-custom:hover { background: var(--primary); color: white; }
        .btn-inscrit { background: var(--primary-light); color: white; cursor: default; opacity: 0.8; font-size: 0.7rem; padding: 6px; border-radius: 8px; }
        .btn-complet { background: #9e9e9e; color: white; cursor: not-allowed; font-size: 0.7rem; padding: 6px; border-radius: 8px; }
        .btn-en-attente { background: #ff9800; color: white; cursor: default; font-size: 0.7rem; padding: 6px; border-radius: 8px; }
        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        .modal-container {
            background: white;
            border-radius: 24px;
            max-width: 420px;
            width: 90%;
            overflow: hidden;
            animation: modalSlideIn 0.2s ease;
        }
        @keyframes modalSlideIn {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-header {
            background: var(--gradient);
            padding: 15px;
            text-align: center;
            color: white;
        }
        .modal-header i { font-size: 2rem; margin-bottom: 5px; }
        .modal-header h3 { margin: 0; font-size: 1.2rem; }
        .modal-body { padding: 15px; }
        .modal-footer {
            padding: 10px 15px 15px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            border-top: 1px solid #e9ecef;
        }
        .event-details-modal {
            background: #e8f5e9;
            border-radius: 12px;
            padding: 10px;
            margin: 10px 0;
            font-size: 0.8rem;
        }
        .event-details-modal p { margin-bottom: 5px; }
        .event-details-modal i { width: 20px; color: var(--primary); }
        .toast-message {
            position: fixed;
            top: 70px;
            right: 20px;
            z-index: 10001;
            animation: slideInRight 0.3s ease;
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        /* Calendar */
        .calendar-container {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            margin-top: 30px;
            box-shadow: var(--shadow);
        }
        .fc { font-family: 'Inter', sans-serif; }
        .fc-event {
            background: var(--gradient);
            border: none;
            cursor: pointer;
            border-radius: 6px;
            font-size: 0.7rem;
            transition: all 0.2s;
        }
        .fc-event:hover { opacity: 0.9; transform: scale(1.02); }
        .fc-col-header-cell-cushion { font-weight: 600; color: var(--primary); text-transform: uppercase; font-size: 0.7rem; }
        .fc-toolbar-title { font-size: 1rem !important; font-weight: 700 !important; color: var(--primary) !important; }
        .fc-button { background: var(--primary) !important; border: none !important; border-radius: 8px !important; padding: 4px 10px !important; font-size: 0.7rem !important; }
        /* Footer */
        .footer {
            background: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 2rem;
            color: #666;
            font-size: 0.8rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        .empty-state { text-align: center; padding: 2rem; background: white; border-radius: var(--radius); }
        .empty-state i { font-size: 2.5rem; color: var(--primary-light); margin-bottom: 1rem; }
        @media (max-width: 768px) {
            .hero-proche .info h1 { font-size: 1.3rem; }
            .hero-proche .date-box { padding: 10px 15px; }
            .hero-proche .date-box .jour { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

    <!-- Toast Notification -->
    <?php if ($message): ?>
    <div class="toast-message">
        <div class="alert alert-<?php echo $messageType; ?> shadow rounded-3 border-0 py-2 px-3">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" style="font-size: 0.7rem;"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal d'inscription -->
    <div id="inscriptionModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <i class="fas fa-ticket-alt"></i>
                <h3>Confirmer l'inscription</h3>
            </div>
            <div class="modal-body">
                <div class="event-details-modal">
                    <p><i class="fas fa-calendar-alt"></i> <strong id="modalTitle">-</strong></p>
                    <p><i class="fas fa-map-marker-alt"></i> Lieu : <span id="modalLieu">-</span></p>
                    <p><i class="fas fa-calendar-day"></i> Date : <span id="modalDate">-</span> à <span id="modalHeure">-</span></p>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Nombre de participants</label>
                    <input type="number" id="nbParticipants" class="form-control form-control-sm" min="1" max="10" value="1">
                    <small class="text-muted" style="font-size: 0.65rem;">Maximum 10 personnes</small>
                </div>
                <div class="alert alert-warning py-1 px-2 mb-0" style="font-size: 0.75rem;">
                    <i class="fas fa-info-circle me-1"></i>
                    Places restantes : <strong id="placesRestantes">-</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" id="closeModalBtn">Annuler</button>
                <button type="button" class="btn btn-primary-custom btn-sm" id="confirmModalBtn">Confirmer</button>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="logo.jpeg" alt="Logo" height="35">
                Smart Municipality
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-calendar-alt me-1"></i> Événements</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-users me-1"></i> Utilisateurs</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-calendar-check me-1"></i> Rendez-vous</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-blog me-1"></i> Blog</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-exclamation-triangle me-1"></i> Signalements</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-headset me-1"></i> Services</a></li>
                    <?php if ($isLoggedIn): ?>
                    <li class="nav-item"><a class="nav-link" href="views/participation/mes_participations.php"><i class="fas fa-ticket-alt me-1"></i> Mes inscriptions</a></li>
                    <?php endif; ?>
                </ul>
                <div class="dropdown">
                    <button class="btn btn-outline-custom dropdown-toggle" data-bs-toggle="dropdown" style="font-size: 0.8rem; padding: 5px 12px;">
                        <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($userName); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if ($isAdmin): ?>
                        <li><a class="dropdown-item" href="views/dashboard/admin.php"><i class="fas fa-chart-line me-2"></i>Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Section Événement le plus proche -->
    <?php if ($evenementProche): 
        $dateObj = new DateTime($evenementProche['date_evenement']);
        $placesTotal = $evenementProche['max_participants'];
        $placesValidees = $participationC->compterParticipationsValidees($evenementProche['id']);
        $placesRestantes = $placesTotal - $placesValidees;
    ?>
    <section class="hero-proche">
        <div class="container">
            <div class="info">
                <h2><i class="fas fa-star me-1"></i> Événement à ne pas manquer</h2>
                <h1><?php echo htmlspecialchars($evenementProche['titre']); ?></h1>
                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($evenementProche['lieu']); ?></p>
                <p><i class="fas fa-users"></i> <?php echo $placesRestantes; ?> places restantes</p>
                <?php if ($isLoggedIn && !$isAdmin && !$participationC->estInscrit($_SESSION['user_id'], $evenementProche['id']) && $placesRestantes > 0): ?>
                    <button class="btn btn-light btn-sm mt-2 btn-inscrire-proche" data-id="<?php echo $evenementProche['id']; ?>" data-title="<?php echo htmlspecialchars($evenementProche['titre']); ?>" data-lieu="<?php echo htmlspecialchars($evenementProche['lieu']); ?>" data-date="<?php echo date('d/m/Y', strtotime($evenementProche['date_evenement'])); ?>" data-heure="<?php echo $evenementProche['heure']; ?>" data-places="<?php echo $placesRestantes; ?>" style="color: var(--primary); font-weight: 600;">
                        <i class="fas fa-ticket-alt me-1"></i> Je m'inscris maintenant
                    </button>
                <?php endif; ?>
            </div>
            <div class="date-box">
                <div class="jour"><?php echo $dateObj->format('d'); ?></div>
                <div class="mois"><?php echo $dateObj->format('M'); ?></div>
                <div class="annee"><?php echo $dateObj->format('Y'); ?></div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <div class="container">
        <!-- Filtres -->
        <div class="filter-bar">
            <form method="GET" class="row g-2" id="filterForm">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-success"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Rechercher un événement..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="categorie_id" class="form-select" id="categorieSelect">
                        <option value="">Toutes les catégories</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $categorie_id == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['nom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="filter_date" class="form-select" id="dateSelect">
                        <option value="">Toutes les dates</option>
                        <option value="today" <?php echo $filter_date == 'today' ? 'selected' : ''; ?>>Aujourd'hui</option>
                        <option value="week" <?php echo $filter_date == 'week' ? 'selected' : ''; ?>>Cette semaine</option>
                        <option value="month" <?php echo $filter_date == 'month' ? 'selected' : ''; ?>>Ce mois</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary-custom w-100"><i class="fas fa-filter me-1"></i> Filtrer</button>
                </div>
            </form>
        </div>

        <!-- Boutons de tri -->
        <div class="sort-buttons">
            <span class="label"><i class="fas fa-sort me-1"></i> Trier par :</span>
            <button type="button" class="sort-btn <?php echo $sort_by == 'date' ? 'active' : ''; ?>" data-sort="date" data-order="<?php echo $sort_by == 'date' && $sort_order == 'asc' ? 'desc' : 'asc'; ?>">
                <i class="fas fa-calendar-day"></i> Date
                <?php if ($sort_by == 'date'): ?>
                    <i class="fas fa-sort-<?php echo $sort_order == 'asc' ? 'up' : 'down'; ?>"></i>
                <?php endif; ?>
            </button>
            <button type="button" class="sort-btn <?php echo $sort_by == 'titre' ? 'active' : ''; ?>" data-sort="titre" data-order="<?php echo $sort_by == 'titre' && $sort_order == 'asc' ? 'desc' : 'asc'; ?>">
                <i class="fas fa-font"></i> Titre
                <?php if ($sort_by == 'titre'): ?>
                    <i class="fas fa-sort-<?php echo $sort_order == 'asc' ? 'up' : 'down'; ?>"></i>
                <?php endif; ?>
            </button>
            <button type="button" class="sort-btn <?php echo $sort_by == 'lieu' ? 'active' : ''; ?>" data-sort="lieu" data-order="<?php echo $sort_by == 'lieu' && $sort_order == 'asc' ? 'desc' : 'asc'; ?>">
                <i class="fas fa-map-marker-alt"></i> Lieu
                <?php if ($sort_by == 'lieu'): ?>
                    <i class="fas fa-sort-<?php echo $sort_order == 'asc' ? 'up' : 'down'; ?>"></i>
                <?php endif; ?>
            </button>
            <button type="button" class="sort-btn <?php echo $sort_by == 'places' ? 'active' : ''; ?>" data-sort="places" data-order="<?php echo $sort_by == 'places' && $sort_order == 'asc' ? 'desc' : 'asc'; ?>">
                <i class="fas fa-users"></i> Places disponibles
                <?php if ($sort_by == 'places'): ?>
                    <i class="fas fa-sort-<?php echo $sort_order == 'asc' ? 'up' : 'down'; ?>"></i>
                <?php endif; ?>
            </button>
            <input type="hidden" name="sort_by" id="sort_by" value="<?php echo $sort_by; ?>">
            <input type="hidden" name="sort_order" id="sort_order" value="<?php echo $sort_order; ?>">
        </div>

        <!-- Admin Add Button -->
        <?php if ($isAdmin): ?>
        <div class="text-end mb-3">
            <a href="views/evenement/ajouter.php" class="btn btn-primary-custom">
                <i class="fas fa-plus me-1"></i> Ajouter un événement
            </a>
        </div>
        <?php endif; ?>

        <!-- Liste des événements -->
        <div class="row" id="eventsContainer">
            <?php if (empty($evenementsFiltres)): ?>
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h5>Aucun événement trouvé</h5>
                    <a href="index.php" class="btn btn-primary-custom btn-sm mt-2">Actualiser</a>
                </div>
            </div>
            <?php else: ?>
                <?php foreach($evenementsFiltres as $event): 
                    $placesTotal = $event['max_participants'];
                    $placesValidees = $participationC->compterParticipationsValidees($event['id']);
                    $placesRestantes = $placesTotal - $placesValidees;
                    $pourcentage = $placesTotal > 0 ? round(($placesValidees / $placesTotal) * 100) : 0;
                    $estComplet = $placesRestantes <= 0;
                    $estInscrit = false;
                    $statutValidation = null;
                    if ($isLoggedIn && !$isAdmin) {
                        $estInscrit = $participationC->estInscrit($_SESSION['user_id'], $event['id']);
                        $statutValidation = $participationC->getStatutValidation($_SESSION['user_id'], $event['id']);
                    }
                ?>
                <div class="col-md-6 col-lg-4 event-card-wrapper"
                     data-id="<?php echo $event['id']; ?>"
                     data-title="<?php echo htmlspecialchars($event['titre']); ?>"
                     data-lieu="<?php echo htmlspecialchars($event['lieu']); ?>"
                     data-date="<?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?>"
                     data-heure="<?php echo $event['heure']; ?>"
                     data-places="<?php echo $placesRestantes; ?>">
                    <div class="event-card">
                        <div class="event-image">
                            <?php $img = !empty($event['categorie_image']) && file_exists($event['categorie_image']) ? $event['categorie_image'] : null; ?>
                            <?php if ($img): ?>
                                <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($event['categorie_nom'] ?? ''); ?>">
                            <?php else: ?>
                                <i class="fas fa-calendar-week fa-2x" style="color: rgba(255,255,255,0.7);"></i>
                            <?php endif; ?>
                            <span class="event-badge"><?php echo htmlspecialchars($event['categorie_nom'] ?? 'Non catégorisé'); ?></span>
                        </div>
                        <div class="event-content">
                            <h5 class="event-title"><?php echo htmlspecialchars($event['titre']); ?></h5>
                            <div class="event-info"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['lieu']); ?></div>
                            <div class="event-info"><i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?> à <?php echo $event['heure']; ?></div>
                            <div class="mt-2">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span><i class="fas fa-users"></i> Places validées</span>
                                    <span><?php echo $placesValidees; ?>/<?php echo $placesTotal; ?></span>
                                </div>
                                <div class="progress-bar-custom">
                                    <div style="width: <?php echo $pourcentage; ?>%; height: 4px; background: #4caf50;"></div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <?php if ($estInscrit): ?>
                                    <?php if ($statutValidation == 'en_attente'): ?>
                                        <button class="btn btn-en-attente w-100" disabled><i class="fas fa-clock me-1"></i> En attente</button>
                                    <?php elseif ($statutValidation == 'valide'): ?>
                                        <button class="btn btn-inscrit w-100" disabled><i class="fas fa-check-circle me-1"></i> Inscrit</button>
                                    <?php else: ?>
                                        <button class="btn btn-complet w-100" disabled><i class="fas fa-times-circle me-1"></i> Refusé</button>
                                    <?php endif; ?>
                                <?php elseif ($estComplet): ?>
                                    <button class="btn btn-complet w-100" disabled><i class="fas fa-times-circle me-1"></i> Complet</button>
                                <?php elseif ($isLoggedIn && !$isAdmin): ?>
                                    <button class="btn btn-primary-custom w-100 btn-inscrire"><i class="fas fa-ticket-alt me-1"></i> S'inscrire</button>
                                <?php elseif (!$isLoggedIn): ?>
                                    <a href="views/auth/login.php" class="btn btn-outline-custom w-100"><i class="fas fa-sign-in-alt me-1"></i> Se connecter</a>
                                <?php endif; ?>
                                <?php if ($isAdmin): ?>
                                <div class="d-flex gap-2 mt-2">
                                    <a href="views/evenement/modifier.php?id=<?php echo $event['id']; ?>" class="btn btn-warning btn-sm flex-grow-1"><i class="fas fa-edit"></i> Modifier</a>
                                    <a href="views/evenement/participants.php?id=<?php echo $event['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-users"></i></a>
                                    <a href="views/evenement/supprimer.php?id=<?php echo $event['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Calendrier -->
        <div class="calendar-container">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p class="mb-0">&copy; 2024 Smart Municipality - Tous droits réservés</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Modal elements
        const modal = document.getElementById('inscriptionModal');
        const closeBtn = document.getElementById('closeModalBtn');
        const confirmBtn = document.getElementById('confirmModalBtn');
        const nbInput = document.getElementById('nbParticipants');
        
        let currentEventId = null;
        let currentPlaces = 0;
        
        function openModal(eventId, title, lieu, date, heure, places) {
            currentEventId = eventId;
            currentPlaces = places;
            document.getElementById('modalTitle').innerHTML = title;
            document.getElementById('modalLieu').innerHTML = lieu;
            document.getElementById('modalDate').innerHTML = date;
            document.getElementById('modalHeure').innerHTML = heure;
            document.getElementById('placesRestantes').innerHTML = places;
            const maxVal = Math.min(10, places);
            nbInput.max = maxVal;
            nbInput.value = 1;
            modal.style.display = 'flex';
        }
        
        function closeModal() {
            modal.style.display = 'none';
            currentEventId = null;
        }
        
        function confirmInscription() {
            if (!currentEventId) return;
            let nb = parseInt(nbInput.value);
            if (isNaN(nb) || nb < 1) nb = 1;
            if (nb > currentPlaces) {
                alert(`⚠️ Il ne reste que ${currentPlaces} place(s) disponible(s).`);
                return;
            }
            window.location.href = `views/participation/ajouter.php?event_id=${currentEventId}&nb_participants=${nb}`;
        }
        
        if (closeBtn) closeBtn.onclick = closeModal;
        if (confirmBtn) confirmBtn.onclick = confirmInscription;
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') closeModal();
        });
        
        // Boutons d'inscription dans la liste
        document.querySelectorAll('.btn-inscrire').forEach(btn => {
            btn.addEventListener('click', function() {
                const wrapper = this.closest('.event-card-wrapper');
                if (wrapper) {
                    openModal(
                        wrapper.getAttribute('data-id'),
                        wrapper.getAttribute('data-title'),
                        wrapper.getAttribute('data-lieu'),
                        wrapper.getAttribute('data-date'),
                        wrapper.getAttribute('data-heure'),
                        wrapper.getAttribute('data-places')
                    );
                }
            });
        });
        
        // Bouton inscription événement proche
        const btnProche = document.querySelector('.btn-inscrire-proche');
        if (btnProche) {
            btnProche.addEventListener('click', function() {
                openModal(
                    this.getAttribute('data-id'),
                    this.getAttribute('data-title'),
                    this.getAttribute('data-lieu'),
                    this.getAttribute('data-date'),
                    this.getAttribute('data-heure'),
                    this.getAttribute('data-places')
                );
            });
        }
        
        // Auto-hide toast
        setTimeout(() => {
            const toast = document.querySelector('.toast-message');
            if (toast) {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);

        // ========== CALENDRIER AVEC PARTICIPATION ==========
        document.addEventListener('DOMContentLoaded', function() {
            // Récupérer tous les événements avec leurs détails pour le calendrier
            var events = <?php 
                $eventsArray = [];
                foreach($tousEvenements as $e) {
                    // Calculer les places restantes
                    $placesVal = $participationC->compterParticipationsValidees($e['id']);
                    $placesRest = $e['max_participants'] - $placesVal;
                    $isFull = $placesRest <= 0;
                    $userIsRegistered = false;
                    if ($isLoggedIn && !$isAdmin) {
                        $userIsRegistered = $participationC->estInscrit($_SESSION['user_id'], $e['id']);
                    }
                    
                    $eventsArray[] = [
                        'id' => $e['id'],
                        'title' => $e['titre'],
                        'start' => $e['date_evenement'],
                        'lieu' => $e['lieu'],
                        'heure' => $e['heure'],
                        'places_restantes' => $placesRest,
                        'places_total' => $e['max_participants'],
                        'description' => substr($e['description'], 0, 100),
                        'isFull' => $isFull,
                        'userRegistered' => $userIsRegistered,
                        'color' => $isFull ? '#9e9e9e' : ($userIsRegistered ? '#2e7d32' : '#1a5e2a'),
                        'textColor' => 'white'
                    ];
                }
                echo json_encode($eventsArray);
            ?>;
            
            var calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    locale: 'fr',
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek'
                    },
                    buttonText: { today: "Aujourd'hui", month: 'Mois', week: 'Semaine' },
                    events: events,
                    eventDidMount: function(info) {
                        // Tooltip avec les détails
                        const event = info.event;
                        const props = event.extendedProps;
                        let tooltip = `${event.title}\n📍 ${props.lieu}\n🕐 ${props.heure}\n👥 ${props.places_restantes}/${props.places_total} places`;
                        if (props.isFull) tooltip += '\n❌ COMPLET';
                        if (props.userRegistered) tooltip += '\n✅ Vous êtes inscrit';
                        info.el.setAttribute('title', tooltip);
                        
                        // Style différent si complet ou déjà inscrit
                        if (props.isFull) {
                            info.el.style.background = '#9e9e9e';
                            info.el.style.opacity = '0.7';
                        } else if (props.userRegistered) {
                            info.el.style.background = '#2e7d32';
                        } else {
                            info.el.style.background = '#1a5e2a';
                        }
                    },
                    eventClick: function(info) {
                        const event = info.event;
                        const props = event.extendedProps;
                        
                        <?php if ($isLoggedIn && !$isAdmin): ?>
                            // Si l'utilisateur est connecté et non admin
                            if (props.userRegistered) {
                                alert('✅ Vous êtes déjà inscrit à cet événement !');
                                return;
                            }
                            if (props.isFull) {
                                alert('❌ Désolé, cet événement est complet !');
                                return;
                            }
                            // Ouvrir le modal d'inscription
                            openModal(
                                event.id,
                                event.title,
                                props.lieu,
                                new Date(event.start).toLocaleDateString('fr-FR'),
                                props.heure,
                                props.places_restantes
                            );
                        <?php elseif ($isAdmin): ?>
                            // Si admin, rediriger vers la modification
                            window.location.href = `views/evenement/modifier.php?id=${event.id}`;
                        <?php else: ?>
                            // Si non connecté, rediriger vers login
                            alert('Veuillez vous connecter pour vous inscrire à cet événement.');
                            window.location.href = 'views/auth/login.php';
                        <?php endif; ?>
                    },
                    height: 450,
                    contentHeight: 'auto',
                    firstDay: 1,
                    dayMaxEvents: true
                });
                calendar.render();
            }
        });

        // Gestion du tri
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const sortBy = this.getAttribute('data-sort');
                let sortOrder = this.getAttribute('data-order');
                const search = document.querySelector('input[name="search"]').value;
                const categorie = document.getElementById('categorieSelect').value;
                const filterDate = document.getElementById('dateSelect').value;
                
                let url = `index.php?sort_by=${sortBy}&sort_order=${sortOrder}`;
                if (search) url += `&search=${encodeURIComponent(search)}`;
                if (categorie) url += `&categorie_id=${categorie}`;
                if (filterDate) url += `&filter_date=${filterDate}`;
                
                window.location.href = url;
            });
        });
    </script>
</body>
</html>