<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementC.php';
require_once __DIR__ . '/../../controller/ParticipationC.php';
require_once __DIR__ . '/../../controller/CategorieEvenementC.php';

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

// Calcul des événements à venir et passés
$evenementsAVenir = 0;
$evenementsPasses = 0;
$aujourdhui = date('Y-m-d');
$tousEvenements = $evenementC->afficherEvenements();
foreach ($tousEvenements as $e) {
    if ($e['date_evenement'] >= $aujourdhui) {
        $evenementsAVenir++;
    } else {
        $evenementsPasses++;
    }
}

$categoriesNom = array_column($evenementsParCategorie, 'nom');
$categoriesCount = array_column($evenementsParCategorie, 'total');
$couleurs = ['#2e7d32', '#388e3c', '#43a047', '#4caf50', '#66bb6a', '#81c784', '#a5d6a7', '#c8e6c9', '#1b5e20', '#0d3b1a'];

// Récupérer les catégories
$categories = $categorieC->afficherCategories();
$displayName = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];
$userRole = $_SESSION['role'];
$avatarName = 'sidebar-photo.svg';
$currentRoute = 'dashboard';
$baseUrl = '../../';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js'></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="../../public/css/admin-sidebar.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #e8f5e9; }

        /* SIDEBAR */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #0d3b1a 0%, #1a5e2a 100%);
            position: fixed;
            width: 280px;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar-header { padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header img { border-radius: 15px; margin-bottom: 10px; border: 2px solid rgba(255,255,255,0.2); }
        .sidebar-header h3 { color: white; font-size: 1.2rem; margin: 0; }
        .sidebar-header p { color: rgba(255,255,255,0.7); font-size: 0.7rem; margin-top: 5px; }
        .sidebar-nav { padding: 18px 0; }
        .sidebar-section-title {
            color: rgba(255,255,255,0.55);
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin: 14px 20px 8px;
            padding-left: 8px;
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 25px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: all 0.3s;
            margin: 5px 10px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .sidebar-nav a i { width: 28px; margin-right: 12px; font-size: 1.1rem; text-align: center; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(255,255,255,0.15); color: white; transform: translateX(5px); }
        .sidebar-link-main { display: inline-flex; align-items: center; }
        .sidebar-badge {
            font-size: 0.62rem;
            font-weight: 700;
            border-radius: 999px;
            padding: 3px 8px;
            background: rgba(255,255,255,0.14);
            color: rgba(255,255,255,0.9);
        }
        .sidebar-badge.badge-live {
            background: rgba(76,175,80,0.22);
            color: #d8ffd8;
        }
        .sidebar-link-coming {
            opacity: 0.9;
        }
        .sidebar-nav hr { border-color: rgba(255,255,255,0.1); margin: 15px; }

        /* MAIN CONTENT */
        .main-content { margin-left: 280px; padding: 25px; }

        /* BOUTONS */
        .btn {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 0.8rem;
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }
        .btn-sm { font-size: 0.7rem; padding: 5px 12px; gap: 5px; }
        .btn-primary { background: linear-gradient(135deg, #1a5e2a, #4caf50); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-info { background: #0891b2; color: white; }
        .btn-success { background: #2e7d32; color: white; }
        .btn-outline-success { background: transparent; border: 2px solid #2e7d32; color: #2e7d32; }
        .btn-outline-success:hover { background: #2e7d32; color: white; }

        /* STATS CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; border-radius: 16px; padding: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: all 0.3s; border-left: 4px solid #1a5e2a; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .stat-info h3 { font-size: 0.7rem; text-transform: uppercase; color: #666; letter-spacing: 0.5px; margin-bottom: 5px; }
        .stat-info h2 { font-size: 1.8rem; font-weight: 700; color: #1a5e2a; margin: 0; }
        .stat-icon { width: 48px; height: 48px; background: #e8f5e9; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: #1a5e2a; }

        /* CARDS */
        .card-pro { background: white; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 25px; border: 1px solid rgba(0,0,0,0.05); }
        .card-header-pro { padding: 18px 25px; border-bottom: 2px solid #e8f5e9; font-weight: 600; color: #1a5e2a; }

        /* PIE CHART - PLUS PETIT */
        .pie-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 280px;
        }
        canvas#pieChart {
            max-width: 220px;
            max-height: 220px;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.08));
        }
        .chart-stats {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
            padding-top: 12px;
            border-top: 1px solid #e8f5e9;
            flex-wrap: wrap;
        }
        .chart-stat-item { text-align: center; padding: 5px 12px; background: #f8faf8; border-radius: 25px; min-width: 80px; }
        .chart-stat-value { font-size: 1rem; font-weight: 700; color: #1a5e2a; }
        .chart-stat-label { font-size: 0.65rem; color: #666; text-transform: uppercase; }

        /* CALENDAR */
        .calendar-container { background: white; border-radius: 16px; padding: 20px; min-height: 500px; }
        .fc { font-family: 'Inter', sans-serif; }
        .fc-event { background: linear-gradient(135deg, #1a5e2a, #4caf50); border: none; cursor: pointer; border-radius: 6px; font-size: 0.7rem; }
        .fc-col-header-cell-cushion { font-weight: 600; color: #1a5e2a; text-transform: uppercase; font-size: 0.7rem; }
        .fc-toolbar-title { font-size: 1rem !important; font-weight: 700 !important; color: #1a5e2a !important; }
        .fc-button { background: #1a5e2a !important; border: none !important; border-radius: 8px !important; padding: 4px 10px !important; font-size: 0.7rem !important; }

        /* ACTIONS RAPIDES */
        .quick-actions { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; }
        .action-btn { background: #f8f9fa; border-radius: 14px; padding: 18px 12px; text-align: center; text-decoration: none; transition: all 0.3s; border: 1px solid #e9ecef; }
        .action-btn:hover { background: linear-gradient(135deg, #1a5e2a, #4caf50); transform: translateY(-5px); box-shadow: 0 10px 25px rgba(26,94,42,0.2); }
        .action-btn i { font-size: 1.8rem; color: #1a5e2a; margin-bottom: 10px; display: block; transition: all 0.3s; }
        .action-btn:hover i, .action-btn:hover span { color: white; }
        .action-btn span { font-weight: 500; color: #333; font-size: 0.75rem; }

        /* BOUTONS CATÉGORIES */
        .category-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
            padding: 15px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .category-actions .btn { flex: 1; min-width: 130px; justify-content: center; }

        /* MODAL AJOUT RAPIDE */
        .modal-add-event .modal-content { border-radius: 20px; overflow: hidden; }
        .modal-add-event .modal-header { background: linear-gradient(135deg, #1a5e2a, #4caf50); color: white; border: none; padding: 20px; }
        .modal-add-event .modal-body { padding: 25px; }

        @media (max-width: 992px) {
            .sidebar { width: 80px; }
            .sidebar-header h3, .sidebar-header p, .sidebar-nav a span, .sidebar-section-title, .sidebar-badge { display: none; }
            .sidebar-nav a { justify-content: center; padding: 12px; }
            .sidebar-nav a i { margin-right: 0; }
            .main-content { margin-left: 80px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .quick-actions { grid-template-columns: repeat(2, 1fr); }
            .category-actions { flex-direction: column; }
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1"><i class="fas fa-chart-line me-2" style="color: #1a5e2a;"></i>Tableau de bord</h1>
                <p class="text-muted">Bienvenue, <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></p>
            </div>
            <a href="../evenement/ajouter.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Nouvel événement</a>
        </div>

        <!-- Stats Cards -->
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
                <div class="stat-info"><h3>Taux remplissage</h3><h2><?php echo $totalEvenements > 0 ? round(($totalParticipations / ($totalEvenements * 50)) * 100, 1) : 0; ?>%</h2></div>
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>

        <!-- Boutons Catégories -->
        <div class="category-actions">
            <a href="categorie/liste.php" class="btn btn-success"><i class="fas fa-list me-2"></i> Gérer les catégories</a>
            <a href="categorie/ajouter.php" class="btn btn-outline-success"><i class="fas fa-plus me-2"></i> Ajouter une catégorie</a>
            <a href="categorie/liste.php" class="btn btn-outline-success"><i class="fas fa-edit me-2"></i> Modifier</a>
            <a href="categorie/liste.php" class="btn btn-outline-danger"><i class="fas fa-trash me-2"></i> Supprimer</a>
        </div>

        <!-- Pie Chart - PLUS PETIT -->
        <div class="card-pro">
            <div class="card-header-pro"><i class="fas fa-chart-pie me-2"></i> Répartition des événements par catégorie</div>
            <div class="p-3">
                <div class="pie-wrapper"><canvas id="pieChart" width="220" height="220"></canvas></div>
                <div class="chart-stats">
                    <?php $total = array_sum($categoriesCount); foreach($evenementsParCategorie as $cat): $pct = $total > 0 ? round(($cat['total'] / $total) * 100, 1) : 0; ?>
                    <div class="chart-stat-item"><div class="chart-stat-value"><?php echo $cat['total']; ?></div><div class="chart-stat-label"><?php echo htmlspecialchars($cat['nom']); ?> (<?php echo $pct; ?>%)</div></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Calendrier -->
        <div class="card-pro">
            <div class="card-header-pro"><i class="fas fa-calendar-week me-2"></i> Calendrier des événements</div>
            <div class="calendar-container"><div id="calendar"></div></div>
        </div>

        <!-- Actions rapides -->
        <div class="card-pro">
            <div class="card-header-pro"><i class="fas fa-bolt me-2"></i> Actions rapides</div>
            <div class="p-4">
                <div class="quick-actions">
                    <a href="../evenement/ajouter.php" class="action-btn"><i class="fas fa-plus-circle"></i><span>Ajouter événement</span></a>
                    <a href="../evenement/liste.php" class="action-btn"><i class="fas fa-list"></i><span>Gérer événements</span></a>
                    <a href="categorie/liste.php" class="action-btn"><i class="fas fa-tags"></i><span>Gérer catégories</span></a>
                    <a href="../participation/mes_participations.php" class="action-btn"><i class="fas fa-users"></i><span>Participations</span></a>
                </div>
            </div>
        </div>

        <!-- Derniers événements -->
        <div class="card-pro">
            <div class="card-header-pro"><i class="fas fa-history me-2"></i> Derniers événements <a href="../evenement/liste.php" class="float-end text-decoration-none" style="color: #1a5e2a;">Voir tout <i class="fas fa-arrow-right ms-1"></i></a></div>
            <div class="list-group list-group-flush">
                <?php foreach($evenementsRecents as $e): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div><strong><?php echo htmlspecialchars($e['titre']); ?></strong><br><small><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($e['lieu']); ?> | <i class="fas fa-calendar me-1"></i><?php echo date('d/m/Y', strtotime($e['date_evenement'])); ?></small></div>
                    <div><a href="../evenement/modifier.php?id=<?php echo $e['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a><a href="../evenement/supprimer.php?id=<?php echo $e['id']; ?>" class="btn btn-danger btn-sm ms-1" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal Ajout rapide -->
    <div class="modal fade modal-add-event" id="quickAddModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Ajouter un événement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="../evenement/ajouter.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Titre *</label><input type="text" name="titre" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Description *</label><textarea name="description" class="form-control" rows="3" required></textarea></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Date *</label><input type="date" name="date_evenement" id="quickAddDate" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Heure *</label><input type="time" name="heure" class="form-control" required></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Lieu *</label><input type="text" name="lieu" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Participants max</label><input type="number" name="max_participants" class="form-control" value="50" min="1"></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Catégorie *</label><select name="categorie_id" class="form-select" required><option value="">-- Sélectionner --</option><?php foreach($categories as $cat): ?><option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nom']); ?></option><?php endforeach; ?></select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-primary">Ajouter</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pie Chart - PLUS PETIT
        new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: { labels: <?php echo json_encode($categoriesNom); ?>, datasets: [{ data: <?php echo json_encode($categoriesCount); ?>, backgroundColor: <?php echo json_encode(array_slice($couleurs, 0, count($categoriesNom))); ?>, borderWidth: 0, hoverOffset: 10 }] },
            options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 10 } } }, tooltip: { callbacks: { label: function(ctx) { return `${ctx.label}: ${ctx.raw} événement(s)`; } } } } }
        });

        // Calendrier
        document.addEventListener('DOMContentLoaded', function() {
            var events = <?php $a = []; foreach($evenementC->afficherEvenements() as $e) { $a[] = ['id' => $e['id'], 'title' => $e['titre'], 'start' => $e['date_evenement'], 'color' => '#1a5e2a', 'url' => '../evenement/modifier.php?id=' . $e['id']]; } echo json_encode($a); ?>;
            var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), { locale: 'fr', initialView: 'dayGridMonth', headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek' }, buttonText: { today: "Aujourd'hui", month: 'Mois', week: 'Semaine' }, events: events, eventClick: function(info) { if (info.event.url) window.location.href = info.event.url; }, height: 450, firstDay: 1 });
            calendar.render();
        });
    </script>
</body>
</html>