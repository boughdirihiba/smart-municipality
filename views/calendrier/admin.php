<?php
session_start();
require_once __DIR__ . '/../../controllers/EvenementC.php';
require_once __DIR__ . '/../../controllers/ParticipationC.php';
require_once __DIR__ . '/../../controllers/CategorieEvenementC.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$evenementC = new EvenementC();
$participationC = new ParticipationC();
$categorieC = new CategorieEvenementC();

$totalEvenements = $evenementC->compterEvenements();
$totalParticipations = $participationC->compterTotalParticipations();
$totalCategories = count($categorieC->afficherCategories());
$evenementsParCategorie = $evenementC->compterEvenementsParCategorie();
$evenementsRecents = array_slice($evenementC->afficherEvenements(), 0, 5);

$evenementsAVenir = 0;
$evenementsPasses = 0;
$aujourdhui = date('Y-m-d');
$tousEvenements = $evenementC->afficherEvenements();
foreach ($tousEvenements as $e) {
    if ($e['date_evenement'] >= $aujourdhui) $evenementsAVenir++;
    else $evenementsPasses++;
}

$categoriesNom = array_column($evenementsParCategorie, 'nom');
$categoriesCount = array_column($evenementsParCategorie, 'total');
$couleurs = ['#2e7d32', '#388e3c', '#43a047', '#4caf50', '#66bb6a', '#81c784', '#a5d6a7', '#c8e6c9', '#1b5e20', '#0d3b1a'];
$userName = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];
$userRole = $_SESSION['role'];
$displayName = $userName;
$avatarName = 'sidebar-photo.svg';
$currentRoute = 'calendrier';
$isAdmin = true;
$baseUrl = '../../';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Smart Municipality</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/admin-sidebar.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js'></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #e8f5e9;
            display: flex;
        }
        
        /* ========== SIDEBAR MODERNE ========== */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0d3b1a 0%, #1a5e2a 100%);
            color: white;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: sticky;
            top: 0;
            transition: all 0.3s;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar-logo {
            padding: 25px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-logo img {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            margin-bottom: 10px;
        }
        .sidebar-logo h2 {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
        }
        .sidebar-logo p {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 5px;
        }
        
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px;
            background: rgba(255,255,255,0.05);
            margin: 15px;
            border-radius: 12px;
        }
        .sidebar-user img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.3);
        }
        .sidebar-user strong {
            display: block;
            font-size: 0.85rem;
        }
        .sidebar-user span {
            font-size: 0.7rem;
            opacity: 0.7;
        }
        
        .notifications-box {
            margin: 0 15px 15px 15px;
            background: rgba(255,255,255,0.08);
            border-radius: 12px;
            position: relative;
        }
        .notifications-trigger {
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            list-style: none;
        }
        .notifications-trigger::-webkit-details-marker { display: none; }
        .notif-icon { font-size: 1.2rem; }
        .notif-count {
            background: #ff9800;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            font-weight: bold;
        }
        .notifications-panel {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            color: #333;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            z-index: 100;
            max-height: 300px;
            overflow-y: auto;
        }
        .notifications-panel h4 {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 0.85rem;
        }
        .notif-list { list-style: none; }
        .notif-item {
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.75rem;
        }
        
        .sidebar-nav {
            flex: 1;
            padding: 10px 0;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: all 0.3s;
            margin: 5px 10px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(5px);
        }
        .sidebar-link .label { flex: 1; }
        .sidebar-link .icon { font-size: 1.1rem; }
        
        .sidebar-footer-links {
            display: flex;
            justify-content: space-around;
            padding: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-footer-links .sidebar-link {
            padding: 8px;
            margin: 0;
        }
        
        .sidebar-toggle {
            position: absolute;
            top: 20px;
            right: -12px;
            background: var(--primary);
            border: none;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }
        
        /* MAIN CONTENT */
        .main-content {
            flex: 1;
            padding: 25px;
            overflow-x: auto;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border-left: 4px solid #1a5e2a;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .stat-info h3 { font-size: 0.7rem; text-transform: uppercase; color: #666; letter-spacing: 0.5px; margin-bottom: 5px; }
        .stat-info h2 { font-size: 1.6rem; font-weight: 700; color: #1a5e2a; margin: 0; }
        .stat-icon { width: 45px; height: 45px; background: #e8f5e9; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #1a5e2a; }
        
        .card-pro {
            background: white;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 25px;
        }
        .card-header-pro {
            padding: 15px 20px;
            border-bottom: 1px solid #e8f5e9;
            font-weight: 600;
            color: #1a5e2a;
        }
        .btn-primary {
            background: linear-gradient(135deg, #1a5e2a, #4caf50);
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 600;
            color: white;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        .action-btn {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
            border: 1px solid #e9ecef;
        }
        .action-btn:hover {
            background: linear-gradient(135deg, #1a5e2a, #4caf50);
            transform: translateY(-3px);
        }
        .action-btn i { font-size: 1.4rem; color: #1a5e2a; margin-bottom: 8px; display: block; }
        .action-btn:hover i, .action-btn:hover span { color: white; }
        .action-btn span { font-weight: 500; color: #333; font-size: 0.75rem; }
        
        .calendar-container { background: white; border-radius: 16px; padding: 20px; }
        .fc-event { background: linear-gradient(135deg, #1a5e2a, #4caf50); border: none; cursor: pointer; border-radius: 6px; }
        .fc-col-header-cell-cushion { font-weight: 600; color: #1a5e2a; text-transform: uppercase; font-size: 0.7rem; }
        .fc-toolbar-title { font-size: 1rem !important; font-weight: 700 !important; color: #1a5e2a !important; }
        .fc-button { background: #1a5e2a !important; border: none !important; border-radius: 8px !important; padding: 4px 10px !important; font-size: 0.7rem !important; }
        
        @media (max-width: 992px) {
            .sidebar { width: 80px; }
            .sidebar-logo h2, .sidebar-logo p, .sidebar-user div, .sidebar-link .label, .notifications-box { display: none; }
            .sidebar-user { justify-content: center; }
            .sidebar-link { justify-content: center; padding: 12px; }
            .sidebar-footer-links .sidebar-link { padding: 5px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .quick-actions { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1"><i class="fas fa-chart-line me-2" style="color: #1a5e2a;"></i>Tableau de bord</h1>
                <p class="text-muted">Bienvenue, <?php echo htmlspecialchars($displayName); ?></p>
            </div>
            <a href="../evenement/ajouter.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Nouvel événement</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info"><h3>Événements</h3><h2><?php echo $totalEvenements; ?></h2><small><?php echo $evenementsAVenir; ?> à venir</small></div>
                <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info"><h3>Participations</h3><h2><?php echo $totalParticipations; ?></h2><small>Inscriptions totales</small></div>
                <div class="stat-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info"><h3>Catégories</h3><h2><?php echo $totalCategories; ?></h2><small>Types d'événements</small></div>
                <div class="stat-icon"><i class="fas fa-tags"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info"><h3>Taux remplissage</h3><h2><?php echo round(($totalParticipations / max(1, $totalEvenements * 50)) * 100, 1); ?>%</h2></div>
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card-pro">
                    <div class="card-header-pro"><i class="fas fa-chart-pie me-2"></i> Répartition par catégorie</div>
                    <div class="p-3"><canvas id="pieChart" height="250"></canvas></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card-pro">
                    <div class="card-header-pro"><i class="fas fa-calendar-week me-2"></i> Calendrier</div>
                    <div class="calendar-container"><div id="calendar"></div></div>
                </div>
            </div>
        </div>

        <div class="card-pro">
            <div class="card-header-pro"><i class="fas fa-bolt me-2"></i> Actions rapides</div>
            <div class="p-4">
                <div class="quick-actions">
                    <a href="../evenement/ajouter.php" class="action-btn"><i class="fas fa-plus-circle"></i><span>Ajouter</span></a>
                    <a href="../evenement/liste.php" class="action-btn"><i class="fas fa-list"></i><span>Gérer</span></a>
                    <a href="categorie/liste.php" class="action-btn"><i class="fas fa-tags"></i><span>Catégories</span></a>
                    <a href="../../index.php" class="action-btn"><i class="fas fa-home"></i><span>Accueil</span></a>
                </div>
            </div>
        </div>

        <div class="card-pro">
            <div class="card-header-pro"><i class="fas fa-history me-2"></i> Derniers événements</div>
            <div class="list-group list-group-flush">
                <?php foreach($evenementsRecents as $e): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div><strong><?php echo htmlspecialchars($e['titre']); ?></strong><br><small><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($e['lieu']); ?> | <i class="fas fa-calendar me-1"></i><?php echo date('d/m/Y', strtotime($e['date_evenement'])); ?></small></div>
                    <div><a href="../evenement/modifier.php?id=<?php echo $e['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a><a href="../evenement/supprimer.php?id=<?php echo $e['id']; ?>" class="btn btn-sm btn-danger ms-1" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: { labels: <?php echo json_encode($categoriesNom); ?>, datasets: [{ data: <?php echo json_encode($categoriesCount); ?>, backgroundColor: <?php echo json_encode(array_slice($couleurs, 0, count($categoriesNom))); ?>, borderWidth: 0 }] },
            options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } } }
        });

        document.addEventListener('DOMContentLoaded', function() {
            var events = <?php $a = []; foreach($evenementC->afficherEvenements() as $e) { $a[] = ['title' => $e['titre'], 'start' => $e['date_evenement'], 'color' => '#1a5e2a', 'url' => '../evenement/modifier.php?id=' . $e['id']]; } echo json_encode($a); ?>;
            new FullCalendar.Calendar(document.getElementById('calendar'), { locale: 'fr', initialView: 'dayGridMonth', headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth' }, buttonText: { today: "Aujourd'hui", month: 'Mois' }, events: events, eventClick: function(info) { if (info.event.url) window.location.href = info.event.url; }, height: 350 }).render();
        });
    </script>
    <script>
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const toggleIcon = document.getElementById('sidebarToggleIcon');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                toggleIcon.textContent = sidebar.classList.contains('collapsed') ? '❯' : '❮';
            });
        }
    </script>
</body>
</html>
