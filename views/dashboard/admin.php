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
$evenementsRecents = $evenementC->afficherEvenements();
$topEvenements = array_slice($evenementsRecents, 0, 5);

// Calcul des statistiques supplémentaires
$evenementsPasses = 0;
$evenementsAVenir = 0;
$dateActuelle = date('Y-m-d');

foreach ($evenementsRecents as $event) {
    if ($event['date_evenement'] < $dateActuelle) {
        $evenementsPasses++;
    } else {
        $evenementsAVenir++;
    }
}

$tauxParticipation = $totalEvenements > 0 ? round(($totalParticipations / $totalEvenements), 1) : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --card-shadow: 0 10px 25px rgba(0,0,0,0.05);
            --hover-shadow: 0 15px 35px rgba(26, 94, 42, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--bg-dark);
            font-family: 'Segoe UI', 'Poppins', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
            position: fixed;
            width: 280px;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 30px 25px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            margin-bottom: 20px;
        }

        .sidebar-brand img {
            border-radius: 15px;
            margin-bottom: 12px;
            transition: transform 0.3s;
        }

        .sidebar-brand img:hover {
            transform: scale(1.05);
        }

        .sidebar-brand h3 {
            color: white;
            margin: 10px 0 0;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .sidebar-brand p {
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .sidebar-nav {
            padding: 10px 0;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: all 0.3s;
            margin: 5px 0;
            border-radius: 12px;
            margin-left: 10px;
            margin-right: 10px;
        }

        .sidebar-nav a:hover, .sidebar-nav a.active {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(5px);
        }

        .sidebar-nav a i {
            width: 28px;
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .sidebar-nav hr {
            border-color: rgba(255,255,255,0.1);
            margin: 15px 20px;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: 280px;
            padding: 25px 35px;
            transition: all 0.3s;
        }

        /* ========== HEADER ========== */
        .header-welcome {
            background: white;
            border-radius: 20px;
            padding: 20px 30px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .welcome-text h1 {
            font-size: 1.8rem;
            color: var(--primary-dark);
            margin-bottom: 5px;
        }

        .welcome-text p {
            color: #666;
            margin: 0;
        }

        .date-time {
            text-align: right;
        }

        .date-time .date {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .date-time .time {
            font-size: 0.9rem;
            color: #888;
        }

        /* ========== STATS CARDS ========== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            border: 1px solid rgba(26, 94, 42, 0.08);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-light));
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            font-weight: 600;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: var(--light-green);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary-dark);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 5px;
        }

        .stat-change {
            font-size: 0.8rem;
            color: #888;
        }

        .stat-change i {
            margin-right: 3px;
        }

        .stat-change.positive {
            color: var(--secondary-dark);
        }

        /* ========== CHARTS SECTION ========== */
        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }

        .chart-card {
            background: white;
            border-radius: 24px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s;
        }

        .chart-card:hover {
            box-shadow: var(--hover-shadow);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-green);
        }

        .chart-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-dark);
            margin: 0;
        }

        .chart-header i {
            font-size: 1.3rem;
            color: var(--secondary-light);
        }

        .chart-container {
            position: relative;
            height: 280px;
        }

        /* ========== RECENT EVENTS ========== */
        .recent-section {
            background: white;
            border-radius: 24px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            margin-bottom: 35px;
        }

        .recent-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-green);
        }

        .recent-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-dark);
            margin: 0;
        }

        .recent-header a {
            color: var(--primary-dark);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .recent-header a:hover {
            text-decoration: underline;
        }

        .event-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-radius: 16px;
            transition: all 0.3s;
            margin-bottom: 10px;
        }

        .event-item:hover {
            background: var(--light-green);
            transform: translateX(5px);
        }

        .event-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .event-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--light-green), white);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-size: 1.2rem;
        }

        .event-details h4 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0 0 5px 0;
            color: var(--text-dark);
        }

        .event-details p {
            font-size: 0.8rem;
            color: #888;
            margin: 0;
        }

        .event-status {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-upcoming {
            background: #fff3e0;
            color: #e65100;
        }

        .badge-past {
            background: #f5f5f5;
            color: #9e9e9e;
        }

        .event-actions {
            display: flex;
            gap: 8px;
        }

        /* ========== QUICK ACTIONS ========== */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .action-btn {
            background: white;
            border: 1px solid rgba(26, 94, 42, 0.15);
            border-radius: 16px;
            padding: 15px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .action-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: var(--hover-shadow);
        }

        .action-btn:hover span, .action-btn:hover i {
            color: white;
        }

        .action-btn i {
            font-size: 1.3rem;
            color: var(--primary-dark);
        }

        .action-btn span {
            font-weight: 600;
            color: var(--text-dark);
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }
            .sidebar-brand h3, .sidebar-brand p, .sidebar-nav a span {
                display: none;
            }
            .sidebar-nav a {
                justify-content: center;
                padding: 12px;
            }
            .sidebar-nav a i {
                margin-right: 0;
                font-size: 1.3rem;
            }
            .main-content {
                margin-left: 80px;
            }
            .charts-section {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .header-welcome {
                flex-direction: column;
                text-align: center;
            }
            .date-time {
                text-align: center;
                margin-top: 10px;
            }
            .event-item {
                flex-direction: column;
                gap: 10px;
            }
            .event-actions {
                width: 100%;
                justify-content: center;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card, .chart-card, .recent-section {
            animation: fadeInUp 0.5s ease forwards;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
    </style>
</head>
<body>
    <!-- ========== SIDEBAR ========== -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="../../logo.jpeg" alt="Smart Municipality" height="55" width="55" style="border-radius: 15px; object-fit: cover;">
            <h3>Smart Municipality</h3>
            <p>Administrateur</p>
        </div>
        <div class="sidebar-nav">
            <a href="admin.php" class="active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="../evenement/liste.php">
                <i class="fas fa-calendar-alt"></i>
                <span>Événements</span>
            </a>
            <a href="categorie/liste.php">
                <i class="fas fa-tags"></i>
                <span>Catégories</span>
            </a>
            <a href="../participation/mes_participations.php">
                <i class="fas fa-users"></i>
                <span>Participations</span>
            </a>
            <hr>
            <a href="../../index.php">
                <i class="fas fa-home"></i>
                <span>Accueil</span>
            </a>
            <a href="../../logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </div>

    <!-- ========== MAIN CONTENT ========== -->
    <div class="main-content">
        <!-- Welcome Header -->
        <div class="header-welcome">
            <div class="welcome-text">
                <h1>
                    <i class="fas fa-chart-line me-2" style="color: var(--primary-dark);"></i>
                    Bonjour, <?php echo htmlspecialchars($_SESSION['prenom']); ?> !
                </h1>
                <p>Voici ce qui se passe dans votre plateforme aujourd'hui.</p>
            </div>
            <div class="date-time">
                <div class="date">
                    <i class="fas fa-calendar-alt me-1"></i>
                    <?php echo date('l d F Y', strtotime('now')); ?>
                </div>
                <div class="time">
                    <i class="fas fa-clock me-1"></i>
                    <?php echo date('H:i'); ?>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Événements</span>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $totalEvenements; ?></div>
                <div class="stat-change">
                    <i class="fas fa-chart-line"></i> 
                    <?php echo $evenementsAVenir; ?> à venir, <?php echo $evenementsPasses; ?> passés
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Participations</span>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $totalParticipations; ?></div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 
                    <?php echo $tauxParticipation; ?> participants/événement
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Catégories</span>
                    <div class="stat-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $totalCategories; ?></div>
                <div class="stat-change">
                    <i class="fas fa-folder"></i> Types d'événements
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Taux d'occupation</span>
                    <div class="stat-icon">
                        <i class="fas fa-percent"></i>
                    </div>
                </div>
                <div class="stat-value">
                    <?php 
                    $taux = $totalEvenements > 0 ? round(($totalParticipations / ($totalEvenements * 50)) * 100, 0) : 0;
                    echo $taux . '%';
                    ?>
                </div>
                <div class="stat-change">
                    <i class="fas fa-users"></i> Capacité moyenne
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <!-- Bar Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-bar me-2"></i> Événements par catégorie</h3>
                    <i class="fas fa-chart-simple"></i>
                </div>
                <div class="chart-container">
                    <canvas id="categorieChart"></canvas>
                </div>
            </div>

            <!-- Doughnut Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-pie me-2"></i> Répartition des événements</h3>
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="chart-container">
                    <canvas id="repartitionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Events -->
        <div class="recent-section">
            <div class="recent-header">
                <h3><i class="fas fa-history me-2"></i> Derniers événements</h3>
                <a href="../evenement/liste.php">
                    Voir tout <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="events-list">
                <?php foreach($topEvenements as $event): 
                    $isUpcoming = $event['date_evenement'] >= date('Y-m-d');
                ?>
                <div class="event-item">
                    <div class="event-info">
                        <div class="event-icon">
                            <i class="fas <?php echo $isUpcoming ? 'fa-calendar-check' : 'fa-calendar-times'; ?>"></i>
                        </div>
                        <div class="event-details">
                            <h4><?php echo htmlspecialchars($event['titre']); ?></h4>
                            <p>
                                <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($event['lieu']); ?> |
                                <i class="fas fa-calendar me-1"></i> <?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?> |
                                <i class="fas fa-clock me-1"></i> <?php echo $event['heure']; ?>
                            </p>
                        </div>
                        <div class="event-status">
                            <span class="badge-status <?php echo $isUpcoming ? 'badge-upcoming' : 'badge-past'; ?>">
                                <i class="fas <?php echo $isUpcoming ? 'fa-hourglass-half' : 'fa-check-double'; ?> me-1"></i>
                                <?php echo $isUpcoming ? 'À venir' : 'Passé'; ?>
                            </span>
                            <span class="badge-status" style="background: var(--light-green); color: var(--primary-dark);">
                                <i class="fas fa-users me-1"></i>
                                <?php echo $participationC->compterParticipationsParEvenement($event['id']); ?> inscrits
                            </span>
                        </div>
                    </div>
                    <div class="event-actions">
                        <a href="../evenement/modifier.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-warning" style="border-radius: 10px;">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="../evenement/supprimer.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-danger" style="border-radius: 10px;" onclick="return confirm('Supprimer cet événement ?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="../evenement/ajouter.php" class="action-btn">
                <i class="fas fa-plus-circle"></i>
                <span>Ajouter un événement</span>
            </a>
            <a href="categorie/ajouter.php" class="action-btn">
                <i class="fas fa-tag"></i>
                <span>Ajouter une catégorie</span>
            </a>
            <a href="../evenement/liste.php" class="action-btn">
                <i class="fas fa-list"></i>
                <span>Gérer les événements</span>
            </a>
            <a href="categorie/liste.php" class="action-btn">
                <i class="fas fa-tags"></i>
                <span>Gérer les catégories</span>
            </a>
        </div>
    </div>

    <script>
        // Bar Chart - Événements par catégorie
        const ctx1 = document.getElementById('categorieChart').getContext('2d');
        const categories = <?php echo json_encode(array_column($evenementsParCategorie, 'nom')); ?>;
        const counts = <?php echo json_encode(array_column($evenementsParCategorie, 'total')); ?>;
        
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: categories,
                datasets: [{
                    label: 'Nombre d\'événements',
                    data: counts,
                    backgroundColor: 'rgba(46, 125, 50, 0.7)',
                    borderColor: '#1a5e2a',
                    borderWidth: 2,
                    borderRadius: 8,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 12 } }
                    },
                    tooltip: {
                        backgroundColor: '#1a5e2a',
                        titleColor: 'white',
                        bodyColor: 'rgba(255,255,255,0.9)',
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        stepSize: 1,
                        grid: { drawBorder: false, color: 'rgba(0,0,0,0.05)' },
                        ticks: { precision: 0, stepSize: 1 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // Doughnut Chart - Répartition (À venir vs Passés)
        const ctx2 = document.getElementById('repartitionChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Événements à venir', 'Événements passés'],
                datasets: [{
                    data: [<?php echo $evenementsAVenir; ?>, <?php echo $evenementsPasses; ?>],
                    backgroundColor: ['#2e7d32', '#9e9e9e'],
                    borderColor: 'white',
                    borderWidth: 3,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 12 }, padding: 15 }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = <?php echo $totalEvenements; ?>;
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: ${value} événements (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    </script>
</body>
</html>