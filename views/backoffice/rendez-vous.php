<?php
session_start();

require_once '../../config/database.php';
require_once '../../controllers/RendezVousController.php';

$db = new Database();
$conn = $db->getConnection();
$rdv = new RendezVous($conn);

$categories = RendezVousController::getAllCategories($rdv);
$allRdv = RendezVousController::readAll($rdv);

$filterService = $_GET['service'] ?? '';
$filterStatut = $_GET['statut'] ?? '';

$rdvList = $allRdv;
if (!empty($filterService) || !empty($filterStatut)) {
    $rdvList = array_filter($allRdv, function($r) use ($filterService, $filterStatut) {
        $match = true;
        if (!empty($filterService) && $r['service_nom'] != $filterService) $match = false;
        if (!empty($filterStatut) && $r['statut'] != $filterStatut) $match = false;
        return $match;
    });
}

$totalAll = count($allRdv);
$totalConfirme = count(array_filter($allRdv, function($r) { return $r['statut'] == 'confirme'; }));
$totalAttente = count(array_filter($allRdv, function($r) { return $r['statut'] == 'en_attente'; }));
$totalAnnule = count(array_filter($allRdv, function($r) { return $r['statut'] == 'annule'; }));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality - Gestion des Rendez-vous</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            background-color: #f5f5f5;
        }

        .page-wrapper {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 220px;
            min-width: 220px;
            background: linear-gradient(180deg, #135D36, #0F3B2C);
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px 15px;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }

        .sidebar-logo img {
            width: 35px;
            height: 35px;
        }

        .sidebar-logo h2 {
            font-size: 15px;
            line-height: 1.3;
        }

        .sidebar-logo span {
            font-size: 11px;
            color: #aaa;
        }

        .sidebar hr {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
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
            padding: 10px 10px;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 2px;
        }

        .nav-menu ul li:hover,
        .nav-bottom ul li:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-menu ul li.active {
            background: linear-gradient(135deg, #2FA084, #27ae60);
            border-radius: 10px;
        }

        .nav-menu ul li img,
        .nav-bottom ul li img {
            width: 18px;
            height: 18px;
        }

        .nav-menu ul li a,
        .nav-bottom ul li a {
            color: white;
            text-decoration: none;
            font-size: 13px;
        }

        .nav-menu {
            flex-grow: 1;
        }

        .sub-nav {
            list-style: none;
            margin-left: 26px;
            margin-top: 2px;
            margin-bottom: 4px;
        }

        .sub-nav li {
            padding: 6px 10px !important;
            font-size: 12px;
        }

        .sub-nav li:hover {
            background-color: rgba(255, 255, 255, 0.08) !important;
            border-radius: 6px;
        }

        .sub-nav li.sub-active {
            background: rgba(255, 255, 255, 0.14) !important;
            border-radius: 6px;
        }

        .sub-nav li a {
            font-size: 12px !important;
        }

        .main-content {
            flex: 1;
            padding: 20px 25px;
            overflow-y: auto;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .top-bar h1 {
            font-size: 22px;
            color: #333;
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-manage-cat {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            background: linear-gradient(135deg, #135D36, #2FA084);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            box-shadow: 0 3px 10px rgba(19, 93, 54, 0.2);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            border: none;
            cursor: pointer;
        }

        .btn-manage-cat:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 16px rgba(19, 93, 54, 0.3);
        }

        .btn-manage-cat:active {
            transform: translateY(0);
        }

        .btn-manage-cat-icon {
            width: 22px;
            height: 22px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            transition: transform 0.3s ease;
        }

        .btn-manage-cat:hover .btn-manage-cat-icon {
            transform: rotate(90deg);
        }

        .btn-manage-cat-arrow {
            font-size: 14px;
            opacity: 0.8;
            transition: transform 0.25s ease;
        }

        .btn-manage-cat:hover .btn-manage-cat-arrow {
            transform: translateX(3px);
            opacity: 1;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }

        .admin-info img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
        }

        .admin-info span {
            font-weight: bold;
        }

        .admin-info small {
            color: #888;
            font-size: 11px;
        }

        .stats {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color: white;
            border-radius: 12px;
            padding: 15px 20px;
            flex: 1;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 4px solid transparent;
        }

        .stat-card.total { border-left-color: #e74c3c; }
        .stat-card.attente { border-left-color: #f39c12; }
        .stat-card.encours { border-left-color: #3498db; }
        .stat-card.resolus { border-left-color: #27ae60; }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .stat-card.total .stat-icon { background-color: #fde8e8; color: #e74c3c; }
        .stat-card.attente .stat-icon { background-color: #fef5e7; color: #f39c12; }
        .stat-card.encours .stat-icon { background-color: #e8f4fd; color: #3498db; }
        .stat-card.resolus .stat-icon { background-color: #e8f8ef; color: #27ae60; }

        .stat-info .stat-number {
            font-size: 26px;
            font-weight: bold;
            color: #333;
        }

        .stat-info .stat-label {
            font-size: 12px;
            color: #888;
        }

        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 15px;
            align-items: center;
        }

        .filters select {
            padding: 7px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 12px;
            background: white;
        }

        .filters button {
            padding: 7px 18px;
            background-color: #135D36;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            cursor: pointer;
        }

        .filters a {
            font-size: 12px;
            color: #888;
            text-decoration: none;
        }

        .rdv-table-wrapper {
            background-color: white;
            border-radius: 12px;
            padding: 20px;
        }

        .rdv-table-wrapper h3 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #333;
        }

        .rdv-table {
            width: 100%;
            border-collapse: collapse;
        }

        .rdv-table thead th {
            padding: 10px 12px;
            text-align: left;
            font-size: 12px;
            color: #888;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }

        .rdv-table tbody td {
            padding: 10px 12px;
            font-size: 12px;
            border-bottom: 1px solid #f5f5f5;
            color: #333;
        }

        .rdv-table tbody tr:hover {
            background-color: #fafafa;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-confirme {
            background-color: #e8f8ef;
            color: #27ae60;
        }

        .badge-annule {
            background-color: #fde8e8;
            color: #e74c3c;
        }

        .badge-en-attente {
            background-color: #fef5e7;
            color: #f39c12;
        }

        .actions {
            display: flex;
            gap: 5px;
        }

        .actions a {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-approve {
            background-color: #e8f8ef;
            color: #27ae60;
        }

        .btn-approve:hover {
            background-color: #d4edda;
        }

        .btn-cancel {
            background-color: #fef5e7;
            color: #f39c12;
        }

        .btn-cancel:hover {
            background-color: #fde8c8;
        }

        .btn-delete {
            background-color: #fde8e8;
            color: #e74c3c;
        }

        .btn-delete:hover {
            background-color: #f8c0c0;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #888;
            font-size: 13px;
        }

        .message-success {
            background-color: #e8f8ef;
            color: #27ae60;
            border: 1px solid #c3e6cb;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .message-error {
            background-color: #fde8e8;
            color: #e74c3c;
            border: 1px solid #f5c6cb;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
        }

    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <div class="page-wrapper">

        <aside class="sidebar">

            <div class="sidebar-logo">
                <img src="<?php echo BASE_URL; ?>/../../assets/icons/logo.png" alt="Logo">
                <div>
                    <h2>Smart Ville</h2>
                    <span>Admin</span>
                </div>
            </div>

            <hr>

            <nav class="nav-menu">
                <ul>
                    <li>
                        <img src="<?php echo BASE_URL; ?>/../../assets/icons/profil.svg" alt="">
                        <a href="<?php echo BASE_URL; ?>/profil.php">Profile</a>
                    </li>
                    <li>
                        <img src="<?php echo BASE_URL; ?>/../../assets/icons/alertes.svg" alt="">
                        <a href="<?php echo BASE_URL; ?>/evenements.php">Événements</a>
                    </li>
                    <li>
                        <img src="<?php echo BASE_URL; ?>/../../assets/icons/carte.svg" alt="">
                        <a href="<?php echo BASE_URL; ?>/carte.php">Carte intelligente</a>
                    </li>
                    <li>
                        <img src="<?php echo BASE_URL; ?>/../../assets/icons/blog.svg" alt="">
                        <a href="<?php echo BASE_URL; ?>/blog.php">Blog</a>
                    </li>
                    <li>
                        <img src="<?php echo BASE_URL; ?>/../../assets/icons/services.svg" alt="">
                        <a href="<?php echo BASE_URL; ?>/services.php">Services en ligne</a>
                    </li>
                    <li class="active">
                        <img src="<?php echo BASE_URL; ?>/../../assets/icons/rdv.svg" alt="">
                        <a href="<?php echo BASE_URL; ?>/rendez-vous.php">Rendez-vous</a>
                    </li>
                </ul>
                <ul class="sub-nav">
                    <li class="sub-active"><a href="<?php echo BASE_URL; ?>/rendez-vous.php">&rsaquo; Liste RDV</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/categories.php">&rsaquo; Catégories</a></li>
                </ul>
            </nav>

            <hr>

            <div class="nav-bottom">
                <ul>
                    <li>
                        <img src="<?php echo BASE_URL; ?>/../../assets/icons/parametres.svg" alt="">
                        <a href="<?php echo BASE_URL; ?>/parametres.php">Paramètres</a>
                    </li>
                    <li>
                        <img src="<?php echo BASE_URL; ?>/../../assets/icons/deconnexion.svg" alt="">
                        <a href="<?php echo BASE_URL; ?>/../../controllers/logout.php">Déconnexion</a>
                    </li>
                </ul>
            </div>

        </aside>

        <main class="main-content">

            <header class="top-bar">
                <div class="top-bar-left">
                    <h1>Dashboard - Rendez-vous</h1>
                    <a href="<?php echo BASE_URL; ?>/categories.php" class="btn-manage-cat">
                        <span class="btn-manage-cat-icon">&#9881;</span>
                        <span>Gérer les catégories</span>
                        <span class="btn-manage-cat-arrow">&rarr;</span>
                    </a>
                </div>
                <div class="admin-info">
                    <div>
                        <span>Admin</span><br>
                        <small>Sarah B.</small>
                    </div>
                    <img src="<?php echo BASE_URL; ?>/../../assets/icons/avatar.svg" alt="">
                </div>
            </header>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="message-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="message-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="stats">
                <div class="stat-card total">
                    <div class="stat-icon">&#128203;</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $totalAll; ?></div>
                        <div class="stat-label">Total rendez-vous</div>
                    </div>
                </div>
                <div class="stat-card attente">
                    <div class="stat-icon">&#9200;</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $totalAttente; ?></div>
                        <div class="stat-label">En attente</div>
                    </div>
                </div>
                <div class="stat-card encours">
                    <div class="stat-icon">&#10004;</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $totalConfirme; ?></div>
                        <div class="stat-label">Confirmés</div>
                    </div>
                </div>
                <div class="stat-card resolus">
                    <div class="stat-icon">&#10006;</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $totalAnnule; ?></div>
                        <div class="stat-label">Annulés</div>
                    </div>
                </div>
            </div>

            <form method="GET" class="filters">
                <select name="service">
                    <option value="">Tous les services</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['nom']); ?>" <?php echo $filterService == $cat['nom'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['nom']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="statut">
                    <option value="">Tous les statuts</option>
                    <option value="confirme" <?php echo $filterStatut == 'confirme' ? 'selected' : ''; ?>>Confirmé</option>
                    <option value="en_attente" <?php echo $filterStatut == 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                    <option value="annule" <?php echo $filterStatut == 'annule' ? 'selected' : ''; ?>>Annulé</option>
                </select>
                <button type="submit">Filtrer</button>
                <a href="<?php echo BASE_URL; ?>/rendez-vous.php">Réinitialiser</a>
            </form>

            <div class="rdv-table-wrapper">

                <h3>Derniers Rendez-vous</h3>

                <?php if (empty($rdvList)): ?>
                    <div class="empty-state">Aucun rendez-vous trouvé.</div>
                <?php else: ?>
                    <table class="rdv-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Citoyen</th>
                                <th>Service</th>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rdvList as $item): ?>
                                <tr>
                                    <td>#<?php echo $item['id']; ?></td>
                                    <td><?php echo htmlspecialchars($item['user_prenom'] . ' ' . $item['user_nom']); ?></td>
                                    <td><?php echo htmlspecialchars($item['service_nom']); ?></td>
                                    <td><?php echo $item['date_rdv']; ?></td>
                                    <td><?php echo $item['heure']; ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = 'badge-' . str_replace('_', '-', $item['statut']);
                                        $statutLabel = str_replace('_', ' ', ucfirst($item['statut']));
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $statutLabel; ?></span>
                                    </td>
                                    <td class="actions">
                                        <?php if ($item['statut'] == 'en_attente'): ?>
                                            <a href="#" class="btn-approve" onclick="confirmRdv(<?php echo $item['id']; ?>)">Confirmer</a>
                                            <a href="#" class="btn-cancel" onclick="cancelRdv(<?php echo $item['id']; ?>)">Annuler</a>
                                        <?php endif; ?>
                                        <a href="#" class="btn-delete" onclick="deleteRdv(<?php echo $item['id']; ?>)">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            </div>

        </main>

    </div>

    <script>
        function confirmRdv(id) {
            Swal.fire({
                title: "Confirmer ce rendez-vous ?",
                text: "Le statut passera à confirmé",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#27ae60",
                cancelButtonColor: "#aaa",
                confirmButtonText: "Oui, confirmer",
                cancelButtonText: "Annuler"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Rendez-vous confirmé !",
                        icon: "success",
                        draggable: true
                    }).then(() => {
                        window.location.href="<?php echo BASE_URL; ?>/smart-municipality/controllers/RendezVousController.php?action=confirm&id=" + id;
                    });
                }
            });
        }

        function cancelRdv(id) {
            Swal.fire({
                title: "Annuler ce rendez-vous ?",
                text: "Le statut passera à annulé",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#f39c12",
                cancelButtonColor: "#aaa",
                confirmButtonText: "Oui, annuler",
                cancelButtonText: "Retour"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Rendez-vous annulé !",
                        icon: "success",
                        draggable: true
                    }).then(() => {
                        window.location.href="<?php echo BASE_URL; ?>/smart-municipality/controllers/RendezVousController.php?action=cancel&id=" + id;
                    });
                }
            });
        }

        function deleteRdv(id) {
            Swal.fire({
                title: "Supprimer définitivement ?",
                text: "Cette action est irréversible",
                icon: "error",
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
                        window.location.href="<?php echo BASE_URL; ?>/smart-municipality/controllers/RendezVousController.php?action=delete&id=" + id;
                    });
                }
            });
        }
    </script>

</body>
</html>