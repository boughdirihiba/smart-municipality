<?php
// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "models/Document.php";
require_once "config/database.php";

// Gestion des messages de notification
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Connexion directe à la base
$database = new Database();
$db = $database->connect();

// Récupérer les statistiques
$sqlTotal = "SELECT COUNT(*) as total FROM demandes";
$stmtTotal = $db->prepare($sqlTotal);
$stmtTotal->execute();
$total_demandes = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

// Récupérer les services stats
$sqlServices = "SELECT type_service, COUNT(*) as nombre FROM demandes GROUP BY type_service ORDER BY nombre DESC";
$stmtServices = $db->prepare($sqlServices);
$stmtServices->execute();
$services_stats = $stmtServices->fetchAll(PDO::FETCH_ASSOC);

// Top 3 services
$top_services = array_slice($services_stats, 0, 3);

// Demandes par mois (6 derniers mois)
$sqlMois = "SELECT DATE_FORMAT(date_creation, '%Y-%m') as mois, COUNT(*) as nombre 
            FROM demandes 
            WHERE date_creation >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(date_creation, '%Y-%m')
            ORDER BY mois DESC";
$stmtMois = $db->prepare($sqlMois);
$stmtMois->execute();
$demandes_mois = $stmtMois->fetchAll(PDO::FETCH_ASSOC);

// Récupérer toutes les demandes pour le modal
$sqlDemandes = "SELECT id, nom, user_id FROM demandes ORDER BY date_creation DESC";
$stmtDemandes = $db->prepare($sqlDemandes);
$stmtDemandes->execute();
$all_demandes = $stmtDemandes->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les documents
$sqlDocuments = "SELECT d.id, d.nom_fichier, d.demande_id, dem.nom as citoyen_nom 
                 FROM documents d 
                 JOIN demandes dem ON d.demande_id = dem.id 
                 ORDER BY d.uploaded_at DESC LIMIT 50";
$stmtDocuments = $db->prepare($sqlDocuments);
$stmtDocuments->execute();
$all_documents = $stmtDocuments->fetchAll(PDO::FETCH_ASSOC);

// Dernières demandes
$sqlLast = "SELECT * FROM demandes ORDER BY date_creation DESC LIMIT 10";
$stmtLast = $db->prepare($sqlLast);
$stmtLast->execute();
$last_demandes = $stmtLast->fetchAll(PDO::FETCH_ASSOC);

// Pour chaque demande, récupérer ses fichiers
foreach($last_demandes as &$demande) {
    $sqlDocs = "SELECT * FROM documents WHERE demande_id = :demande_id ORDER BY uploaded_at DESC";
    $stmtDocs = $db->prepare($sqlDocs);
    $stmtDocs->bindParam(":demande_id", $demande['id']);
    $stmtDocs->execute();
    $demande['fichiers'] = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);
    $demande['fichiers_count'] = count($demande['fichiers']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality | Administration Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2f7 100%);
            color: #1a2c3e;
            min-height: 100vh;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ========== MODE SOMBRE PREMIUM ========== */
        body.dark-mode {
            background: linear-gradient(135deg, #0f172a 0%, #1a1f36 100%);
            color: #e2e8f0;
        }

        body.dark-mode .glass-card,
        body.dark-mode .glass-header,
        body.dark-mode .glass-table {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border-color: rgba(71, 85, 105, 0.5);
        }

        body.dark-mode .stat-card {
            background: linear-gradient(135deg, #1e293b, #1a2332);
            border-color: #334155;
        }

        body.dark-mode .nav-item {
            color: rgba(255, 255, 255, 0.7);
        }

        body.dark-mode .nav-item:hover {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        body.dark-mode .modal-content {
            background: #1e293b;
            border: 1px solid #334155;
        }

        body.dark-mode .form-control {
            background: #334155;
            border-color: #475569;
            color: #e2e8f0;
        }

        body.dark-mode .status-pending {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }

        /* ========== EFFETS VERRE ========== */
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* ========== SIDEBAR PREMIUM ========== */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #052E16 0%, #064e3b 100%);
            position: fixed;
            height: 100vh;
            padding: 2rem 1.5rem;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            z-index: 100;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #10b981;
            border-radius: 10px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            transition: all 0.3s;
        }

        .logo-container img {
            max-width: 180px;
            height: auto;
            filter: brightness(0) invert(1);
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            padding: 0.875rem 1rem;
            margin: 0.5rem 0;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 0;
            height: 100%;
            background: rgba(16, 185, 129, 0.2);
            transition: width 0.3s ease;
            z-index: -1;
        }

        .nav-item:hover::before {
            width: 100%;
        }

        .nav-item i {
            width: 24px;
            font-size: 1.2rem;
            transition: transform 0.3s;
        }

        .nav-item:hover i {
            transform: translateX(4px);
        }

        /* ========== MAIN CONTENT ========== */
        .main {
            margin-left: 280px;
            padding: 2rem;
            transition: all 0.3s;
        }

        /* ========== HEADER PREMIUM ========== */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1.5rem 2rem;
            border-radius: 24px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            transition: all 0.3s ease;
        }

        .header h1 {
            background: linear-gradient(135deg, #052E16, #10b981);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 2rem;
            font-weight: 800;
        }

        /* ========== STATS CARDS PREMIUM ========== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #052E16, #10b981);
            transform: scaleX(0);
            transition: transform 0.3s;
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .stat-info h3 {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #052E16, #10b981);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #052E16, #10b981);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            transition: transform 0.3s;
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
        }

        /* ========== BOUTONS PREMIUM ========== */
        .btn-premium {
            background: linear-gradient(135deg, #052E16, #10b981);
            border: none;
            padding: 10px 24px;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn-premium::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-premium:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(5, 46, 22, 0.3);
        }

        /* ========== TABLE PREMIUM ========== */
        .glass-table {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            overflow-x: auto;
        }

        .demandes-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .demandes-table th {
            padding: 12px 16px;
            color: #64748b;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .demandes-table td {
            padding: 16px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 12px;
            transition: all 0.3s;
        }

        .demandes-table tr:hover td {
            background: rgba(16, 185, 129, 0.05);
            transform: scale(1.01);
        }

        /* ========== MODAL PREMIUM ========== */
        .modal-content {
            background: white;
            border-radius: 32px;
            width: 550px;
            max-width: 90%;
            max-height: 90%;
            overflow-y: auto;
            animation: modalSlideIn 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        /* ========== ANIMATIONS ========== */
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

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Style pour l'export PDF */
        .pdf-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #052E16, #064e3b);
            color: white;
            border-radius: 12px;
        }

        .pdf-header h1 {
            font-size: 28px;
            margin: 0;
            font-weight: 700;
        }

        .pdf-header p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main {
                margin-left: 0;
                padding: 1rem;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- NOTIFICATIONS FLOTTANTES -->
    <?php if($success_message): ?>
    <div class="notification success animate-fadeInUp">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
    </div>
    <script>setTimeout(() => document.querySelector('.notification')?.remove(), 4000);</script>
    <?php endif; ?>

    <!-- SIDEBAR AVEC LOGO -->
    <div class="sidebar">
        <div class="logo-container">
            <img src="assets/images/logo.png" alt="Smart Municipality Logo" onerror="this.src='https://via.placeholder.com/180x60?text=Smart+Municipality'">
        </div>
        <nav>
            <a href="#" class="nav-item"><i class="fas fa-chart-line"></i> Tableau de bord</a>
            <a href="#" class="nav-item"><i class="fas fa-users"></i> Citoyens</a>
            <a href="#" class="nav-item"><i class="fas fa-calendar-alt"></i> Événements</a>
            <a href="#" class="nav-item"><i class="fas fa-brain"></i> Carte intelligente</a>
            <a href="#" class="nav-item"><i class="fas fa-newspaper"></i> Publications</a>
            <a href="index.php?action=list_services" class="nav-item"><i class="fas fa-concierge-bell"></i> Services</a>
            <a href="#" class="nav-item"><i class="fas fa-calendar-check"></i> Rendez-vous</a>
            <a href="#" class="nav-item"><i class="fas fa-chart-pie"></i> Statistiques</a>
        </nav>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main">
        <div class="header">
            <div>
                <h1><i class="fas fa-chart-line"></i> Tableau de bord</h1>
                <p style="color: #64748b; margin-top: 0.5rem;">Bienvenue dans l'espace d'administration</p>
            </div>
            <div style="display: flex; gap: 16px;">
                <button id="darkModeToggle" class="btn-premium">
                    <i class="fas fa-moon"></i> Mode sombre
                </button>
                <button class="btn-premium" onclick="openNotifyModal()">
                    <i class="fas fa-bell"></i> Notification
                </button>
                <a href="index.php?action=create_service" class="btn-premium">
                    <i class="fas fa-plus"></i> Nouveau service
                </a>
            </div>
        </div>

        <!-- STATS CARDS -->
        <div class="stats-grid">
            <div class="stat-card animate-fadeInUp" style="animation-delay: 0.1s">
                <div class="stat-info">
                    <h3><?php echo $total_demandes; ?></h3>
                    <p>Demandes totales</p>
                </div>
                <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
            </div>
            <div class="stat-card animate-fadeInUp" style="animation-delay: 0.2s">
                <div class="stat-info">
                    <h3><?php echo !empty($top_services[0]) ? $top_services[0]['nombre'] : 0; ?></h3>
                    <p>Service le plus demandé</p>
                </div>
                <div class="stat-icon"><i class="fas fa-trophy"></i></div>
            </div>
            <div class="stat-card animate-fadeInUp" style="animation-delay: 0.3s">
                <div class="stat-info">
                    <h3><?php echo !empty($demandes_mois) ? $demandes_mois[0]['nombre'] : 0; ?></h3>
                    <p>Demandes ce mois</p>
                </div>
                <div class="stat-icon"><i class="fas fa-calendar-week"></i></div>
            </div>
            <div class="stat-card animate-fadeInUp" style="animation-delay: 0.4s">
                <div class="stat-info">
                    <h3><?php echo count($services_stats); ?></h3>
                    <p>Services actifs</p>
                </div>
                <div class="stat-icon"><i class="fas fa-cubes"></i></div>
            </div>
        </div>

        <!-- GRAPHIQUES AVEC CHART.JS -->
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; margin-bottom: 32px;">
            <div class="glass-card" style="padding: 1.5rem; border-radius: 24px;">
                <h3 style="margin-bottom: 1rem;"><i class="fas fa-chart-bar"></i> Évolution mensuelle</h3>
                <canvas id="monthlyChart" height="200"></canvas>
            </div>
            <div class="glass-card" style="padding: 1.5rem; border-radius: 24px;">
                <h3 style="margin-bottom: 1rem;"><i class="fas fa-chart-pie"></i> Répartition par service</h3>
                <canvas id="servicesChart" height="200"></canvas>
            </div>
        </div>

        <!-- TABLEAU DES DEMANDES -->
        <div class="glass-table animate-fadeInUp" style="animation-delay: 0.5s">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3><i class="fas fa-clock"></i> Dernières demandes</h3>
                <button id="exportPdfBtn" class="btn-premium">
                    <i class="fas fa-file-pdf"></i> Exporter PDF
                </button>
            </div>
            <div id="demandesTableContainer">
                <table class="demandes-table" id="demandesTable">
                    <thead>
                        <tr><th>ID</th><th>Citoyen</th><th>Service</th><th>Documents requis</th><th>Date</th><th>Statut</th><th>Fichiers</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($last_demandes as $demande): ?>
                        <tr>
                            <td>#<?php echo $demande['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($demande['nom']); ?></strong></td>
                            <td><?php echo htmlspecialchars($demande['type_service']); ?></td>
                            <td><?php echo substr(htmlspecialchars($demande['documents'] ?? ''), 0, 40); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($demande['date_creation'])); ?></td>
                            <td><span class="status-pending">En attente</span></td>
                            <td>
                                <?php if($demande['fichiers_count'] > 0): ?>
                                    <?php foreach($demande['fichiers'] as $doc): ?>
                                        <div style="display: flex; gap: 8px; margin-bottom: 4px;">
                                            <i class="fas fa-file"></i>
                                            <span><?php echo substr($doc['nom_fichier'], 0, 20); ?></span>
                                            <a href="index.php?action=download_document&id=<?php echo $doc['id']; ?>" style="color: #10b981;">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span style="color: #94a3b8;">Aucun fichier</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="text-align: center; margin-top: 32px;">
            <a href="index.php?action=manage">
                <button class="btn-premium"><i class="fas fa-arrow-left"></i> Retour au site</button>
            </a>
        </div>
    </div>

    <!-- MODAL NOTIFICATION -->
    <div id="notifyModal" class="modal" style="display: none; position: fixed; top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:10001;">
        <div class="modal-content">
            <div class="modal-header" style="padding: 20px 24px; border-bottom: 1px solid #e2e8f0;">
                <h3><i class="fas fa-bell"></i> Envoyer une notification</h3>
                <span class="modal-close" onclick="closeNotifyModal()" style="cursor:pointer;font-size:28px;">&times;</span>
            </div>
            <form action="send.php" method="POST">
                <div class="modal-body" style="padding: 24px;">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>Demande concernée</label>
                        <select name="demande_id" id="notify_demande_id" class="form-control" required>
                            <option value="">-- Sélectionnez --</option>
                            <?php foreach($all_demandes as $demande): ?>
                                <option value="<?php echo $demande['id']; ?>">#<?php echo $demande['id']; ?> - <?php echo htmlspecialchars($demande['nom']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>Document lié (optionnel)</label>
                        <select name="document_id" id="notify_document_id" class="form-control">
                            <option value="">-- Aucun --</option>
                            <?php foreach($all_documents as $document): ?>
                                <option value="<?php echo $document['id']; ?>">📄 <?php echo substr($document['nom_fichier'], 0, 40); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>Messages rapides</label>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            <button type="button" class="quick-msg" data-msg="Votre demande est en cours de traitement." style="padding:8px 12px;background:#f1f5f9;border:none;border-radius:20px;cursor:pointer;">📋 En cours</button>
                            <button type="button" class="quick-msg" data-msg="Félicitations ! Votre demande a été acceptée." style="padding:8px 12px;background:#f1f5f9;border:none;border-radius:20px;cursor:pointer;">✅ Acceptée</button>
                            <button type="button" class="quick-msg" data-msg="Votre demande a été refusée." style="padding:8px 12px;background:#f1f5f9;border:none;border-radius:20px;cursor:pointer;">❌ Refusée</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" id="notify_message" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 16px 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" class="btn-cancel" onclick="closeNotifyModal()" style="padding:10px 20px;background:#f1f5f9;border:none;border-radius:40px;">Annuler</button>
                    <button type="submit" class="btn-send" style="padding:10px 20px;background:linear-gradient(135deg,#052E16,#10b981);color:white;border:none;border-radius:40px;">Envoyer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // MODE SOMBRE
        const darkModeToggle = document.getElementById('darkModeToggle');
        darkModeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
            darkModeToggle.innerHTML = document.body.classList.contains('dark-mode') ? '<i class="fas fa-sun"></i> Mode clair' : '<i class="fas fa-moon"></i> Mode sombre';
        });
        if(localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i> Mode clair';
        }

        // CHART.JS GRAPHIQUES
        const monthlyData = <?php 
            $months = array_reverse($demandes_mois);
            $labels = array_map(function($m) { return date('M Y', strtotime($m['mois'] . '-01')); }, $months);
            $counts = array_column($months, 'nombre');
            echo json_encode(['labels' => $labels, 'counts' => $counts]);
        ?>;
        
        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: monthlyData.labels,
                datasets: [{
                    label: 'Demandes',
                    data: monthlyData.counts,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });

        const servicesData = <?php 
            $serviceLabels = array_column($services_stats, 'type_service');
            $serviceCounts = array_column($services_stats, 'nombre');
            echo json_encode(['labels' => $serviceLabels, 'counts' => $serviceCounts]);
        ?>;

        new Chart(document.getElementById('servicesChart'), {
            type: 'doughnut',
            data: {
                labels: servicesData.labels,
                datasets: [{
                    data: servicesData.counts,
                    backgroundColor: ['#052E16', '#0a4a22', '#10b981', '#34d399', '#6ee7b7'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // EXPORT PDF AVEC EN-TÊTE SMART MUNICIPALITY
        async function exportToPDF() {
            const btn = document.getElementById('exportPdfBtn');
            const originalText = btn.innerHTML;
            
            try {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';
                btn.disabled = true;
                
                // Cloner le tableau
                const element = document.getElementById('demandesTableContainer');
                const tableClone = element.cloneNode(true);
                
                // Créer un conteneur pour le PDF
                const pdfContainer = document.createElement('div');
                pdfContainer.style.padding = '20px';
                pdfContainer.style.backgroundColor = 'white';
                pdfContainer.style.width = '100%';
                pdfContainer.style.fontFamily = 'Arial, sans-serif';
                
                // Ajouter l'en-tête SMART MUNICIPALITY
                const pdfHeader = document.createElement('div');
                pdfHeader.style.textAlign = 'center';
                pdfHeader.style.marginBottom = '30px';
                pdfHeader.style.padding = '20px';
                pdfHeader.style.background = 'linear-gradient(135deg, #052E16, #064e3b)';
                pdfHeader.style.color = 'white';
                pdfHeader.style.borderRadius = '12px';
                pdfHeader.innerHTML = `
                    <h1 style="margin: 0; font-size: 28px; font-weight: 700;">🏛️ SMART MUNICIPALITY</h1>
                    <p style="margin: 10px 0 0; opacity: 0.9; font-size: 14px;">Administration moderne et innovante</p>
                    <p style="margin: 5px 0 0; opacity: 0.8; font-size: 12px;">Liste des dernières demandes</p>
                    <p style="margin: 10px 0 0; font-size: 11px; opacity: 0.7;">Généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</p>
                `;
                pdfContainer.appendChild(pdfHeader);
                
                // Nettoyer le tableau cloné (enlever les boutons d'action)
                const clonedTable = tableClone.querySelector('#demandesTable');
                if (clonedTable) {
                    // Supprimer les boutons d'action
                    clonedTable.querySelectorAll('.file-actions, .download, .delete, .btn-icon, .file-icon-btn, .action-buttons').forEach(el => {
                        if (el) el.remove();
                    });
                    
                    // Simplifier l'affichage des fichiers
                    clonedTable.querySelectorAll('.file-section .files-list').forEach(list => {
                        const files = list.querySelectorAll('.file-item');
                        if (files.length === 0) {
                            list.innerHTML = '<span style="color: #94a3b8;">Aucun fichier</span>';
                        } else {
                            files.forEach(file => {
                                const actions = file.querySelector('.file-actions');
                                if (actions) actions.remove();
                                
                                // Garder seulement le nom du fichier
                                const fileInfo = file.querySelector('.file-info');
                                if (fileInfo) {
                                    const newContent = document.createElement('span');
                                    newContent.innerHTML = fileInfo.innerHTML;
                                    file.innerHTML = '';
                                    file.appendChild(newContent);
                                }
                            });
                        }
                    });
                    
                    pdfContainer.appendChild(clonedTable);
                } else {
                    pdfContainer.appendChild(tableClone);
                }
                
                // Ajouter un pied de page
                const footer = document.createElement('div');
                footer.style.textAlign = 'center';
                footer.style.marginTop = '30px';
                footer.style.padding = '15px';
                footer.style.borderTop = '1px solid #e2e8f0';
                footer.style.fontSize = '10px';
                footer.style.color = '#94a3b8';
                footer.innerHTML = `
                    <p>Smart Municipality - Solutions numériques pour les citoyens</p>
                    <p>© ${new Date().getFullYear()} Tous droits réservés</p>
                `;
                pdfContainer.appendChild(footer);
                
                // Ajouter au body temporairement pour le rendu
                pdfContainer.style.position = 'absolute';
                pdfContainer.style.left = '-9999px';
                pdfContainer.style.top = '-9999px';
                document.body.appendChild(pdfContainer);
                
                // Générer le PDF
                const canvas = await html2canvas(pdfContainer, {
                    scale: 2,
                    backgroundColor: '#ffffff',
                    logging: false,
                    useCORS: true
                });
                
                document.body.removeChild(pdfContainer);
                
                const { jsPDF } = window.jspdf;
                const imgData = canvas.toDataURL('image/png');
                const imgWidth = 280;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                
                const pdf = new jsPDF({
                    orientation: 'landscape',
                    unit: 'mm',
                    format: 'a4'
                });
                
                pdf.addImage(imgData, 'PNG', 10, 10, imgWidth, imgHeight);
                pdf.save('smart_municipality_demandes.pdf');
                
                btn.innerHTML = originalText;
                btn.disabled = false;
                
                // Notification de succès
                const notif = document.createElement('div');
                notif.className = 'notification success';
                notif.innerHTML = '<i class="fas fa-check-circle"></i> PDF généré avec succès !';
                document.body.appendChild(notif);
                setTimeout(() => notif.remove(), 3000);
                
            } catch (error) {
                console.error('Erreur PDF:', error);
                btn.innerHTML = originalText;
                btn.disabled = false;
                
                const notif = document.createElement('div');
                notif.className = 'notification error';
                notif.innerHTML = '<i class="fas fa-exclamation-circle"></i> Erreur lors de la génération du PDF';
                document.body.appendChild(notif);
                setTimeout(() => notif.remove(), 3000);
            }
        }
        
        document.getElementById('exportPdfBtn').addEventListener('click', exportToPDF);

        // MODAL
        function openNotifyModal() {
            document.getElementById('notifyModal').style.display = 'flex';
        }
        function closeNotifyModal() {
            document.getElementById('notifyModal').style.display = 'none';
        }
        
        document.querySelectorAll('.quick-msg').forEach(btn => {
            btn.addEventListener('click', function() {
                const msg = this.dataset.msg;
                const textarea = document.getElementById('notify_message');
                textarea.value = textarea.value ? textarea.value + '\n\n' + msg : msg;
            });
        });
    </script>
</body>
</html>