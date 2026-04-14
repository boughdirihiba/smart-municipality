<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f4f8;
        }
        
        /* Sidebar Vert */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #0a2e1f 0%, #1a5a3a 50%, #0d3d26 100%);
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            box-shadow: 2px 0 15px rgba(0,0,0,0.15);
            overflow-y: auto;
        }
        
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #2ecc71, #27ae60, #2ecc71);
        }
        
        .sidebar::-webkit-scrollbar {
            width: 5px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: #2ecc71;
            border-radius: 10px;
        }
        
        .logo {
            padding: 25px 24px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            margin-bottom: 20px;
            background: linear-gradient(90deg, rgba(255,255,255,0.05), transparent);
        }
        
        .logo h2 {
            color: white;
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo h2 i {
            font-size: 28px;
            color: #2ecc71;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 24px;
            margin: 5px 12px;
            text-decoration: none;
            color: rgba(255,255,255,0.8);
            border-radius: 12px;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        
        .nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(46,204,113,0.2), transparent);
            transition: left 0.5s;
        }
        
        .nav-item:hover::before {
            left: 100%;
        }
        
        .nav-item:hover {
            background: linear-gradient(90deg, rgba(46,204,113,0.2), rgba(46,204,113,0.05));
            color: white;
            transform: translateX(5px);
        }
        
        .nav-item.active {
            background: linear-gradient(90deg, #27ae60, #1e8449);
            color: white;
            box-shadow: 0 2px 8px rgba(39,174,96,0.3);
        }
        
        .nav-item i {
            width: 20px;
            font-size: 16px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 25px 35px;
        }
        
        .back-btn-container {
            margin-bottom: 25px;
        }
        
        .btn-back {
            background: #95a5a6;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }
        
        .dashboard-header {
            margin-bottom: 30px;
        }
        
        .dashboard-header h1 {
            color: #1e8449;
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .dashboard-header p {
            color: #6c757d;
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #2ecc71;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
        }
        
        .stat-card h3 {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #1e8449;
            font-family: 'Poppins', sans-serif;
        }
        
        .stat-label {
            font-size: 12px;
            color: #27ae60;
            margin-top: 8px;
        }
        
        /* Charts */
        .charts-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .chart-card h3 {
            color: #1e8449;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
            border-bottom: 2px solid #2ecc71;
            padding-bottom: 10px;
            display: inline-block;
        }
        
        .bar-chart {
            margin-top: 15px;
        }
        
        .bar-item {
            margin-bottom: 15px;
        }
        
        .bar-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 13px;
            color: #555;
        }
        
        .bar-bg {
            background: #e9ecef;
            border-radius: 10px;
            height: 30px;
            overflow: hidden;
        }
        
        .bar-fill {
            background: linear-gradient(90deg, #2ecc71, #27ae60);
            height: 100%;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            color: white;
            font-size: 12px;
            font-weight: 600;
        }
        
        /* Pie Chart Container */
        .pie-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 20px;
        }
        
        .pie-chart {
            width: 220px;
            height: 220px;
            border-radius: 50%;
            margin-bottom: 25px;
            transition: transform 0.3s;
        }
        
        .pie-chart:hover {
            transform: scale(1.05);
        }
        
        .pie-legend {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
        }
        
        .legend-color {
            width: 14px;
            height: 14px;
            border-radius: 4px;
        }
        
        /* Activities Section */
        .activities-section {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .activities-section h3 {
            color: #1e8449;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
            border-bottom: 2px solid #2ecc71;
            padding-bottom: 10px;
            display: inline-block;
        }
        
        .refresh-btn {
            float: right;
            background: #2ecc71;
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.3s;
        }
        
        .refresh-btn:hover {
            background: #27ae60;
        }
        
        .activities-list {
            margin-top: 20px;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eef2f6;
            transition: background 0.3s;
        }
        
        .activity-item:hover {
            background: #f8f9fa;
        }
        
        .activity-date {
            min-width: 100px;
            text-align: center;
        }
        
        .activity-date .day {
            font-size: 24px;
            font-weight: 700;
            color: #1e8449;
            font-family: 'Poppins', sans-serif;
        }
        
        .activity-date .month {
            font-size: 12px;
            color: #6c757d;
        }
        
        .activity-info {
            flex: 1;
            padding: 0 15px;
        }
        
        .activity-info h4 {
            color: #333;
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .activity-info p {
            color: #6c757d;
            font-size: 13px;
        }
        
        .activity-category {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .activity-category.Culture { background: #d5f5e3; color: #1e8449; }
        .activity-category.Sport { background: #d5f5e3; color: #1e8449; }
        .activity-category.Environnement { background: #d5f5e3; color: #1e8449; }
        .activity-category.Social { background: #d5f5e3; color: #1e8449; }
        .activity-category.Education { background: #d5f5e3; color: #1e8449; }
        
        .activity-time {
            min-width: 80px;
            text-align: right;
            color: #6c757d;
            font-size: 13px;
        }
        
        .no-activities {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .quick-actions {
            background: white;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .quick-actions h3 {
            color: #1e8449;
            margin-bottom: 15px;
        }
        
        .btn-manage {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .btn-manage:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46,204,113,0.3);
        }
        
        @media (max-width: 900px) {
            .sidebar {
                width: 80px;
            }
            .sidebar .logo h2 span:last-child,
            .sidebar .nav-item span:last-child {
                display: none;
            }
            .main-content {
                margin-left: 80px;
            }
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
            .charts-container {
                grid-template-columns: 1fr;
            }
            .activity-item {
                flex-direction: column;
                text-align: center;
            }
            .activity-date, .activity-time {
                margin-bottom: 10px;
            }
        }
        .logo {
    padding: 20px 24px 25px;
    border-bottom: 1px solid rgba(255,255,255,0.15);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo-img {
    width: 45px;
    height: 45px;
    object-fit: cover;
    border-radius: 12px;
    background: white;
    padding: 5px;
}

.logo-text {
    display: flex;
    flex-direction: column;
}

.logo-title {
    color: white;
    font-family: 'Poppins', sans-serif;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.2;
}


    </style>
</head>
<body>

<!-- Sidebar Vert -->
<div class="sidebar">

    <div class="logo">
    <img src="assets/logo.jpeg" alt="Smart Municipality" class="logo-img">
    <div class="logo-text">
        <span class="logo-title">Smart Municipality</span>
        
    </div>
</div>
    <a href="profile.php" class="nav-item">
        <i class="fas fa-user-circle"></i>
        <span>Profile</span>
    </a>
    
    <a href="index.php?action=manage" class="nav-item">
        <i class="fas fa-calendar-alt"></i>
        <span>Événements</span>
    </a>
    

    
    <a href="carte.php" class="nav-item">
        <i class="fas fa-map-marked-alt"></i>
        <span>Carte intelligente</span>
    </a>
    
    <a href="blog.php" class="nav-item">
        <i class="fas fa-blog"></i>
        <span>Blog</span>
    </a>
    
    <a href="services.php" class="nav-item">
        <i class="fas fa-concierge-bell"></i>
        <span>Services en ligne</span>
    </a>
    
    <a href="rdv.php" class="nav-item">
        <i class="fas fa-calendar-check"></i>
        <span>Rendez-vous</span>
    </a>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="back-btn-container">
        <a href="index.php?action=manage" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour à la gestion
        </a>
    </div>

    <div class="dashboard-header">
        <h1><i class="fas fa-chart-line"></i> Tableau de Bord</h1>
        <p>Statistiques et analyses des événements municipaux</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <h3><i class="fas fa-calendar-alt"></i> TOTAL ÉVÉNEMENTS</h3>
            <div class="stat-number"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Événements créés</div>
        </div>
        <div class="stat-card">
            <h3><i class="fas fa-clock"></i> À VENIR</h3>
            <div class="stat-number"><?php echo $stats['upcoming']; ?></div>
            <div class="stat-label">Événements futurs</div>
        </div>
        <div class="stat-card">
            <h3><i class="fas fa-check-circle"></i> TAUX PARTICIPATION</h3>
            <div class="stat-number">
                <?php 
                // Calcul du taux de participation (exemple: 75%)
                $participation_rate = $stats['total'] > 0 ? round(($stats['upcoming'] / $stats['total']) * 100) : 0;
                echo $participation_rate . '%';
                ?>
            </div>
            <div class="stat-label">Taux de participation estimé</div>
        </div>
        <div class="stat-card">
            <h3><i class="fas fa-tags"></i> CATÉGORIES</h3>
            <div class="stat-number">
                <?php 
                $active_categories = 0;
                foreach($stats['by_category'] as $count) {
                    if($count > 0) $active_categories++;
                }
                echo $active_categories;
                ?>
            </div>
            <div class="stat-label">Catégories actives / 5</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-container">
        <!-- Bar Chart -->
        <div class="chart-card">
            <h3><i class="fas fa-chart-bar"></i> Événements par Catégorie</h3>
            <div class="bar-chart">
                <?php 
                $maxCount = max($stats['by_category']) ?: 1;
                foreach($stats['by_category'] as $cat => $count): 
                    $percentage = ($count / $maxCount) * 100;
                ?>
                <div class="bar-item">
                    <div class="bar-label">
                        <span><i class="fas fa-tag"></i> <?php echo $cat; ?></span>
                        <span><?php echo $count; ?> événement(s)</span>
                    </div>
                    <div class="bar-bg">
                        <div class="bar-fill" style="width: <?php echo $percentage; ?>%;">
                            <?php if($count > 0): ?><?php echo $count; ?><?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pie Chart avec NOUVELLES STATISTIQUES -->
        <div class="chart-card">
            <h3><i class="fas fa-chart-pie"></i> Analyse des Événements</h3>
            <div class="pie-container">
                <?php
                // Calcul des nouvelles statistiques pour le pie chart
                $total = $stats['total'];
                $upcoming = $stats['upcoming'];
                $past = $stats['past'];
                
                // Pourcentage
                $upcoming_percent = $total > 0 ? round(($upcoming / $total) * 100) : 0;
                $past_percent = $total > 0 ? round(($past / $total) * 100) : 0;
                
                // Angle pour le conic-gradient
                $upcoming_angle = $total > 0 ? ($upcoming / $total) * 360 : 0;
                ?>
                
                <!-- Pie Chart SVG -->
                <div class="pie-chart" style="background: conic-gradient(
                    #2ecc71 0deg <?php echo $upcoming_angle; ?>deg,
                    #95a5a6 <?php echo $upcoming_angle; ?>deg <?php echo $upcoming_angle + (($past / $total) * 360); ?>deg,
                    #e74c3c <?php echo $upcoming_angle + (($past / $total) * 360); ?>deg 360deg
                );"></div>
                
                <div class="pie-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background: #2ecc71;"></div>
                        <span>À venir: <?php echo $upcoming; ?> (<?php echo $upcoming_percent; ?>%)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #95a5a6;"></div>
                        <span>Terminés: <?php echo $past; ?> (<?php echo $past_percent; ?>%)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #e74c3c;"></div>
                        <span>Annulés: 0 (0%)</span>
                    </div>
                </div>
                
                <!-- Statistiques supplémentaires -->
                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; width: 100%; text-align: center;">
                    <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 15px;">
                        <div>
                            <small style="color: #6c757d;">Moyenne/mois</small>
                            <div style="font-weight: bold; color: #1e8449;">
                                <?php echo $total > 0 ? round($total / 3) : 0; ?>
                            </div>
                        </div>
                        <div>
                            <small style="color: #6c757d;">Catégorie populaire</small>
                            <div style="font-weight: bold; color: #1e8449;">
                                <?php 
                                $max_cat = max($stats['by_category']);
                                $popular_cat = array_search($max_cat, $stats['by_category']);
                                echo $popular_cat ?: 'Aucune';
                                ?>
                            </div>
                        </div>
                        <div>
                            <small style="color: #6c757d;">Prochain événement</small>
                            <div style="font-weight: bold; color: #1e8449;">
                                <?php 
                                if(count($upcoming_events) > 0) {
                                    $next_event = reset($upcoming_events);
                                    echo date('d/m', strtotime($next_event['date_evenement']));
                                } else {
                                    echo '---';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activities Section -->
    <div class="activities-section">
        <h3><i class="fas fa-calendar-week"></i> Activités à venir</h3>
        <a href="index.php?action=dashboard" class="refresh-btn">
            <i class="fas fa-sync-alt"></i> Actualiser
        </a>
        <div style="clear: both;"></div>
        
        <div class="activities-list">
            <?php if(count($upcoming_events) > 0): ?>
                <?php foreach($upcoming_events as $event): ?>
                    <div class="activity-item">
                        <div class="activity-date">
                            <div class="day"><?php echo date('d', strtotime($event['date_evenement'])); ?></div>
                            <div class="month"><?php echo date('M Y', strtotime($event['date_evenement'])); ?></div>
                        </div>
                        <div class="activity-info">
                            <h4><?php echo htmlspecialchars($event['titre']); ?></h4>
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['lieu']); ?></p>
                        </div>
                        <div class="activity-category <?php echo $event['categorie']; ?>">
                            <?php echo $event['categorie']; ?>
                        </div>
                        <div class="activity-time">
                            <i class="fas fa-clock"></i> <?php echo $event['heure']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-activities">
                    <i class="fas fa-calendar-check"></i> Aucun événement à venir.<br>
                    <a href="index.php?action=create" style="color: #2ecc71;">Ajoutez un événement</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Action -->
    <div class="quick-actions">
        <h3><i class="fas fa-cog"></i> Gestion des événements</h3>
        <p style="margin-bottom: 15px; color: #6c757d;">Ajoutez, modifiez ou supprimez des événements</p>
        <a href="index.php?action=manage" class="btn-manage">
            <i class="fas fa-arrow-right"></i> Accéder à la gestion
        </a>
    </div>
</div>

</body>
</html>