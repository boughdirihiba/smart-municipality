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

// Dernières demandes avec leurs fichiers
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
    <!-- Librairies pour export PDF -->
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
            background: #f5f7fb;
            color: #1e293b;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* ========== MODE SOMBRE ========== */
        body.dark-mode {
            background: #0f172a;
            color: #e2e8f0;
        }

        body.dark-mode .sidebar {
            background: #052E16;
        }

        body.dark-mode .header,
        body.dark-mode .stat-card,
        body.dark-mode .chart-box,
        body.dark-mode .last-demandes {
            background: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
        }

        body.dark-mode .stat-info h3,
        body.dark-mode .stat-info p {
            color: #e2e8f0;
        }

        body.dark-mode .demandes-table th {
            background: #334155;
            color: #e2e8f0;
        }

        body.dark-mode .demandes-table td {
            color: #cbd5e1;
        }

        body.dark-mode .demandes-table tr:hover td {
            background: #334155;
        }

        body.dark-mode .file-item {
            background: #334155;
        }

        body.dark-mode .admin-info {
            background: #334155;
        }

        body.dark-mode .modal-content {
            background: #1e293b;
            border-color: #334155;
        }

        body.dark-mode .quick-msg {
            background: #334155;
            color: #e2e8f0;
        }

        body.dark-mode .quick-msg:hover {
            background: #10b981;
            color: white;
        }

        body.dark-mode .btn-cancel {
            background: #334155;
            color: #e2e8f0;
        }

        body.dark-mode select,
        body.dark-mode textarea {
            background: #334155;
            border-color: #475569;
            color: #e2e8f0;
        }

        body.dark-mode option {
            background: #1e293b;
        }

        body.dark-mode .notification {
            background: #1e293b;
            color: #e2e8f0;
        }

        body.dark-mode .notification.success {
            background: #10b981;
            color: white;
        }

        body.dark-mode .notification.error {
            background: #ef4444;
            color: white;
        }

        body.dark-mode .sort-info {
            background: #334155;
            color: #e2e8f0;
        }

        body.dark-mode .btn-export-pdf {
            background: linear-gradient(135deg, #b91c1c, #991b1b);
        }

        /* Couleurs - Vert foncé */
        :root {
            --primary: #052E16;
            --primary-dark: #022c0f;
            --primary-light: #0a4a22;
            --primary-soft: #e8f3e8;
            --primary-gradient: linear-gradient(135deg, #052E16, #0a4a22);
            --primary-gradient-light: linear-gradient(135deg, #0a4a22, #166534);
            --accent: #22c55e;
        }

        /* Bouton mode sombre */
        .btn-darkmode {
            background: #f1f5f9;
            border: none;
            padding: 10px 18px;
            border-radius: 40px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #475569;
        }

        .btn-darkmode:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        body.dark-mode .btn-darkmode {
            background: #334155;
            color: #e2e8f0;
        }

        body.dark-mode .btn-darkmode:hover {
            background: #10b981;
            color: white;
        }

        /* Bouton export PDF */
        .btn-export-pdf {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-export-pdf:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .btn-export-pdf:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* NOTIFICATIONS FLOTTANTES */
        .notification {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 10000;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.875rem;
            animation: slideInRight 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notification.success {
            background: #10b981;
            color: white;
        }

        .notification.error {
            background: #ef4444;
            color: white;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* ========== SIDEBAR VERT FONCÉ #052E16 ========== */
        .sidebar {
            width: 280px;
            background: #052E16;
            position: fixed;
            height: 100vh;
            padding: 1.5rem 1rem;
            box-shadow: 8px 0 32px rgba(0, 0, 0, 0.08);
            z-index: 100;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            padding: 0.5rem;
            background: transparent;
        }

        .logo-container img {
            max-width: 180px;
            height: auto;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            padding: 0.75rem 1rem;
            margin: 0.5rem 0;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .nav-item i {
            width: 24px;
            font-size: 1.1rem;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(4px);
        }

        /* MAIN CONTENT */
        .main {
            margin-left: 280px;
            padding: 2rem;
            transition: all 0.3s;
        }

        /* HEADER */
        .header {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            transition: all 0.3s ease;
        }

        .header h1 {
            color: #1a1f36;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .header p {
            color: #64748b;
            margin-top: 0.25rem;
            font-size: 0.875rem;
        }

        .header-buttons {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        /* BOUTON ENVOYER NOTIFICATION */
        .btn-notify {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 40px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-notify:hover {
            background: var(--primary-gradient-light);
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(5, 46, 22, 0.25);
        }

        .btn-add-service {
            background: #1a1f36;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 40px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-add-service:hover {
            background: var(--primary-gradient);
            transform: translateY(-2px);
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #f1f5f9;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .admin-name {
            font-weight: 600;
            font-size: 0.875rem;
        }

        .admin-role {
            font-size: 0.75rem;
            color: #64748b;
        }

        /* STATS CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            border-color: #10b981;
        }

        .stat-info h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a1f36;
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-gradient);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        /* TWO COLUMNS */
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        .chart-box {
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .chart-box h3 {
            color: #1a1f36;
            margin-bottom: 1.5rem;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chart-box h3 i {
            color: var(--primary);
        }

        /* BAR CHART */
        .bar-chart {
            margin-top: 20px;
        }

        .bar-item {
            margin-bottom: 20px;
        }

        .bar-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.8rem;
            color: #475569;
            font-weight: 500;
        }

        .bar-bg {
            background: #e2e8f0;
            border-radius: 8px;
            height: 32px;
            overflow: hidden;
        }

        .bar-fill {
            background: var(--primary-gradient);
            height: 100%;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 12px;
            color: white;
            font-weight: 600;
            font-size: 0.75rem;
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
            border-bottom: 1px solid #e2e8f0;
        }

        .service-item:last-child {
            border-bottom: none;
        }

        .service-name {
            font-weight: 500;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .service-badge {
            width: 32px;
            height: 32px;
            background: #f0fdf4;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .service-count {
            background: #f1f5f9;
            color: #1e293b;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        /* TABLE */
        .last-demandes {
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            overflow-x: auto;
            transition: all 0.3s ease;
        }

        .last-demandes h3 {
            color: #1a1f36;
            margin-bottom: 0;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .last-demandes h3 i {
            color: var(--primary);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 15px;
        }

        .demandes-table {
            width: 100%;
            border-collapse: collapse;
        }

        .demandes-table th {
            text-align: left;
            padding: 12px 12px;
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .demandes-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
            vertical-align: top;
            transition: all 0.3s ease;
        }

        .demandes-table tr:hover td {
            background: #f8fafc;
        }

        /* FICHIERS */
        .file-section {
            min-width: 260px;
        }

        .files-list {
            max-height: 200px;
            overflow-y: auto;
        }

        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            padding: 8px 12px;
            margin-bottom: 8px;
            border-radius: 10px;
            transition: all 0.2s;
        }

        .file-item:hover {
            background: #f1f5f9;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
        }

        .file-icon {
            font-size: 1rem;
        }

        .file-icon.pdf { color: #ef4444; }
        .file-icon.image { color: #8b5cf6; }
        .file-icon.doc { color: #3b82f6; }
        .file-icon.default { color: #10b981; }

        .file-name {
            font-size: 0.75rem;
            font-weight: 500;
            color: #1e293b;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
        }

        .file-size {
            font-size: 0.65rem;
            color: #64748b;
        }

        .file-actions {
            display: flex;
            gap: 8px;
        }

        .file-actions a {
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.2s;
        }

        .file-actions .download { color: #10b981; }
        .file-actions .delete { color: #ef4444; }

        .file-actions a:hover {
            opacity: 0.7;
        }

        .empty-files {
            text-align: center;
            padding: 20px;
            color: #94a3b8;
            font-size: 0.75rem;
        }

        .btn-back {
            background: #1a1f36;
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 40px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background: var(--primary-gradient);
            transform: translateY(-2px);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        /* MODAL STYLES */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 10001;
            align-items: center;
            justify-content: center;
        }
        .modal.show {
            display: flex;
        }
        .modal-content {
            background: white;
            border-radius: 24px;
            width: 500px;
            max-width: 90%;
            max-height: 90%;
            overflow-y: auto;
            animation: modalFadeIn 0.3s ease;
            transition: all 0.3s ease;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
        }
        .modal-header h3 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }
        .modal-header h3 i {
            color: #10b981;
            margin-right: 10px;
        }
        .modal-close {
            font-size: 28px;
            cursor: pointer;
            color: #94a3b8;
            transition: color 0.2s;
        }
        .modal-close:hover {
            color: #ef4444;
        }
        .modal-body {
            padding: 24px;
        }
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 13px;
            color: #334155;
        }
        .form-group label i {
            color: #10b981;
            margin-right: 6px;
        }
        select, textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }
        select:focus, textarea:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        .quick-messages {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .quick-msg {
            background: #f1f5f9;
            border: none;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .quick-msg:hover {
            background: #10b981;
            color: white;
        }
        .btn-cancel, .btn-send {
            padding: 10px 24px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        .btn-cancel {
            background: #f1f5f9;
            color: #475569;
        }
        .btn-cancel:hover {
            background: #e2e8f0;
        }
        .btn-send {
            background: var(--primary-gradient);
            color: white;
        }
        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(5, 46, 22, 0.3);
        }

        /* Sort info */
        .sort-info {
            background: #e8f3e8;
            padding: 10px 20px;
            border-radius: 40px;
            margin-bottom: 28px;
            font-size: 13px;
            color: #052E16;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(5, 46, 22, 0.1);
        }

        body.dark-mode .sort-info {
            background: #334155;
            color: #e2e8f0;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .two-columns {
                grid-template-columns: 1fr;
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
            .header-buttons {
                flex-direction: column;
                width: 100%;
            }
            .btn-notify, .btn-add-service, .btn-darkmode {
                width: 100%;
                justify-content: center;
            }
            .table-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

    <!-- NOTIFICATIONS -->
    <?php if($success_message): ?>
    <div class="notification success">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
    </div>
    <script>setTimeout(() => document.querySelector('.notification')?.remove(), 4000);</script>
    <?php endif; ?>

    <?php if($error_message): ?>
    <div class="notification error">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
    </div>
    <script>setTimeout(() => document.querySelector('.notification')?.remove(), 4000);</script>
    <?php endif; ?>

    <!-- SIDEBAR VERT FONCÉ #052E16 -->
    <div class="sidebar">
        <div class="logo-container">
            <img src="assets/images/logo.png" alt="Smart Municipality Logo">
        </div>
        <a href="#" class="nav-item"><i class="fas fa-id-card"></i> Profil</a>
        <a href="#" class="nav-item"><i class="fas fa-calendar-alt"></i> Événements</a>
        <a href="#" class="nav-item"><i class="fas fa-brain"></i> Carte intelligente</a>
        <a href="#" class="nav-item"><i class="fas fa-newspaper"></i> Blog</a>
        <a href="index.php?action=list_services" class="nav-item"><i class="fas fa-concierge-bell"></i> Services en ligne</a>
        <a href="#" class="nav-item"><i class="fas fa-calendar-check"></i> Rendez-vous</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main">
        <div class="header">
            <div>
                <h1>Tableau de bord</h1>
                <p>Bienvenue dans l'interface d'administration</p>
            </div>
            <div class="header-buttons">
                <button id="darkModeToggle" class="btn-darkmode">
                    <i class="fas fa-moon"></i> <span id="darkModeText">Sombre</span>
                </button>
                <button class="btn-notify" onclick="openNotifyModal()">
                    <i class="fas fa-bell"></i> Envoyer notification
                </button>
                <a href="index.php?action=create_service" class="btn-add-service">
                    <i class="fas fa-plus-circle"></i> Nouveau service
                </a>
                <div class="admin-info">
                    <div class="admin-avatar"><i class="fas fa-user"></i></div>
                    <div>
                        <div class="admin-name">Admin Système</div>
                        <div class="admin-role">Administrateur</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STATISTIQUES -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $total_demandes; ?></h3>
                    <p>Demandes totales</p>
                </div>
                <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo !empty($top_services[0]) ? $top_services[0]['nombre'] : 0; ?></h3>
                    <p>Service le plus demandé</p>
                </div>
                <div class="stat-icon"><i class="fas fa-trophy"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo !empty($demandes_mois) ? $demandes_mois[0]['nombre'] : 0; ?></h3>
                    <p>Demandes ce mois</p>
                </div>
                <div class="stat-icon"><i class="fas fa-calendar"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo count($services_stats); ?></h3>
                    <p>Services actifs</p>
                </div>
                <div class="stat-icon"><i class="fas fa-cubes"></i></div>
            </div>
        </div>

        <!-- GRAPHIQUES -->
        <div class="two-columns">
            <div class="chart-box">
                <h3><i class="fas fa-chart-bar"></i> Répartition par service</h3>
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
                                    <?php if($percentage > 20): echo $service['nombre']; endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="chart-box">
                <h3><i class="fas fa-medal"></i> Top 3 des services</h3>
                <ul class="service-list">
                    <?php if(!empty($top_services)): $medals = ['🥇', '🥈', '🥉']; ?>
                        <?php foreach($top_services as $index => $service): ?>
                            <li class="service-item">
                                <span class="service-name">
                                    <span class="service-badge"><?php echo $medals[$index]; ?></span>
                                    <?php echo htmlspecialchars($service['type_service']); ?>
                                </span>
                                <span class="service-count"><?php echo $service['nombre']; ?> demandes</span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="service-item">Aucune donnée disponible</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="chart-box" style="margin-bottom: 32px;">
            <h3><i class="fas fa-chart-line"></i> Évolution des demandes (6 derniers mois)</h3>
            <div class="bar-chart">
                <?php if(!empty($demandes_mois)): 
                    $maxMonthCount = max(array_column($demandes_mois, 'nombre'));
                    foreach(array_reverse($demandes_mois) as $mois): 
                        $percentage = ($mois['nombre'] / $maxMonthCount) * 100;
                ?>
                    <div class="bar-item">
                        <div class="bar-label">
                            <span><?php echo date('F Y', strtotime($mois['mois'] . '-01')); ?></span>
                            <span><?php echo $mois['nombre']; ?> demande(s)</span>
                        </div>
                        <div class="bar-bg">
                            <div class="bar-fill" style="width: <?php echo $percentage; ?>%;">
                                <?php if($percentage > 20): echo $mois['nombre']; endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- TABLEAU DES DEMANDES AVEC BOUTON EXPORT PDF -->
        <div class="last-demandes">
            <div class="table-header">
                <h3><i class="fas fa-clock"></i> Dernières demandes</h3>
                <button id="exportPdfBtn" class="btn-export-pdf">
                    <i class="fas fa-file-pdf"></i> Exporter PDF
                </button>
            </div>
            <div id="demandesTableContainer">
                <table class="demandes-table" id="demandesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Citoyen</th>
                            <th>Service</th>
                            <th>Documents requis</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Fichiers</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($last_demandes)): ?>
                            <?php foreach($last_demandes as $demande): ?>
                                <tr>
                                    <td>#<?php echo $demande['id']; ?></td>
                                    <td><?php echo htmlspecialchars($demande['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($demande['type_service']); ?></td>
                                    <td style="max-width: 180px;"><?php echo htmlspecialchars(substr($demande['documents'] ?? '', 0, 40)) . (strlen($demande['documents'] ?? '') > 40 ? '...' : ''); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($demande['date_creation'])); ?></td>
                                    <td><span class="status-badge status-pending">En attente</span></td>
                                    <td class="file-section">
                                        <div class="files-list">
                                            <?php if($demande['fichiers_count'] > 0): ?>
                                                <?php foreach($demande['fichiers'] as $doc): 
                                                    $ext = pathinfo($doc['nom_fichier'], PATHINFO_EXTENSION);
                                                    $iconClass = 'default';
                                                    if($ext == 'pdf') $iconClass = 'pdf';
                                                    elseif(in_array($ext, ['jpg','jpeg','png','gif'])) $iconClass = 'image';
                                                    elseif(in_array($ext, ['doc','docx'])) $iconClass = 'doc';
                                                ?>
                                                    <div class="file-item">
                                                        <div class="file-info">
                                                            <i class="fas <?php echo $iconClass == 'pdf' ? 'fa-file-pdf' : ($iconClass == 'image' ? 'fa-file-image' : ($iconClass == 'doc' ? 'fa-file-word' : 'fa-file')); ?> file-icon <?php echo $iconClass; ?>"></i>
                                                            <span class="file-name"><?php echo htmlspecialchars(substr($doc['nom_fichier'], 0, 25)); ?></span>
                                                            <span class="file-size">(<?php echo round($doc['taille'] / 1024, 1); ?> KB)</span>
                                                        </div>
                                                        <div class="file-actions">
                                                            <a href="index.php?action=download_document&id=<?php echo $doc['id']; ?>" class="download"><i class="fas fa-download"></i></a>
                                                            <a href="index.php?action=delete_document&id=<?php echo $doc['id']; ?>" class="delete" onclick="return confirm('Supprimer ce document ?')"><i class="fas fa-trash-alt"></i></a>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="empty-files"><i class="fas fa-folder-open"></i> Aucun fichier</div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align:center; padding:40px;">Aucune demande trouvée</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="text-align: center; margin-top: 32px;">
            <a href="index.php?action=manage"><button class="btn-back"><i class="fas fa-arrow-left"></i> Retour au Front Office</button></a>
        </div>
    </div>

    <!-- MODAL POUR ENVOYER UNE NOTIFICATION -->
    <div id="notifyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-bell"></i> Envoyer une notification</h3>
                <span class="modal-close" onclick="closeNotifyModal()">&times;</span>
            </div>
            <form action="send.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fas fa-folder-open"></i> Demande concernée</label>
                        <select name="demande_id" id="notify_demande_id" class="form-control" required>
                            <option value="">-- Sélectionnez une demande --</option>
                            <?php foreach($all_demandes as $demande): ?>
                                <option value="<?php echo $demande['id']; ?>">
                                    #<?php echo $demande['id']; ?> - <?php echo htmlspecialchars($demande['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Messages rapides</label>
                        <div class="quick-messages">
                            <button type="button" class="quick-msg" data-msg="🔄 Votre demande est en cours de traitement. Nous vous tiendrons informé.">
                                <i class="fas fa-spinner"></i> En cours
                            </button>
                            <button type="button" class="quick-msg" data-msg="✅ Félicitations ! Votre demande a été acceptée. Vous recevrez une confirmation sous 48h.">
                                <i class="fas fa-check-circle"></i> Acceptée
                            </button>
                            <button type="button" class="quick-msg" data-msg="❌ Votre demande a été refusée. Veuillez contacter l'administration.">
                                <i class="fas fa-times-circle"></i> Refusée
                            </button>
                            <button type="button" class="quick-msg" data-msg="📄 Des documents sont manquants pour votre demande. Merci de les fournir rapidement.">
                                <i class="fas fa-file-alt"></i> Docs manquants
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Message personnalisé</label>
                        <textarea name="message" id="notify_message" rows="4" placeholder="Saisissez votre message ici..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeNotifyModal()">Annuler</button>
                    <button type="submit" class="btn-send">
                        <i class="fas fa-paper-plane"></i> Envoyer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ========== MODE SOMBRE ==========
        function initDarkMode() {
            const darkMode = localStorage.getItem('darkMode');
            const darkModeToggle = document.getElementById('darkModeToggle');
            const darkModeText = document.getElementById('darkModeText');
            
            if (darkMode === 'enabled') {
                document.body.classList.add('dark-mode');
                if (darkModeText) darkModeText.textContent = 'Clair';
                if (darkModeToggle) darkModeToggle.innerHTML = '<i class="fas fa-sun"></i> Clair';
            }
            
            if (darkModeToggle) {
                darkModeToggle.addEventListener('click', () => {
                    document.body.classList.toggle('dark-mode');
                    
                    if (document.body.classList.contains('dark-mode')) {
                        localStorage.setItem('darkMode', 'enabled');
                        darkModeToggle.innerHTML = '<i class="fas fa-sun"></i> Clair';
                        if (darkModeText) darkModeText.textContent = 'Clair';
                    } else {
                        localStorage.setItem('darkMode', 'disabled');
                        darkModeToggle.innerHTML = '<i class="fas fa-moon"></i> Sombre';
                        if (darkModeText) darkModeText.textContent = 'Sombre';
                    }
                });
            }
        }

        // ========== EXPORT PDF ==========
        async function exportToPDF() {
            const btn = document.getElementById('exportPdfBtn');
            const originalText = btn.innerHTML;
            
            try {
                // Désactiver le bouton et afficher chargement
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';
                btn.disabled = true;
                
                // Créer un élément temporaire pour l'export
                const element = document.getElementById('demandesTableContainer');
                const tableClone = element.cloneNode(true);
                
                // Créer un conteneur pour le PDF
                const pdfContainer = document.createElement('div');
                pdfContainer.style.padding = '20px';
                pdfContainer.style.backgroundColor = 'white';
                pdfContainer.style.width = '100%';
                pdfContainer.style.fontFamily = 'Arial, sans-serif';
                
                // Ajouter l'en-tête
                const header = document.createElement('div');
                header.style.textAlign = 'center';
                header.style.marginBottom = '20px';
                header.style.padding = '10px';
                header.style.borderBottom = '2px solid #052E16';
                header.innerHTML = `
                    <h2 style="color: #052E16; margin: 0;">Smart Municipality</h2>
                    <p style="color: #64748b; margin: 5px 0 0;">Liste des dernières demandes</p>
                    <p style="color: #94a3b8; font-size: 12px; margin-top: 5px;">Généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</p>
                `;
                pdfContainer.appendChild(header);
                
                // Nettoyer le tableau cloné
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
                footer.style.marginTop = '20px';
                footer.style.padding = '10px';
                footer.style.borderTop = '1px solid #e2e8f0';
                footer.style.fontSize = '10px';
                footer.style.color = '#94a3b8';
                footer.innerHTML = `Smart Municipality - Document généré automatiquement`;
                pdfContainer.appendChild(footer);
                
                // Ajouter le conteneur au body temporairement
                pdfContainer.style.position = 'absolute';
                pdfContainer.style.left = '-9999px';
                pdfContainer.style.top = '-9999px';
                document.body.appendChild(pdfContainer);
                
                // Utiliser html2canvas et jsPDF
                const canvas = await html2canvas(pdfContainer, {
                    scale: 2,
                    backgroundColor: '#ffffff',
                    logging: false,
                    useCORS: true
                });
                
                // Supprimer le conteneur temporaire
                document.body.removeChild(pdfContainer);
                
                // Créer le PDF
                const { jsPDF } = window.jspdf;
                const imgData = canvas.toDataURL('image/png');
                const imgWidth = 297; // A4 en mm en paysage
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                
                const pdf = new jsPDF({
                    orientation: 'landscape',
                    unit: 'mm',
                    format: 'a4'
                });
                
                pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);
                pdf.save('demandes_smart_municipality.pdf');
                
                // Réactiver le bouton
                btn.innerHTML = originalText;
                btn.disabled = false;
                
                // Notification de succès
                showNotification('success', 'PDF généré avec succès !');
                
            } catch (error) {
                console.error('Erreur lors de la génération du PDF:', error);
                btn.innerHTML = originalText;
                btn.disabled = false;
                showNotification('error', 'Erreur lors de la génération du PDF');
            }
        }

        function showNotification(type, message) {
            const notif = document.createElement('div');
            notif.className = `notification ${type}`;
            notif.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 4000);
        }

        function openNotifyModal() {
            document.getElementById('notifyModal').classList.add('show');
            document.getElementById('notify_demande_id').value = '';
            document.getElementById('notify_message').value = '';
        }
        
        function closeNotifyModal() {
            document.getElementById('notifyModal').classList.remove('show');
        }
        
        document.querySelectorAll('.quick-msg').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('notify_message').value = this.dataset.msg;
            });
        });
        
        document.getElementById('notifyModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeNotifyModal();
        });

        // Bouton export PDF
        const exportBtn = document.getElementById('exportPdfBtn');
        if (exportBtn) {
            exportBtn.addEventListener('click', exportToPDF);
        }

        // Initialiser le mode sombre
        initDarkMode();
    </script>
</body>
</html>