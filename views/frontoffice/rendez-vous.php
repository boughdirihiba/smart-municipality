<?php
session_start();

require_once '../../config/database.php';
require_once '../../models/RendezVous.php';

$db = new Database();
$conn = $db->getConnection();
$rdv = new RendezVous($conn);

$categories = $rdv->getAllCategories();
$mesRdv = $rdv->readByUser(1);

$heures = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];
$selectedCategorieId = $_GET['categorie_id'] ?? '';
$selectedDate = $_GET['date'] ?? '';
$selectedHeure = $_GET['heure'] ?? '';

$slotTaken = false;
if (!empty($selectedCategorieId) && !empty($selectedDate) && !empty($selectedHeure)) {
    $slotTaken = $rdv->isSlotTaken($selectedCategorieId, $selectedDate, $selectedHeure);
}

$selectedCategorieName = '';
if (!empty($selectedCategorieId)) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $selectedCategorieId) {
            $selectedCategorieName = $cat['nom'];
            break;
        }
    }
}

$step = 1;
if (!empty($selectedCategorieId)) $step = 2;
if (!empty($selectedCategorieId) && !empty($selectedDate) && !empty($selectedHeure)) $step = 3;

$joursSemaine = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
$moisNoms = ['', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
$moisComplet = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality - Rendez-vous</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        html, body { height: 100%; }
        body { background-color: #e8efe8; }

        .page-wrapper { display: flex; min-height: 100vh; }

        .sidebar {
            width: 65px; min-width: 65px; background: linear-gradient(180deg, #135D36, #0F3B2C); color: white;
            display: flex; flex-direction: column; padding: 15px 8px; position: fixed;
            top: 0; left: 0; height: 100vh; overflow: hidden; z-index: 100;
            transition: width 0.3s ease, min-width 0.3s ease, padding 0.3s ease;
        }
        .sidebar:hover {
            width: 220px; min-width: 220px; padding: 20px 15px;
        }
        .sidebar-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; overflow: hidden; white-space: nowrap; }
        .sidebar-logo img { width: 40px; height: 40px; min-width: 40px; }
        .sidebar-logo h2 { font-size: 16px; line-height: 1.3; opacity: 0; transition: opacity 0.2s ease; }
        .sidebar:hover .sidebar-logo h2 { opacity: 1; }
        .sidebar hr { border: none; border-top: 1px solid rgba(255,255,255,0.15); margin: 15px 0; }
        .nav-menu ul, .nav-bottom ul { list-style: none; }
        .nav-menu ul li, .nav-bottom ul li { display: flex; align-items: center; gap: 10px; padding: 12px 12px; border-radius: 10px; cursor: pointer; overflow: hidden; white-space: nowrap; margin-bottom: 4px; }
        .nav-menu ul li:hover, .nav-bottom ul li:hover { background-color: rgba(255,255,255,0.1); }
        .nav-menu ul li.active { background: linear-gradient(135deg, #2FA084, #27ae60); border-radius: 10px; }
        .nav-menu ul li img, .nav-bottom ul li img { width: 20px; height: 20px; min-width: 20px; }
        .nav-menu ul li a, .nav-bottom ul li a { color: white; text-decoration: none; font-size: 14px; font-weight: 500; opacity: 0; transition: opacity 0.2s ease; }
        .sidebar:hover .nav-menu ul li a, .sidebar:hover .nav-bottom ul li a { opacity: 1; }
        .nav-menu { flex-grow: 1; }

        .main-content { flex: 1; padding: 15px 25px; margin-left: 65px; overflow-y: auto; transition: margin-left 0.3s ease; }
        .sidebar:hover ~ .main-content { margin-left: 220px; }

        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .search-bar { display: flex; align-items: center; background-color: white; border: 1px solid #ccc; border-radius: 20px; padding: 6px 15px; width: 300px; }
        .search-bar img { width: 16px; height: 16px; margin-right: 8px; }
        .search-bar input { border: none; outline: none; font-size: 13px; width: 100%; }
        .user-info { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: bold; }
        .user-info img { width: 30px; height: 30px; border-radius: 50%; }

        .page-title { text-align: center; font-size: 20px; color: #333; margin-bottom: 10px; }

        .steps { display: flex; justify-content: center; gap: 30px; margin-bottom: 15px; }
        .step { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #888; }
        .step.completed .step-icon { background-color: #135D36; color: white; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; }
        .step.completed .step-text { color: #333; font-weight: bold; }
        .step.current .step-number { background-color: white; color: #135D36; border: 2px solid #135D36; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; }
        .step.current .step-text { color: #333; font-weight: bold; }

        .content-area { display: flex; gap: 15px; min-height: 420px; }
        .column { background-color: white; border-radius: 10px; padding: 15px; overflow-y: auto; }
        .column-services { width: 25%; }
        .services-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .service-card { display: flex; flex-direction: column; align-items: center; justify-content: center; border: 2px solid #ddd; border-radius: 10px; padding: 12px 8px; text-decoration: none; color: #333; font-size: 12px; font-weight: bold; text-align: center; cursor: pointer; transition: border-color 0.2s; }
        .service-card:hover { border-color: #135D36; }
        .service-card.selected { border-color: #135D36; background-color: #f0f8f0; }
        .service-card img { width: 35px; height: 35px; margin-bottom: 6px; }

        .column-calendar { width: 40%; }
        .calendar-header { display: flex; justify-content: center; align-items: center; gap: 20px; margin-bottom: 10px; }
        .calendar-prev, .calendar-next { text-decoration: none; color: #333; font-size: 18px; font-weight: bold; }
        .calendar-month { font-size: 15px; font-weight: bold; }
        .calendar-table { width: 100%; border-collapse: collapse; text-align: center; margin-bottom: 15px; }
        .calendar-table th { padding: 6px; font-size: 12px; color: #666; }
        .calendar-table td { padding: 6px; font-size: 12px; }
        .calendar-day { text-decoration: none; color: #333; display: inline-block; width: 28px; height: 28px; line-height: 28px; border-radius: 50%; text-align: center; }
        .calendar-day:hover { background-color: #e0e0e0; }
        .calendar-day.selected-day { background-color: #135D36; color: white; font-weight: bold; }
        .time-title { font-size: 13px; margin-bottom: 8px; color: #333; }
        .time-slots { display: flex; flex-direction: column; gap: 8px; }
        .time-row { display: flex; gap: 8px; }
        .time-slot { border: 2px solid #ddd; border-radius: 8px; padding: 6px 16px; text-decoration: none; color: #333; font-size: 13px; font-weight: bold; text-align: center; cursor: pointer; transition: border-color 0.2s; }
        .time-slot:hover { border-color: #135D36; }
        .time-slot.selected { border-color: #135D36; background-color: #f0f8f0; }

        .column-summary { width: 35%; display: flex; flex-direction: column; }
        .summary-title { font-size: 15px; color: #333; margin-bottom: 8px; }
        .column-summary hr { border: none; border-top: 1px solid #ddd; margin-bottom: 12px; }
        .summary-label { font-size: 13px; font-weight: bold; color: #333; margin-bottom: 2px; }
        .summary-value { font-size: 13px; color: #555; margin-bottom: 12px; }
        .summary-details { flex-grow: 1; }
        .btn-confirm { background-color: #135D36; color: white; border: none; border-radius: 8px; padding: 10px 18px; font-size: 13px; font-weight: bold; cursor: pointer; width: 100%; text-align: center; margin-top: auto; }
        .btn-confirm:hover { background-color: #0F3B2C; }
        .btn-confirm.disabled { background-color: #aaa; cursor: not-allowed; }

        .message-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 8px 12px; border-radius: 5px; margin-bottom: 10px; font-size: 13px; }
        .message-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 8px 12px; border-radius: 5px; margin-bottom: 10px; font-size: 13px; }

        /* ====== MES RENDEZ-VOUS SECTION ====== */

        .mes-rdv-section {
            margin-top: 20px;
            padding-bottom: 30px;
        }

        .mes-rdv-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .mes-rdv-header h2 {
            font-size: 18px;
            color: #333;
        }

        .mes-rdv-count {
            background-color: #135D36;
            color: white;
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .rdv-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .rdv-card {
            background-color: white;
            border-radius: 12px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .rdv-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .rdv-card-top {
            padding: 15px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .rdv-date-badge {
            min-width: 50px;
            text-align: center;
            background-color: #f0f8f0;
            border-radius: 10px;
            padding: 8px 6px;
        }

        .rdv-date-badge .day {
            font-size: 22px;
            font-weight: bold;
            color: #135D36;
            line-height: 1;
        }

        .rdv-date-badge .month {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .rdv-info {
            flex: 1;
        }

        .rdv-service-name {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
        }

        .rdv-detail {
            font-size: 12px;
            color: #888;
            margin-bottom: 2px;
        }

        .rdv-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 6px;
        }

        .status-en-attente {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-confirme {
            background-color: #d4edda;
            color: #155724;
        }

        .status-annule {
            background-color: #f8d7da;
            color: #721c24;
        }

        .rdv-card-bottom {
            display: flex;
            border-top: 1px solid #f0f0f0;
        }

        .rdv-card-bottom a {
            flex: 1;
            text-align: center;
            padding: 10px;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .rdv-btn-modify {
            color: #135D36;
            border-right: 1px solid #f0f0f0;
        }

        .rdv-btn-modify:hover {
            background-color: #f0f8f0;
        }

        .rdv-btn-delete {
            color: #e74c3c;
        }

        .rdv-btn-delete:hover {
            background-color: #fde8e8;
        }

        .empty-rdv {
            grid-column: 1 / -1;
            text-align: center;
            padding: 30px;
            color: #aaa;
            font-size: 14px;
            background: white;
            border-radius: 12px;
        }

        .rdv-timeline-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .dot-en-attente { background-color: #f39c12; }
        .dot-confirme { background-color: #27ae60; }
        .dot-annule { background-color: #e74c3c; }

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
                    <li><img src="../../assets/icons/profil.svg" alt=""><a href="profil.php">Profile</a></li>
                    <li><img src="../../assets/icons/alertes.svg" alt=""><a href="evenements.php">Événements</a></li>
                    <li><img src="../../assets/icons/carte.svg" alt=""><a href="carte.php">Carte intelligente</a></li>
                    <li><img src="../../assets/icons/blog.svg" alt=""><a href="blog.php">Blog</a></li>
                    <li><img src="../../assets/icons/services.svg" alt=""><a href="services.php">Services en ligne</a></li>
                    <li class="active"><img src="../../assets/icons/rdv.svg" alt=""><a href="rendez-vous.php">Rendez-vous</a></li>
                </ul>
            </nav>
            <hr>
            <div class="nav-bottom">
                <ul>
                    <li><img src="../../assets/icons/parametres.svg" alt=""><a href="parametres.php">Paramètres</a></li>
                    <li><img src="../../assets/icons/deconnexion.svg" alt=""><a href="../../controllers/logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </aside>

        <main class="main-content">

            <header class="top-bar">
                <div class="search-bar">
                    <img src="../../assets/icons/search.svg" alt="">
                    <input type="text" placeholder="Rechercher">
                </div>
                <div class="user-info">
                    <span>Eliza Thorne</span>
                    <img src="../../assets/icons/avatar.svg" alt="">
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
                        <?php foreach ($categories as $cat): ?>
                            <a href="rendez-vous.php?categorie_id=<?php echo $cat['id']; ?>" class="service-card <?php echo ($selectedCategorieId == $cat['id']) ? 'selected' : ''; ?>">
                                <img src="../../assets/icons/<?php echo htmlspecialchars($cat['icone']); ?>" alt="">
                                <span><?php echo htmlspecialchars($cat['nom']); ?></span>
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
                    $monthName = $moisComplet[$month];
                    $prevMonth = $month - 1; $prevYear = $year;
                    if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
                    $nextMonth = $month + 1; $nextYear = $year;
                    if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
                    ?>
                    <div class="calendar-header">
                        <a href="rendez-vous.php?categorie_id=<?php echo $selectedCategorieId; ?>&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="calendar-prev">&lt;</a>
                        <span class="calendar-month"><?php echo $monthName . ' ' . $year; ?></span>
                        <a href="rendez-vous.php?categorie_id=<?php echo $selectedCategorieId; ?>&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="calendar-next">&gt;</a>
                    </div>
                    <table class="calendar-table">
                        <thead><tr><th>Lu</th><th>Ma</th><th>Me</th><th>Ju</th><th>Vr</th><th>Sa</th><th>So</th></tr></thead>
                        <tbody><tr>
                        <?php
                        $dayCount = 0;
                        for ($i = 1; $i < $firstDay; $i++) { echo "<td></td>"; $dayCount++; }
                        for ($day = 1; $day <= $daysInMonth; $day++) {
                            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                            $isSelected = ($selectedDate == $dateStr) ? 'selected-day' : '';
                            echo '<td><a href="rendez-vous.php?categorie_id=' . $selectedCategorieId . '&date=' . $dateStr . '&month=' . $month . '&year=' . $year . '" class="calendar-day ' . $isSelected . '">' . $day . '</a></td>';
                            $dayCount++;
                            if ($dayCount % 7 == 0 && $day != $daysInMonth) { echo "</tr><tr>"; }
                        }
                        while ($dayCount % 7 != 0) { echo "<td></td>"; $dayCount++; }
                        ?>
                        </tr></tbody>
                    </table>
                    <h4 class="time-title">Heure</h4>
                    <div class="time-slots">
                        <div class="time-row">
                            <?php for ($i = 0; $i < 3; $i++): ?>
                                <a href="rendez-vous.php?categorie_id=<?php echo $selectedCategorieId; ?>&date=<?php echo $selectedDate; ?>&heure=<?php echo $heures[$i]; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>" class="time-slot <?php echo ($selectedHeure == $heures[$i]) ? 'selected' : ''; ?>"><?php echo $heures[$i]; ?></a>
                            <?php endfor; ?>
                        </div>
                        <div class="time-row">
                            <?php for ($i = 3; $i < 6; $i++): ?>
                                <a href="rendez-vous.php?categorie_id=<?php echo $selectedCategorieId; ?>&date=<?php echo $selectedDate; ?>&heure=<?php echo $heures[$i]; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>" class="time-slot <?php echo ($selectedHeure == $heures[$i]) ? 'selected' : ''; ?>"><?php echo $heures[$i]; ?></a>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <div class="column column-summary">
                    <h3 class="summary-title">RÉSUMÉ DU RENDEZ-VOUS</h3>
                    <hr>
                    <div class="summary-details">
                        <p class="summary-label">Service:</p>
                        <p class="summary-value"><?php echo !empty($selectedCategorieName) ? htmlspecialchars($selectedCategorieName) : '---'; ?></p>
                        <p class="summary-label">Date:</p>
                        <p class="summary-value">
                            <?php
                            if (!empty($selectedDate)) {
                                $ts = strtotime($selectedDate);
                                echo $joursSemaine[date('w', $ts)] . ', ' . date('d', $ts) . ' ' . $moisComplet[date('n', $ts)];
                            } else { echo '---'; }
                            ?>
                        </p>
                        <p class="summary-label">Heure:</p>
                        <p class="summary-value"><?php echo !empty($selectedHeure) ? htmlspecialchars($selectedHeure) : '---'; ?></p>
                    </div>
                    <?php if ($step == 3): ?>
                        <form id="rdvForm" action="/smart-municipality/controllers/RendezVousController.php" method="POST">
                            <input type="hidden" name="action" value="create">
                            <input type="hidden" name="categorie_id" value="<?php echo htmlspecialchars($selectedCategorieId); ?>">
                            <input type="hidden" name="date_rdv" value="<?php echo htmlspecialchars($selectedDate); ?>">
                            <input type="hidden" name="heure" value="<?php echo htmlspecialchars($selectedHeure); ?>">
                            <button type="button" class="btn-confirm" onclick="confirmRdv()">&#10004; Confirmer le rendez-vous</button>
                        </form>
                    <?php else: ?>
                        <button class="btn-confirm disabled" disabled>&#10004; Confirmer le rendez-vous</button>
                    <?php endif; ?>
                </div>

            </div>

            <!-- ====== MES RENDEZ-VOUS ====== -->
            <div class="mes-rdv-section">

                <div class="mes-rdv-header">
                    <h2>&#128197; Mes Rendez-vous</h2>
                    <span class="mes-rdv-count"><?php echo count($mesRdv); ?> rendez-vous</span>
                </div>

                <div class="rdv-cards">

                    <?php if (empty($mesRdv)): ?>
                        <div class="empty-rdv">Vous n'avez aucun rendez-vous pour le moment.</div>
                    <?php else: ?>
                        <?php foreach ($mesRdv as $item):
                            $ts = strtotime($item['date_rdv']);
                            $dayNum = date('d', $ts);
                            $monthShort = $moisNoms[date('n', $ts)];
                            $dayName = $joursSemaine[date('w', $ts)];
                            $statusClass = 'status-' . str_replace('_', '-', $item['statut']);
                            $dotClass = 'dot-' . str_replace('_', '-', $item['statut']);
                            $statusLabel = str_replace('_', ' ', ucfirst($item['statut']));
                        ?>
                            <div class="rdv-card">
                                <div class="rdv-card-top">
                                    <div class="rdv-date-badge">
                                        <div class="day"><?php echo $dayNum; ?></div>
                                        <div class="month"><?php echo $monthShort; ?></div>
                                    </div>
                                    <div class="rdv-info">
                                        <div class="rdv-service-name"><?php echo htmlspecialchars($item['service_nom']); ?></div>
                                        <div class="rdv-detail"><?php echo $dayName; ?> &bull; <?php echo substr($item['heure'], 0, 5); ?></div>
                                        <span class="rdv-status <?php echo $statusClass; ?>">
                                            <span class="rdv-timeline-dot <?php echo $dotClass; ?>"></span>
                                            <?php echo $statusLabel; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="rdv-card-bottom">
                                    <?php if ($item['statut'] == 'en_attente'): ?>
                                        <a href="rendez-vous.php?categorie_id=<?php echo $item['categorie_id']; ?>&date=<?php echo $item['date_rdv']; ?>&heure=<?php echo substr($item['heure'], 0, 5); ?>&month=<?php echo date('n', $ts); ?>&year=<?php echo date('Y', $ts); ?>" class="rdv-btn-modify">&#9998; Modifier</a>
                                    <?php else: ?>
                                        <a href="#" class="rdv-btn-modify" style="color: #aaa; cursor: default;">&#9998; Modifier</a>
                                    <?php endif; ?>
                                    <a href="#" class="rdv-btn-delete" onclick="deleteMyRdv(<?php echo $item['id']; ?>)">&#10006; Supprimer</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>

            </div>

        </main>

    </div>

    <script>
        var slotTaken = <?php echo $slotTaken ? 'true' : 'false'; ?>;

        function confirmRdv() {
            if (typeof Swal === 'undefined') {
                if (slotTaken) {
                    alert("Ce créneau est déjà réservé ! Veuillez changer l'heure ou la date.");
                } else {
                    alert("Rendez-vous en attente");
                    document.getElementById('rdvForm').submit();
                }
                return;
            }

            if (slotTaken) {
                Swal.fire({
                    title: "Créneau indisponible !",
                    html: "Ce créneau est <b>déjà réservé</b> par un autre utilisateur.<br><br>Veuillez choisir une <b>autre heure</b> ou une <b>autre date</b>.",
                    icon: "error",
                    confirmButtonColor: "#e74c3c",
                    confirmButtonText: "Changer l'heure",
                    showCancelButton: true,
                    cancelButtonText: "Changer la date",
                    cancelButtonColor: "#f39c12"
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        window.location.href = "rendez-vous.php?categorie_id=<?php echo $selectedCategorieId; ?>&month=<?php echo isset($month) ? $month : date('n'); ?>&year=<?php echo isset($year) ? $year : date('Y'); ?>";
                    }
                });
            } else {
                Swal.fire({
                    title: "Rendez-vous en attente",
                    icon: "success",
                    draggable: true
                }).then(() => {
                    document.getElementById('rdvForm').submit();
                });
            }
        }

        function deleteMyRdv(id) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: "Supprimer ce rendez-vous ?",
                    text: "Cette action est irréversible",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#e74c3c",
                    cancelButtonColor: "#aaa",
                    confirmButtonText: "Oui, supprimer",
                    cancelButtonText: "Annuler"
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: "Rendez-vous supprimé !",
                            icon: "success",
                            draggable: true
                        }).then(() => {
                            window.location.href = "/smart-municipality/controllers/RendezVousController.php?action=delete&id=" + id + "&from=front";
                        });
                    }
                });
            } else {
                if (confirm("Supprimer ce rendez-vous ?")) {
                    window.location.href = "/smart-municipality/controllers/RendezVousController.php?action=delete&id=" + id + "&from=front";
                }
            }
        }
    </script>

</body>
</html>