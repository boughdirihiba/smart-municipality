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

// Préparer les données pour le Pie Chart
$categoriesNom = array_column($evenementsParCategorie, 'nom');
$categoriesCount = array_column($evenementsParCategorie, 'total');
$couleurs = ['#4caf50', '#2196f3', '#ff9800', '#f44336', '#9c27b0', '#00bcd4', '#ffeb3b', '#795548', '#e91e63', '#3f51b5'];
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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #e8f5e9;
        }
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
        .sidebar-nav { padding: 20px 0; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
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
        /* STATS CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; border-radius: 16px; padding: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: all 0.3s; border-left: 4px solid #1a5e2a; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .stat-info h3 { font-size: 0.7rem; text-transform: uppercase; color: #666; letter-spacing: 0.5px; margin-bottom: 5px; }
        .stat-info h2 { font-size: 1.8rem; font-weight: 700; color: #1a5e2a; margin: 0; }
        .stat-icon { width: 48px; height: 48px; background: #e8f5e9; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: #1a5e2a; }
        /* CARDS */
        .card-pro { background: white; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 20px; border: 1px solid rgba(0,0,0,0.05); }
        .card-header-pro { padding: 15px 20px; border-bottom: 1px solid #e8f5e9; font-weight: 600; color: #1a5e2a; }
        .quick-actions { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; }
        .action-btn { background: #f8f9fa; border-radius: 12px; padding: 15px; text-align: center; text-decoration: none; transition: all 0.3s; border: 1px solid #e9ecef; }
        .action-btn:hover { background: linear-gradient(135deg, #1a5e2a, #4caf50); transform: translateY(-3px); }
        .action-btn i { font-size: 1.5rem; color: #1a5e2a; margin-bottom: 8px; display: block; }
        .action-btn:hover i, .action-btn:hover span { color: white; }
        .action-btn span { font-weight: 500; color: #333; font-size: 0.8rem; }
        /* CALENDAR - STYLES POUR JOURS DE SEMAINE */
        .calendar-container {
            background: white;
            border-radius: 16px;
            padding: 20px;
            min-height: 550px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .fc {
            font-family: 'Inter', sans-serif;
        }
        /* Style des en-têtes des jours (Lundi, Mardi, ...) */
        .fc-col-header-cell-cushion {
            font-weight: 700;
            color: #1a5e2a;
            text-transform: uppercase;
            font-size: 0.75rem;
            padding: 10px 0;
            text-decoration: none;
        }
        .fc-col-header-cell {
            background: #e8f5e9;
        }
        .fc-event {
            background: linear-gradient(135deg, #1a5e2a, #4caf50);
            border: none;
            cursor: pointer;
            border-radius: 8px;
            padding: 4px 8px;
            font-weight: 500;
            font-size: 0.75rem;
            transition: all 0.2s;
        }
        .fc-event:hover {
            transform: scale(1.02);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .fc-day-today {
            background: #e8f5e9 !important;
        }
        .fc-day-today .fc-daygrid-day-number {
            background: #1a5e2a;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .fc-daygrid-day-number {
            font-weight: 500;
            color: #333;
            text-decoration: none;
        }
        .fc-daygrid-day-number:hover {
            background: #e8f5e9;
            border-radius: 50%;
        }
        .fc-daygrid-day.fc-day-today {
            background: #e8f5e9;
        }
        .fc-header-toolbar {
            margin-bottom: 20px !important;
        }
        .fc-toolbar-title {
            font-size: 1.3rem !important;
            font-weight: 700 !important;
            color: #1a5e2a !important;
        }
        .fc-button {
            background: #1a5e2a !important;
            border: none !important;
            border-radius: 10px !important;
            padding: 6px 12px !important;
            font-weight: 500 !important;
            text-transform: capitalize !important;
            transition: all 0.2s !important;
        }
        .fc-button:hover {
            background: #0d3b1a !important;
            transform: translateY(-2px) !important;
        }
        .fc-button-primary:not(:disabled).fc-button-active,
        .fc-button-primary:not(:disabled):active {
            background: #0d3b1a !important;
        }
        /* Style des titres des jours dans la vue semaine */
        .fc-timegrid-col-header-cushion {
            font-weight: 600;
            color: #1a5e2a;
        }
        /* PIE CHART */
        .pie-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 320px;
        }
        @media (max-width: 992px) {
            .sidebar { width: 80px; }
            .sidebar-header h3, .sidebar-header p, .sidebar-nav a span { display: none; }
            .sidebar-nav a { justify-content: center; padding: 12px; }
            .sidebar-nav a i { margin-right: 0; }
            .main-content { margin-left: 80px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .quick-actions { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .calendar-container { padding: 10px; }
            .fc-toolbar { flex-direction: column; gap: 10px; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../../logo.jpeg" alt="Logo" height="45">
            <h3>Smart Municipality</h3>
            <p>Administrateur</p>
        </div>
        <div class="sidebar-nav">
            <a href="admin.php" class="active"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="../evenement/liste.php"><i class="fas fa-calendar-alt"></i><span>Événements</span></a>
            <a href="#"><i class="fas fa-users"></i><span>Utilisateurs</span></a>
            <a href="#"><i class="fas fa-calendar-check"></i><span>Rendez-vous</span></a>
            <a href="#"><i class="fas fa-blog"></i><span>Blog</span></a>
            <a href="#"><i class="fas fa-exclamation-triangle"></i><span>Signalements</span></a>
            <a href="#"><i class="fas fa-headset"></i><span>Services</span></a>
            <hr>
            <a href="../../index.php"><i class="fas fa-home"></i><span>Accueil</span></a>
            <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span>Déconnexion</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1"><i class="fas fa-chart-line me-2" style="color: #1a5e2a;"></i>Tableau de bord</h1>
                <p class="text-muted">Bienvenue, <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></p>
            </div>
            <a href="../evenement/ajouter.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Nouvel événement</a>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Événements</h3>
                    <h2><?php echo $totalEvenements; ?></h2>
                    <small><?php echo $evenementsAVenir; ?> à venir</small>
                </div>
                <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Participations</h3>
                    <h2><?php echo $totalParticipations; ?></h2>
                    <small>Inscriptions totales</small>
                </div>
                <div class="stat-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Catégories</h3>
                    <h2><?php echo $totalCategories; ?></h2>
                    <small>Types d'événements</small>
                </div>
                <div class="stat-icon"><i class="fas fa-tags"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Taux remplissage</h3>
                    <h2><?php echo $totalEvenements > 0 ? round(($totalParticipations / ($totalEvenements * 50)) * 100, 1) : 0; ?>%</h2>
                    <small>Moyenne par événement</small>
                </div>
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>

        <!-- 2 colonnes: Pie Chart + Calendrier -->
        <div class="row">
            <div class="col-lg-5">
                <div class="card-pro">
                    <div class="card-header-pro"><i class="fas fa-chart-pie me-2"></i> Répartition des événements par catégorie</div>
                    <div class="pie-container p-3">
                        <canvas id="pieChart" width="350" height="350" style="max-width: 100%; height: auto;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card-pro">
                    <div class="card-header-pro"><i class="fas fa-calendar-week me-2"></i> Calendrier des événements</div>
                    <div class="calendar-container">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card-pro">
            <div class="card-header-pro"><i class="fas fa-bolt me-2"></i> Actions rapides</div>
            <div class="p-4">
                <div class="quick-actions">
                    <a href="../evenement/ajouter.php" class="action-btn"><i class="fas fa-plus-circle"></i><span>Ajouter événement</span></a>
                    <a href="../evenement/liste.php" class="action-btn"><i class="fas fa-list"></i><span>Gérer événements</span></a>
                    <a href="../participation/mes_participations.php" class="action-btn"><i class="fas fa-users"></i><span>Participations</span></a>
                    <a href="../../index.php" class="action-btn"><i class="fas fa-home"></i><span>Accueil</span></a>
                </div>
            </div>
        </div>

        <!-- Recent Events -->
        <div class="card-pro">
            <div class="card-header-pro">
                <i class="fas fa-history me-2"></i> Derniers événements 
                <a href="../evenement/liste.php" class="float-end text-decoration-none" style="color: #1a5e2a; font-size: 0.75rem;">Voir tout <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach($evenementsRecents as $e): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?php echo htmlspecialchars($e['titre']); ?></strong><br>
                        <small><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($e['lieu']); ?> | <i class="fas fa-calendar me-1"></i><?php echo date('d/m/Y', strtotime($e['date_evenement'])); ?></small>
                    </div>
                    <div>
                        <a href="../evenement/modifier.php?id=<?php echo $e['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        <a href="../evenement/supprimer.php?id=<?php echo $e['id']; ?>" class="btn btn-danger btn-sm ms-1" onclick="return confirm('Supprimer cet événement ?')"><i class="fas fa-trash"></i></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        // Pie Chart
        new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($categoriesNom); ?>,
                datasets: [{
                    data: <?php echo json_encode($categoriesCount); ?>,
                    backgroundColor: <?php echo json_encode(array_slice($couleurs, 0, count($categoriesNom))); ?>,
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { 
                        position: 'bottom', 
                        labels: { 
                            font: { size: 11, weight: 'bold' }, 
                            boxWidth: 12,
                            padding: 12
                        } 
                    },
                    tooltip: { 
                        callbacks: { 
                            label: function(context) { 
                                return context.label + ': ' + context.raw + ' événement(s)'; 
                            } 
                        } 
                    }
                }
            }
        });

        // Calendrier avec jours de la semaine bien affichés
        document.addEventListener('DOMContentLoaded', function() {
            var events = <?php 
                $eventsArray = [];
                foreach($evenementC->afficherEvenements() as $e) {
                    $eventsArray[] = [
                        'id' => $e['id'],
                        'title' => $e['titre'],
                        'start' => $e['date_evenement'],
                        'lieu' => $e['lieu'],
                        'color' => '#1a5e2a',
                        'url' => '../evenement/modifier.php?id=' . $e['id']
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
                        if (info.event.url) {
                            window.location.href = info.event.url;
                        }
                    },
                    eventDidMount: function(info) {
                        // Tooltip avec le lieu
                        if (info.event.extendedProps.lieu) {
                            info.el.setAttribute('title', info.event.title + ' - ' + info.event.extendedProps.lieu);
                        } else {
                            info.el.setAttribute('title', info.event.title);
                        }
                    },
                    height: 500,
                    contentHeight: 'auto',
                    weekNumbers: true,
                    dayMaxEvents: true,
                    displayEventTime: false,
                    firstDay: 1, // Lundi comme premier jour de la semaine
                    weekNumberCalculation: 'ISO',
                    columnHeaderFormat: {
                        weekday: 'long'
                    }
                });
                calendar.render();
            }
        });
    </script>
</body>
</html>