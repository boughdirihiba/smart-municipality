<?php
session_start();
$user = $_SESSION['user'] ?? null;
$services = ['État Civil', 'Urbanisme', 'Cadastre', 'Services Sociaux', "Services d'usaiux"];
$heures = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];
$selectedService = $_GET['service'] ?? '';
$selectedDate = $_GET['date'] ?? '';
$selectedHeure = $_GET['heure'] ?? '';
$step = 1;
if (!empty($selectedService)) $step = 2;
if (!empty($selectedService) && !empty($selectedDate) && !empty($selectedHeure)) $step = 3;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality - Rendez-vous</title>
    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #e8efe8;
            min-height: 100vh;
        }

        .page-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 220px;
            background-color: #1a5c2a;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px 15px;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .sidebar-logo img {
            width: 40px;
            height: 40px;
        }

        .sidebar-logo h2 {
            font-size: 16px;
            line-height: 1.3;
        }

        .sidebar hr {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            margin: 15px 0;
        }

        .nav-menu ul,
        .nav-bottom ul {
            list-style: none;
        }

        .nav-menu ul li,
        .nav-bottom ul li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 5px;
            border-radius: 5px;
            cursor: pointer;
        }

        .nav-menu ul li:hover,
        .nav-bottom ul li:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }

        .nav-menu ul li.active {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .nav-menu ul li img,
        .nav-bottom ul li img {
            width: 20px;
            height: 20px;
        }

        .nav-menu ul li a,
        .nav-bottom ul li a {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .nav-menu {
            flex-grow: 1;
        }

        .main-content {
            flex: 1;
            padding: 20px 30px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 20px;
            padding: 8px 15px;
            width: 350px;
        }

        .search-bar img {
            width: 18px;
            height: 18px;
            margin-right: 10px;
        }

        .search-bar input {
            border: none;
            outline: none;
            font-size: 14px;
            width: 100%;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: bold;
        }

        .user-info img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
        }

        .page-title {
            text-align: center;
            font-size: 22px;
            color: #333;
            margin-bottom: 20px;
        }

        .steps {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-bottom: 25px;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #888;
        }

        .step.completed .step-icon {
            background-color: #1a5c2a;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .step.completed .step-text {
            color: #333;
            font-weight: bold;
        }

        .step.current .step-number {
            background-color: white;
            color: #1a5c2a;
            border: 2px solid #1a5c2a;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: bold;
        }

        .step.current .step-text {
            color: #333;
            font-weight: bold;
        }

        .content-area {
            display: flex;
            gap: 20px;
        }

        .column {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
        }

        .column-services {
            width: 30%;
        }

        .services-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .service-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 15px 10px;
            text-decoration: none;
            color: #333;
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s;
        }

        .service-card:hover {
            border-color: #1a5c2a;
        }

        .service-card.selected {
            border-color: #1a5c2a;
            background-color: #f0f8f0;
        }

        .service-card img {
            width: 40px;
            height: 40px;
            margin-bottom: 8px;
        }

        .column-calendar {
            width: 38%;
        }

        .calendar-header {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
        }

        .calendar-prev,
        .calendar-next {
            text-decoration: none;
            color: #333;
            font-size: 18px;
            font-weight: bold;
        }

        .calendar-month {
            font-size: 16px;
            font-weight: bold;
        }

        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            margin-bottom: 20px;
        }

        .calendar-table th {
            padding: 8px;
            font-size: 13px;
            color: #666;
        }

        .calendar-table td {
            padding: 8px;
            font-size: 13px;
        }

        .calendar-day {
            text-decoration: none;
            color: #333;
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            border-radius: 50%;
            text-align: center;
        }

        .calendar-day:hover {
            background-color: #e0e0e0;
        }

        .calendar-day.selected-day {
            background-color: #1a5c2a;
            color: white;
            font-weight: bold;
        }

        .time-title {
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
        }

        .time-slots {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .time-row {
            display: flex;
            gap: 10px;
        }

        .time-slot {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 8px 18px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s;
        }

        .time-slot:hover {
            border-color: #1a5c2a;
        }

        .time-slot.selected {
            border-color: #1a5c2a;
            background-color: #f0f8f0;
        }

        .column-summary {
            width: 32%;
            display: flex;
            flex-direction: column;
        }

        .summary-title {
            font-size: 16px;
            color: #333;
            margin-bottom: 10px;
        }

        .column-summary hr {
            border: none;
            border-top: 1px solid #ddd;
            margin-bottom: 15px;
        }

        .summary-label {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 2px;
        }

        .summary-value {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
        }

        .summary-details {
            flex-grow: 1;
        }

        .btn-confirm {
            background-color: #1a5c2a;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            text-align: center;
            margin-top: auto;
        }

        .btn-confirm:hover {
            background-color: #14491f;
        }

        .btn-confirm.disabled {
            background-color: #aaa;
            cursor: not-allowed;
        }

        .message-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .message-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }

    </style>
</head>
<body>

    <div class="page-wrapper">

        <aside class="sidebar">

            <div class="sidebar-logo">
                <img src="../../assets/icons/logo.png" alt="Logo">
                <h2>Smart<br>Municipality</h2>
            </div>

            <hr>

            <nav class="nav-menu">
                <ul>
                    <li>
                        <img src="../../assets/icons/profil.png" alt="">
                        <a href="profil.php">Profil</a>
                    </li>
                    <li>
                        <img src="../../assets/icons/signalement.png" alt="">
                        <a href="signalement.php">Signalement</a>
                    </li>
                    <li>
                        <img src="../../assets/icons/alertes.png" alt="">
                        <a href="alertes.php">Alertes</a>
                    </li>
                    <li>
                        <img src="../../assets/icons/carte.png" alt="">
                        <a href="carte.php">Carte intelligente</a>
                    </li>
                    <li>
                        <img src="../../assets/icons/services.png" alt="">
                        <a href="services.php">Services en ligne</a>
                    </li>
                    <li class="active">
                        <img src="../../assets/icons/rdv.png" alt="">
                        <a href="rendez-vous.php">Rendez-vous</a>
                    </li>
                </ul>
            </nav>

            <hr>

            <div class="nav-bottom">
                <ul>
                    <li>
                        <img src="../../assets/icons/parametres.png" alt="">
                        <a href="parametres.php">Paramètres</a>
                    </li>
                    <li>
                        <img src="../../assets/icons/deconnexion.png" alt="">
                        <a href="../../controllers/logout.php">Déconnexion</a>
                    </li>
                </ul>
            </div>

        </aside>

        <main class="main-content">

            <header class="top-bar">
                <div class="search-bar">
                    <img src="../../assets/icons/search.png" alt="">
                    <input type="text" placeholder="Rechercher">
                </div>
                <div class="user-info">
                    <span><?php echo $user ? htmlspecialchars($user['nom']) : 'Utilisateur'; ?></span>
                    <img src="../../assets/icons/avatar.png" alt="">
                </div>
            </header>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="message-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="message-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <h1 class="page-title">VOTRE COMPTE - RENDEZ-VOUS</h1>

            <div class="steps">
                <div class="step <?php echo $step >= 1 ? 'completed' : ''; ?>">
                    <span class="step-icon">&#10004;</span>
                    <span class="step-text">Sélectionner un Service</span>
                </div>
                <div class="step <?php echo $step == 2 ? 'current' : ($step > 2 ? 'completed' : ''); ?>">
                    <span class="step-number">2</span>
                    <span class="step-text">Choisir une Date</span>
                </div>
                <div class="step <?php echo $step == 3 ? 'completed' : ''; ?>">
                    <span class="step-icon">&#10004;</span>
                    <span class="step-text">Récapitulatif &amp; Confirmation</span>
                </div>
            </div>

            <div class="content-area">

                <div class="column column-services">
                    <div class="services-grid">
                        <?php foreach ($services as $service): ?>
                            <a href="rendez-vous.php?service=<?php echo urlencode($service); ?>" class="service-card <?php echo ($selectedService == $service) ? 'selected' : ''; ?>">
                                <img src="../../assets/icons/<?php echo strtolower(str_replace([' ', "'"], ['-', ''], $service)); ?>.png" alt="">
                                <span><?php echo htmlspecialchars($service); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="column column-calendar">

                    <?php
                    $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
                    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
                    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                    $firstDay = date('N', mktime(0, 0, 0, $month, 1, $year));
                    $monthName = strftime('%B', mktime(0, 0, 0, $month, 1, $year));
                    $prevMonth = $month - 1;
                    $prevYear = $year;
                    if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
                    $nextMonth = $month + 1;
                    $nextYear = $year;
                    if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
                    ?>

                    <div class="calendar-header">
                        <a href="rendez-vous.php?service=<?php echo urlencode($selectedService); ?>&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="calendar-prev">&lt;</a>
                        <span class="calendar-month"><?php echo $monthName . ' ' . $year; ?></span>
                        <a href="rendez-vous.php?service=<?php echo urlencode($selectedService); ?>&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="calendar-next">&gt;</a>
                    </div>

                    <table class="calendar-table">
                        <thead>
                            <tr>
                                <th>Lu</th>
                                <th>Ma</th>
                                <th>Me</th>
                                <th>Ju</th>
                                <th>Vr</th>
                                <th>Sa</th>
                                <th>So</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            <?php
                            $dayCount = 0;

                            for ($i = 1; $i < $firstDay; $i++) {
                                echo "<td></td>";
                                $dayCount++;
                            }

                            for ($day = 1; $day <= $daysInMonth; $day++) {
                                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                $isSelected = ($selectedDate == $dateStr) ? 'selected-day' : '';

                                echo '<td>';
                                echo '<a href="rendez-vous.php?service=' . urlencode($selectedService) . '&date=' . $dateStr . '&month=' . $month . '&year=' . $year . '" class="calendar-day ' . $isSelected . '">' . $day . '</a>';
                                echo '</td>';

                                $dayCount++;

                                if ($dayCount % 7 == 0 && $day != $daysInMonth) {
                                    echo "</tr><tr>";
                                }
                            }

                            while ($dayCount % 7 != 0) {
                                echo "<td></td>";
                                $dayCount++;
                            }
                            ?>
                            </tr>
                        </tbody>
                    </table>

                    <h4 class="time-title">Heure</h4>

                    <div class="time-slots">
                        <div class="time-row">
                            <?php for ($i = 0; $i < 3; $i++): ?>
                                <a href="rendez-vous.php?service=<?php echo urlencode($selectedService); ?>&date=<?php echo $selectedDate; ?>&heure=<?php echo $heures[$i]; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>" class="time-slot <?php echo ($selectedHeure == $heures[$i]) ? 'selected' : ''; ?>">
                                    <?php echo $heures[$i]; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                        <div class="time-row">
                            <?php for ($i = 3; $i < 6; $i++): ?>
                                <a href="rendez-vous.php?service=<?php echo urlencode($selectedService); ?>&date=<?php echo $selectedDate; ?>&heure=<?php echo $heures[$i]; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>" class="time-slot <?php echo ($selectedHeure == $heures[$i]) ? 'selected' : ''; ?>">
                                    <?php echo $heures[$i]; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    </div>

                </div>

                <div class="column column-summary">

                    <h3 class="summary-title">RÉSUMÉ DU RENDEZ-VOUS</h3>

                    <hr>

                    <div class="summary-details">
                        <p class="summary-label">Service:</p>
                        <p class="summary-value"><?php echo !empty($selectedService) ? htmlspecialchars($selectedService) : '---'; ?></p>

                        <p class="summary-label">Date:</p>
                        <p class="summary-value">
                            <?php
                            if (!empty($selectedDate)) {
                                setlocale(LC_TIME, 'fr_FR.UTF-8');
                                echo strftime('%A, %d %B', strtotime($selectedDate));
                            } else {
                                echo '---';
                            }
                            ?>
                        </p>

                        <p class="summary-label">Heure:</p>
                        <p class="summary-value"><?php echo !empty($selectedHeure) ? htmlspecialchars($selectedHeure) : '---'; ?></p>
                    </div>

                    <?php if ($step == 3): ?>
                        <form action="../../controllers/RendezVousController.php" method="POST">
                            <input type="hidden" name="action" value="create">
                            <input type="hidden" name="service" value="<?php echo htmlspecialchars($selectedService); ?>">
                            <input type="hidden" name="date_rdv" value="<?php echo htmlspecialchars($selectedDate); ?>">
                            <input type="hidden" name="heure" value="<?php echo htmlspecialchars($selectedHeure); ?>">
                            <button type="submit" class="btn-confirm">&#10004; Confirmer le rendez-vous</button>
                        </form>
                    <?php else: ?>
                        <button class="btn-confirm disabled" disabled>&#10004; Confirmer le rendez-vous</button>
                    <?php endif; ?>

                </div>

            </div>

        </main>

    </div>

</body>
</html>