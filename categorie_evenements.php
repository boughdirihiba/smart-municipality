<?php
session_start();
require_once __DIR__ . '/controller/EvenementC.php';
require_once __DIR__ . '/controller/ParticipationC.php';
require_once __DIR__ . '/controller/CategorieEvenementC.php';

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$userId = $_SESSION['user_id'] ?? null;
$userName = isset($_SESSION['prenom']) ? $_SESSION['prenom'] . ' ' . $_SESSION['nom'] : 'Invité';
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'citoyen';

$evenementC = new EvenementC();
$participationC = new ParticipationC();
$categorieC = new CategorieEvenementC();

$categorie_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$categorie = $categorieC->afficherCategorieParId($categorie_id);

if (!$categorie) {
    header('Location: index.php');
    exit();
}

$tousEvenements = $evenementC->afficherEvenementsAVenir();
$evenements = array_filter($tousEvenements, function($event) use ($categorie_id) {
    return $event['categorie_id'] == $categorie_id;
});

// Recherche
$recherche = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($recherche)) {
    $evenements = array_filter($evenements, function($event) use ($recherche) {
        return stripos($event['titre'], $recherche) !== false || 
               stripos($event['description'], $recherche) !== false ||
               stripos($event['lieu'], $recherche) !== false;
    });
}

// Tri
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'date';
$sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] === 'asc' ? 'asc' : 'desc';

$evenementsArray = array_values($evenements);
usort($evenementsArray, function($a, $b) use ($sort_by, $sort_order, $participationC) {
    if ($sort_by == 'date') {
        $val1 = strtotime($a['date_evenement']);
        $val2 = strtotime($b['date_evenement']);
    } elseif ($sort_by == 'titre') {
        $val1 = strtolower($a['titre']);
        $val2 = strtolower($b['titre']);
    } elseif ($sort_by == 'lieu') {
        $val1 = strtolower($a['lieu']);
        $val2 = strtolower($b['lieu']);
    } elseif ($sort_by == 'places') {
        $val1 = $a['max_participants'] - $participationC->compterParticipationsValidees($a['id']);
        $val2 = $b['max_participants'] - $participationC->compterParticipationsValidees($b['id']);
    } else {
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
if (isset($_GET['success']) && $_GET['success'] == 'inscrit') {
    $message = '✅ Votre inscription a été envoyée ! En attente de validation.';
    $messageType = 'success';
}
if (isset($_GET['error'])) {
    $message = '❌ ' . htmlspecialchars($_GET['error']);
    $messageType = 'danger';
}

$nbEvenements = count($evenementsArray);
$totalPlaces = array_sum(array_column($evenementsArray, 'max_participants'));

// Préparer les événements pour le calendrier
$calendarEvents = [];
foreach ($evenementsArray as $e) {
    $placesRestantes = $e['max_participants'] - $participationC->compterParticipationsValidees($e['id']);
    $estComplet = $placesRestantes <= 0;
    $estInscrit = false;
    if ($isLoggedIn) {
        $estInscrit = $participationC->estInscrit($userId, $e['id']);
    }
    
    $calendarEvents[] = [
        'id' => $e['id'],
        'title' => $e['titre'],
        'start' => $e['date_evenement'],
        'lieu' => $e['lieu'],
        'heure' => $e['heure'],
        'places_restantes' => $placesRestantes,
        'places_total' => $e['max_participants'],
        'est_complet' => $estComplet,
        'est_inscrit' => $estInscrit,
        'color' => $estComplet ? '#9e9e9e' : ($estInscrit ? '#2e7d32' : '#1a5e2a'),
        'textColor' => 'white'
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($categorie['nom']); ?> - Smart Municipality</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js'></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
        :root {
            --primary: #1a5e2a;
            --primary-dark: #0d3b1a;
            --primary-light: #2e7d32;
            --gradient: linear-gradient(135deg, #1a5e2a, #4caf50);
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
            --shadow-md: 0 5px 15px rgba(0,0,0,0.05);
            --radius: 12px;
            --radius-lg: 20px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f0;
            min-height: 100vh;
        }
        
        /* ========== NAVBAR ========== */
        .navbar {
            background: white;
            box-shadow: var(--shadow-sm);
            padding: 0.75rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .nav-brand img { height: 35px; border-radius: 10px; }
        .nav-brand-text { font-weight: 700; font-size: 1.25rem; color: var(--primary); }
        .nav-brand-text span { color: #4caf50; }
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 10px;
        }
        .mobile-toggle span {
            display: block;
            width: 25px;
            height: 2px;
            background: var(--primary);
            margin: 5px 0;
        }
        .nav-links {
            display: flex;
            list-style: none;
            gap: 1.5rem;
            margin: 0;
            padding: 0;
        }
        .nav-links li a {
            text-decoration: none;
            color: #4a5568;
            font-weight: 500;
            transition: all 0.2s;
            padding: 0.5rem 0;
        }
        .nav-links li a:hover { color: var(--primary); }
        .nav-links li a.active { color: var(--primary); border-bottom: 2px solid var(--primary); }
        .nav-right { display: flex; align-items: center; gap: 1rem; }
        .nav-search {
            display: flex;
            align-items: center;
            background: #f5f5f5;
            border-radius: 30px;
            padding: 6px 15px;
            gap: 8px;
        }
        .nav-search-icon { color: #999; }
        .nav-search input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 0.8rem;
            width: 180px;
        }
        .user-info { display: flex; align-items: center; gap: 12px; }
        .user-avatar {
            width: 35px;
            height: 35px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        .btn-login {
            background: var(--gradient);
            border: none;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.8rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); }
        .btn-dashboard {
            background: transparent;
            border: 2px solid var(--primary);
            padding: 6px 18px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.75rem;
            color: var(--primary);
            text-decoration: none;
        }
        .btn-dashboard:hover { background: var(--gradient); color: white; }
        .btn-logout {
            background: transparent;
            border: 2px solid #dc2626;
            padding: 6px 18px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.75rem;
            color: #dc2626;
            text-decoration: none;
        }
        .btn-logout:hover { background: #dc2626; color: white; }
        
        /* Hero */
        .hero-categorie {
            background: var(--gradient);
            padding: 40px 0;
            text-align: center;
            color: white;
        }
        .hero-categorie h1 { font-size: 1.8rem; font-weight: 700; margin-bottom: 10px; }
        .hero-categorie p { font-size: 0.85rem; opacity: 0.9; }
        .hero-stats { display: flex; justify-content: center; gap: 20px; margin-top: 15px; }
        .hero-stat {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 5px 15px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.75rem;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            margin-bottom: 20px;
            background: rgba(255,255,255,0.15);
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 0.75rem;
        }
        .back-link:hover { background: rgba(255,255,255,0.25); transform: translateX(-3px); }
        
        /* Filter */
        .filter-bar {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-sm);
        }
        .sort-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            background: white;
            padding: 0.8rem 1rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1rem;
        }
        .sort-label { font-size: 0.75rem; font-weight: 600; color: var(--primary); margin-right: 10px; }
        .sort-btn {
            background: #f0f4f0;
            border: none;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            color: #555;
            cursor: pointer;
            text-decoration: none;
        }
        .sort-btn:hover, .sort-btn.active { background: var(--primary); color: white; }
        .btn-add-event {
            background: var(--gradient);
            border: none;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.75rem;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1rem;
        }
        .btn-add-event:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); }
        
        /* Event Card */
        .event-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            margin-bottom: 1.25rem;
            border: 1px solid rgba(0,0,0,0.03);
            overflow: hidden;
        }
        .event-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .event-card-inner { padding: 1.25rem; }
        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        .event-title { font-weight: 700; font-size: 1rem; color: var(--primary-dark); margin: 0; }
        .event-category {
            background: #e8f5e9;
            color: var(--primary);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
        }
        .event-details { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 12px; }
        .event-detail { display: flex; align-items: center; gap: 5px; font-size: 0.7rem; color: #666; }
        .event-detail i { width: 20px; color: var(--primary); }
        .event-description { font-size: 0.7rem; color: #777; line-height: 1.4; margin-bottom: 12px; }
        .progress-section { margin: 12px 0; }
        .progress-stats { display: flex; justify-content: space-between; font-size: 0.65rem; color: #888; margin-bottom: 5px; }
        .progress-bar-custom { height: 4px; background: #e8ece8; border-radius: 4px; overflow: hidden; }
        .progress-fill { height: 100%; background: var(--primary); border-radius: 4px; }
        .btn-subscribe {
            width: 100%;
            background: white;
            border: 1.5px solid var(--primary);
            color: var(--primary);
            padding: 8px 12px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.7rem;
            transition: all 0.2s;
            cursor: pointer;
        }
        .btn-subscribe:hover { background: var(--primary); color: white; transform: translateY(-2px); }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            width: 100%;
            justify-content: center;
        }
        .status-pending { background: #ff9800; color: white; }
        .status-confirmed { background: #4caf50; color: white; }
        .status-refused { background: #f44336; color: white; }
        .status-full { background: #9e9e9e; color: white; }
        .admin-buttons { display: flex; gap: 8px; margin-top: 10px; }
        .btn-admin { padding: 5px 10px; border-radius: 8px; font-size: 0.65rem; font-weight: 500; text-decoration: none; }
        .btn-edit { background: #f59e0b; color: white; }
        .btn-users { background: #0891b2; color: white; }
        .btn-delete { background: #dc2626; color: white; }
        
        /* Bouton partager Facebook */
        .btn-share {
            background: #1877f2;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            margin-top: 8px;
        }
        .btn-share:hover {
            background: #0d5bd9;
            transform: translateY(-2px);
            color: white;
        }
        
        /* Calendar */
        .calendar-container {
            background: white;
            border-radius: var(--radius-lg);
            padding: 20px;
            margin-top: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-sm);
        }
        .fc { font-family: 'Inter', sans-serif; }
        .fc-event {
            cursor: pointer;
            border-radius: 6px;
            font-size: 0.7rem;
            padding: 4px 6px;
            transition: all 0.2s;
        }
        .fc-event:hover { transform: scale(1.02); opacity: 0.9; }
        .fc-col-header-cell-cushion { font-weight: 600; color: var(--primary); text-transform: uppercase; font-size: 0.7rem; }
        .fc-toolbar-title { font-size: 1rem !important; font-weight: 700 !important; color: var(--primary) !important; }
        .fc-button { background: var(--primary) !important; border: none !important; border-radius: 8px !important; padding: 4px 10px !important; font-size: 0.7rem !important; }
        .fc-button:hover { background: var(--primary-dark) !important; }
        .fc-day-today { background: #e8f5e9 !important; }
        
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
        .modal-header h3 { margin: 0; font-size: 1.1rem; }
        .modal-body { padding: 20px; }
        .modal-footer {
            padding: 12px 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            border-top: 1px solid #e9ecef;
        }
        .event-details-modal {
            background: #e8f5e9;
            border-radius: 12px;
            padding: 12px;
            margin: 15px 0;
            font-size: 0.8rem;
        }
        .btn-primary-custom {
            background: var(--gradient);
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.7rem;
            color: white;
        }
        
        .toast-message {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 10001;
            animation: slideInRight 0.3s ease;
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: var(--radius-lg);
        }
        .empty-state i { font-size: 2.5rem; color: var(--primary-light); margin-bottom: 1rem; }
        .footer {
            background: white;
            text-align: center;
            padding: 1.25rem;
            margin-top: 2rem;
            color: #666;
            font-size: 0.7rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        
        @media (max-width: 768px) {
            .navbar { padding: 0.75rem 1rem; }
            .mobile-toggle { display: block; }
            .nav-links {
                display: none;
                width: 100%;
                flex-direction: column;
                gap: 0;
                margin-top: 1rem;
            }
            .nav-links.open { display: flex; }
            .nav-links li a { display: block; padding: 10px 0; }
            .nav-right { margin-top: 1rem; width: 100%; justify-content: space-between; }
            .hero-categorie h1 { font-size: 1.3rem; }
            .hero-stats { flex-direction: column; align-items: center; gap: 8px; }
            .calendar-container { padding: 10px; }
            .fc-toolbar { flex-direction: column; gap: 10px; }
        }
    </style>
</head>
<body class="role-<?php echo $userRole; ?>">

    <!-- Toast Notification -->
    <?php if ($message): ?>
    <div class="toast-message">
        <div class="alert alert-<?php echo $messageType; ?> shadow rounded-3 border-0 py-2 px-3">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
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
                    <small class="text-muted">Maximum 10 personnes</small>
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
    <nav class="navbar" id="navbar">
        <a class="nav-brand" href="index.php">
            <img src="logo.jpeg" alt="Logo Smart Municipality">
            <span class="nav-brand-text">Smart <span>Municipality</span></span>
        </a>
        <button class="mobile-toggle" type="button" aria-label="Ouvrir le menu" onclick="document.querySelector('.nav-links').classList.toggle('open')">
            <span></span><span></span><span></span>
        </button>
        <ul class="nav-links">
            <li><a href="#">Profil</a></li>
            <li><a href="index.php">Événements</a></li>
            <li><a href="#">Carte</a></li>
            <li><a href="#">Blog</a></li>
            <li><a href="#">Services</a></li>
            <li><a href="#">Rendez-vous</a></li>
        </ul>
        <div class="nav-right">
            <div class="nav-search">
                <span class="nav-search-icon">⌕</span>
                <input type="text" id="searchInput" placeholder="Rechercher...">
            </div>
            <div class="user-info">
                <?php if ($isLoggedIn): ?>
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['prenom'] ?? 'U', 0, 1)); ?></div>
                    <span class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($userName); ?></span>
                    <?php if ($isAdmin): ?>
                    <a href="views/dashboard/admin.php" class="btn-dashboard"><i class="fas fa-chart-line"></i> Dashboard</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                <?php else: ?>
                    <a href="views/auth/login.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Se connecter</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero-categorie">
        <div class="container">
            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour aux catégories</a>
            <h1><i class="fas <?php 
                if($categorie['nom'] == 'Culture') echo 'fa-music';
                elseif($categorie['nom'] == 'Sport') echo 'fa-futbol';
                elseif($categorie['nom'] == 'Environnement') echo 'fa-leaf';
                elseif($categorie['nom'] == 'Social') echo 'fa-handshake';
                elseif($categorie['nom'] == 'Technologie') echo 'fa-microchip';
                else echo 'fa-tag';
            ?> me-2"></i><?php echo htmlspecialchars($categorie['nom']); ?></h1>
            <p><?php echo htmlspecialchars($categorie['description']); ?></p>
            <div class="hero-stats">
                <div class="hero-stat"><i class="fas fa-calendar-alt"></i> <?php echo $nbEvenements; ?> événement(s)</div>
                <div class="hero-stat"><i class="fas fa-users"></i> <?php echo $totalPlaces; ?> places totales</div>
            </div>
        </div>
    </section>

    <div class="container">
        <!-- Filtres -->
        <div class="filter-bar">
            <form method="GET" class="row g-2">
                <input type="hidden" name="id" value="<?php echo $categorie_id; ?>">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-success"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?php echo htmlspecialchars($recherche); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary-custom w-100"><i class="fas fa-search me-1"></i> Rechercher</button>
                </div>
            </form>
        </div>

        <!-- Tris -->
        <div class="sort-buttons">
            <span class="sort-label"><i class="fas fa-sort me-1"></i> Trier par :</span>
            <a href="?id=<?php echo $categorie_id; ?>&sort_by=date&sort_order=<?php echo $sort_by == 'date' && $sort_order == 'asc' ? 'desc' : 'asc'; ?><?php echo $recherche ? '&search='.urlencode($recherche) : ''; ?>" class="sort-btn <?php echo $sort_by == 'date' ? 'active' : ''; ?>">Date <?php if($sort_by == 'date') echo $sort_order == 'asc' ? '↑' : '↓'; ?></a>
            <a href="?id=<?php echo $categorie_id; ?>&sort_by=titre&sort_order=<?php echo $sort_by == 'titre' && $sort_order == 'asc' ? 'desc' : 'asc'; ?><?php echo $recherche ? '&search='.urlencode($recherche) : ''; ?>" class="sort-btn <?php echo $sort_by == 'titre' ? 'active' : ''; ?>">Titre <?php if($sort_by == 'titre') echo $sort_order == 'asc' ? '↑' : '↓'; ?></a>
            <a href="?id=<?php echo $categorie_id; ?>&sort_by=lieu&sort_order=<?php echo $sort_by == 'lieu' && $sort_order == 'asc' ? 'desc' : 'asc'; ?><?php echo $recherche ? '&search='.urlencode($recherche) : ''; ?>" class="sort-btn <?php echo $sort_by == 'lieu' ? 'active' : ''; ?>">Lieu <?php if($sort_by == 'lieu') echo $sort_order == 'asc' ? '↑' : '↓'; ?></a>
        </div>

        <!-- Admin Add Button -->
        <?php if ($isAdmin): ?>
        <div class="text-end mb-3">
            <a href="views/evenement/ajouter.php?categorie=<?php echo $categorie_id; ?>" class="btn-add-event">
                <i class="fas fa-plus-circle"></i> Ajouter un événement
            </a>
        </div>
        <?php endif; ?>

        <!-- Liste des événements -->
        <?php if (empty($evenementsArray)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h5>Aucun événement trouvé</h5>
            <p class="text-muted">Aucun événement dans la catégorie "<?php echo htmlspecialchars($categorie['nom']); ?>"</p>
            <a href="categorie_evenements.php?id=<?php echo $categorie_id; ?>" class="btn btn-primary-custom btn-sm mt-2">Réinitialiser</a>
        </div>
        <?php else: ?>
            <?php foreach($evenementsArray as $event): 
                $placesTotal = $event['max_participants'];
                $placesValidees = $participationC->compterParticipationsValidees($event['id']);
                $placesRestantes = $placesTotal - $placesValidees;
                $pourcentage = $placesTotal > 0 ? round(($placesValidees / $placesTotal) * 100) : 0;
                $estComplet = $placesRestantes <= 0;
                $estInscrit = false;
                $statutValidation = null;
                if ($isLoggedIn) {
                    $estInscrit = $participationC->estInscrit($userId, $event['id']);
                    $statutValidation = $participationC->getStatutValidation($userId, $event['id']);
                }
            ?>
            <div class="event-card event-card-wrapper"
                 data-id="<?php echo $event['id']; ?>"
                 data-title="<?php echo htmlspecialchars($event['titre']); ?>"
                 data-lieu="<?php echo htmlspecialchars($event['lieu']); ?>"
                 data-date="<?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?>"
                 data-heure="<?php echo $event['heure']; ?>"
                 data-places="<?php echo $placesRestantes; ?>">
                <div class="event-card-inner">
                    <div class="event-header">
                        <h5 class="event-title"><?php echo htmlspecialchars($event['titre']); ?></h5>
                        <span class="event-category"><?php echo htmlspecialchars($event['categorie_nom'] ?? $categorie['nom']); ?></span>
                    </div>
                    <div class="event-details">
                        <span class="event-detail"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['lieu']); ?></span>
                        <span class="event-detail"><i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?></span>
                        <span class="event-detail"><i class="fas fa-clock"></i> <?php echo $event['heure']; ?></span>
                    </div>
                    <div class="event-description"><?php echo substr(htmlspecialchars($event['description']), 0, 100); ?>...</div>
                    <div class="progress-section">
                        <div class="progress-stats">
                            <span><i class="fas fa-users"></i> <?php echo $placesValidees; ?>/<?php echo $placesTotal; ?> inscrits</span>
                            <span><?php echo $placesRestantes; ?> places restantes</span>
                        </div>
                        <div class="progress-bar-custom"><div class="progress-fill" style="width: <?php echo $pourcentage; ?>%;"></div></div>
                    </div>
                    <div class="action-buttons">
                        <?php if ($estInscrit): ?>
                            <?php if ($statutValidation == 'en_attente'): ?>
                                <div class="status-badge status-pending"><i class="fas fa-clock"></i> En attente</div>
                            <?php elseif ($statutValidation == 'valide'): ?>
                                <div class="status-badge status-confirmed"><i class="fas fa-check-circle"></i> Inscrit</div>
                            <?php else: ?>
                                <div class="status-badge status-refused"><i class="fas fa-times-circle"></i> Refusé</div>
                            <?php endif; ?>
                        <?php elseif ($estComplet): ?>
                            <div class="status-badge status-full"><i class="fas fa-ban"></i> Complet</div>
                        <?php else: ?>
                            <!-- Bouton inscription pour TOUS les utilisateurs connectés (y compris admin) -->
                            <button class="btn-subscribe btn-inscrire"><i class="fas fa-ticket-alt"></i> S'inscrire</button>
                        <?php endif; ?>
                        
                        <!-- Bouton Partager Facebook avec message automatique -->
                        <button onclick="partagerFacebook(<?php echo $event['id']; ?>, '<?php echo htmlspecialchars(addslashes($event['titre'])); ?>', '<?php echo htmlspecialchars(addslashes($event['lieu'])); ?>', '<?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?>', '<?php echo htmlspecialchars(addslashes($event['description'])); ?>')" class="btn-share">
                            <i class="fab fa-facebook-f"></i> Partager sur Facebook
                        </button>
                        
                        <?php if ($isAdmin): ?>
                        <div class="admin-buttons">
                            <a href="views/evenement/modifier.php?id=<?php echo $event['id']; ?>" class="btn-admin btn-edit"><i class="fas fa-edit"></i></a>
                            <a href="views/evenement/participants.php?id=<?php echo $event['id']; ?>" class="btn-admin btn-users"><i class="fas fa-users"></i></a>
                            <a href="views/evenement/supprimer.php?id=<?php echo $event['id']; ?>" class="btn-admin btn-delete" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- ========== CALENDRIER ========== -->
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
        
        // Boutons d'inscription
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
        
        // ========== CALENDRIER INTERACTIF ==========
        document.addEventListener('DOMContentLoaded', function() {
            var events = <?php echo json_encode($calendarEvents); ?>;
            
            var calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    locale: 'fr',
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                    buttonText: {
                        today: "Aujourd'hui",
                        month: 'Mois',
                        week: 'Semaine',
                        list: 'Liste'
                    },
                    events: events,
                    eventClick: function(info) {
                        const event = info.event;
                        const props = event.extendedProps;
                        
                        <?php if ($isAdmin): ?>
                            window.location.href = `views/evenement/modifier.php?id=${event.id}`;
                        <?php elseif ($isLoggedIn && !$isAdmin): ?>
                            if (props.est_inscrit) {
                                alert('✅ Vous êtes déjà inscrit à cet événement !');
                                return;
                            }
                            if (props.est_complet) {
                                alert('❌ Désolé, cet événement est complet !');
                                return;
                            }
                            openModal(
                                event.id,
                                event.title,
                                props.lieu,
                                event.startStr,
                                props.heure,
                                props.places_restantes
                            );
                        <?php else: ?>
                            alert('🔐 Veuillez vous connecter pour vous inscrire à cet événement.');
                            window.location.href = 'views/auth/login.php';
                        <?php endif; ?>
                    },
                    dateClick: function(info) {
                        <?php if ($isAdmin): ?>
                            window.location.href = `views/evenement/ajouter.php?categorie=<?php echo $categorie_id; ?>&date=${info.dateStr}`;
                        <?php else: ?>
                            alert('📅 Connectez-vous en tant qu\'administrateur pour ajouter un événement.');
                        <?php endif; ?>
                    },
                    eventDidMount: function(info) {
                        const props = info.event.extendedProps;
                        let tooltip = `${info.event.title}\n📍 ${props.lieu}\n🕐 ${props.heure}\n👥 ${props.places_restantes}/${props.places_total} places`;
                        if (props.est_complet) tooltip += '\n❌ COMPLET';
                        if (props.est_inscrit) tooltip += '\n✅ Vous êtes inscrit';
                        info.el.setAttribute('title', tooltip);
                    },
                    height: 480,
                    contentHeight: 'auto',
                    firstDay: 1,
                    dayMaxEvents: true
                });
                calendar.render();
            }
        });
        
        // Search input handler
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const searchValue = this.value;
                    if (searchValue) {
                        window.location.href = `?id=<?php echo $categorie_id; ?>&search=${encodeURIComponent(searchValue)}`;
                    }
                }
            });
        }
        
        // Fonction pour partager sur Facebook avec message automatique
// Fonction pour partager sur Facebook - Version simple et fiable
function partagerFacebook(eventId, titre, lieu, date) {
    // URL de l'événement
    var url = window.location.origin + '/smart_municipality/categorie_evenements.php?id=' + eventId;
    
    // URL de partage Facebook uniquement avec le lien
    var shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
    
    // Ouvrir la fenêtre de partage
    window.open(shareUrl, 'facebook-share', 'width=600,height=400');
    return false;
}
        
        setTimeout(() => {
            const toast = document.querySelector('.toast-message');
            if (toast) {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    </script>
</body>
</html>