<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>BackOffice - Dashboard Smart Municipality</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f4f8;
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, #2FA084, #0f3b2c);
            position: fixed;
            height: 100%;
            padding: 1rem;
        }

        .sidebar h2 {
            color: white;
            margin-bottom: 2rem;
            text-align: center;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 0.75rem 1rem;
            margin: 0.5rem 0;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .sidebar a:hover, .sidebar a.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        /* MAIN CONTENT */
        .main {
            margin-left: 270px;
            padding: 2rem;
        }

        /* HEADER */
        .header {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #0f3b2c;
            font-size: 28px;
        }

        .header p {
            color: #666;
            margin-top: 5px;
        }

        /* STATS CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .icon {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #2FA084;
        }

        .stat-card .label {
            color: #666;
            margin-top: 10px;
        }

        /* TWO COLUMNS */
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .chart-box {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .chart-box h3 {
            color: #0f3b2c;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2FA084;
        }

        /* SERVICE LIST */
        .service-list {
            list-style: none;
        }

        .service-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .service-name {
            font-weight: 500;
        }

        .service-count {
            background: linear-gradient(135deg, #2FA084, #0f3b2c);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }

        /* BAR CHART */
        .bar-chart {
            margin-top: 20px;
        }

        .bar-item {
            margin-bottom: 15px;
        }

        .bar-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .bar-bg {
            background: #e0e0e0;
            border-radius: 10px;
            height: 30px;
            overflow: hidden;
        }

        .bar-fill {
            background: linear-gradient(90deg, #2FA084, #0f3b2c);
            height: 100%;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        /* LAST DEMANDES TABLE */
        .last-demandes {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .last-demandes h3 {
            color: #0f3b2c;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2FA084;
        }

        .demandes-table {
            width: 100%;
            border-collapse: collapse;
        }

        .demandes-table th,
        .demandes-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .demandes-table th {
            background: linear-gradient(135deg, #2FA084, #0f3b2c);
            color: white;
        }

        .btn-back {
            background: linear-gradient(135deg, #2FA084, #0f3b2c);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            margin-top: 20px;
            font-size: 16px;
        }

        .btn-back:hover {
            background: linear-gradient(135deg, #0f3b2c, #2FA084);
        }

        @media (max-width: 1000px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .two-columns {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR - BOUTONS SUPPRIMES -->
    <div class="sidebar">
        <h2>🏛️ Smart Municipality</h2>
        <a href="index.php?action=dashboard" class="active">📊 Dashboard</a>
        <a href="#">👤 Profil</a>
        <a href="#">⚠️ Signalement</a>
        <a href="#">📝 Blog</a>
        <a href="#">🎉 Événement</a>
        <a href="#">📅 RDV</a>
        <a href="#">⚙️ Paramètres</a>
        <a href="#">🚪 Déconnexion</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main">
        <div class="header">
            <h1>📊 Tableau de bord - BackOffice</h1>
            <p>Bienvenue dans l'interface d'administration de la Smart Municipality</p>
        </div>

        <!-- STATISTIQUES CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">📋</div>
                <div class="number"><?php echo $total_demandes; ?></div>
                <div class="label">Total des demandes</div>
            </div>
            <div class="stat-card">
                <div class="icon">🏆</div>
                <div class="number"><?php echo !empty($top_services[0]) ? $top_services[0]['nombre'] : 0; ?></div>
                <div class="label">Service le plus demandé</div>
            </div>
            <div class="stat-card">
                <div class="icon">📅</div>
                <div class="number"><?php echo !empty($demandes_mois) ? $demandes_mois[0]['nombre'] : 0; ?></div>
                <div class="label">Demandes ce mois</div>
            </div>
            <div class="stat-card">
                <div class="icon">🎯</div>
                <div class="number"><?php echo count($services_stats); ?></div>
                <div class="label">Services disponibles</div>
            </div>
        </div>

        <!-- DEUX COLONNES -->
        <div class="two-columns">
            <!-- Répartition par service -->
            <div class="chart-box">
                <h3>📊 Répartition par type de service</h3>
                <div class="bar-chart">
                    <?php 
                    $maxCount = !empty($services_stats) ? max(array_column($services_stats, 'nombre')) : 1;
                    foreach($services_stats as $service): 
                        $percentage = ($service['nombre'] / $maxCount) * 100;
                    ?>
                        <div class="bar-item">
                            <div class="bar-label">
                                <span><?php echo htmlspecialchars($service['type_service']); ?></span>
                                <span><?php echo $service['nombre']; ?> demande(s)</span>
                            </div>
                            <div class="bar-bg">
                                <div class="bar-fill" style="width: <?php echo $percentage; ?>%;">
                                    <?php if($percentage > 20): ?>
                                        <?php echo $service['nombre']; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($services_stats)): ?>
                        <p style="text-align:center; color:#999; padding:20px;">Aucune donnée disponible</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top 3 services -->
            <div class="chart-box">
                <h3>🏆 Top 3 des services les plus demandés</h3>
                <ul class="service-list">
                    <?php if(!empty($top_services)): ?>
                        <?php 
                        $icons = ['🥇', '🥈', '🥉'];
                        foreach($top_services as $index => $service): 
                        ?>
                            <li class="service-item">
                                <span class="service-name"><?php echo $icons[$index] ?? '📌'; ?> <?php echo htmlspecialchars($service['type_service']); ?></span>
                                <span class="service-count"><?php echo $service['nombre']; ?> demandes</span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="service-item">
                            <span class="service-name">Aucune demande pour le moment</span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Évolution des demandes -->
        <div class="chart-box" style="margin-bottom: 30px;">
            <h3>📈 Évolution des demandes (6 derniers mois)</h3>
            <div class="bar-chart">
                <?php if(!empty($demandes_mois)): 
                    $maxMonthCount = max(array_column($demandes_mois, 'nombre'));
                ?>
                    <?php foreach(array_reverse($demandes_mois) as $mois): 
                        $percentage = ($mois['nombre'] / $maxMonthCount) * 100;
                        $mois_name = date('F Y', strtotime($mois['mois'] . '-01'));
                    ?>
                        <div class="bar-item">
                            <div class="bar-label">
                                <span><?php echo $mois_name; ?></span>
                                <span><?php echo $mois['nombre']; ?> demande(s)</span>
                            </div>
                            <div class="bar-bg">
                                <div class="bar-fill" style="width: <?php echo $percentage; ?>%;">
                                    <?php if($percentage > 20): ?>
                                        <?php echo $mois['nombre']; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center; color:#999; padding:20px;">Aucune donnée disponible</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dernières demandes -->
        <div class="last-demandes">
            <h3>🕐 Dernières demandes soumises</h3>
            <table class="demandes-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Type de service</th>
                        <th>Date de création</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($last_demandes)): ?>
                        <?php foreach($last_demandes as $demande): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($demande['id']); ?></td>
                                <td><?php echo htmlspecialchars($demande['nom']); ?></td>
                                <td><?php echo htmlspecialchars($demande['type_service']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($demande['date_creation'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center; color:#999; padding:30px;">Aucune demande trouvée</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php?action=manage">
                <button class="btn-back">← Retour au Front Office</button>
            </a>
        </div>
    </div>

</body>
</html>