<?php
require_once "models/Document.php";
require_once "config/database.php";
require_once "controllers/ServiceController.php";
require_once "controllers/RatingController.php";

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer l'ID utilisateur depuis la session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

// Connexion directe à la base
$database = new Database();
$db = $database->connect();

// ==================== SYSTÈME DE TRI COMPLET ====================
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

$allowed_sort = ['nom', 'popularite', 'date'];
$sort_column = in_array($sort, $allowed_sort) ? $sort : 'date';
$order_direction = ($order === 'ASC' || $order === 'DESC') ? $order : 'DESC';

// Récupérer les services depuis la table services
$sql = "SELECT * FROM services ORDER BY id DESC";
$stmt = $db->prepare($sql);
$stmt->execute();
$allServices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filtrer les services uniques par nom et ajouter le compteur de demandes
$uniqueServices = [];
$seenNames = [];

foreach($allServices as $service) {
    $nomKey = strtolower(trim($service['nom']));
    if (!in_array($nomKey, $seenNames)) {
        $seenNames[] = $nomKey;
        
        // Compter les demandes pour ce service
        $sqlCount = "SELECT COUNT(*) as count FROM demandes WHERE type_service = :service_name";
        $stmtCount = $db->prepare($sqlCount);
        $stmtCount->bindParam(":service_name", $service['nom']);
        $stmtCount->execute();
        $count = $stmtCount->fetch(PDO::FETCH_ASSOC);
        
        $service['demandes_count'] = $count['count'];
        
        // Récupérer la moyenne des notes pour ce service
        $sqlRating = "SELECT AVG(rating) as average, COUNT(*) as rating_count FROM ratings WHERE service_id = :service_id";
        $stmtRating = $db->prepare($sqlRating);
        $stmtRating->bindParam(":service_id", $service['id']);
        $stmtRating->execute();
        $ratingData = $stmtRating->fetch(PDO::FETCH_ASSOC);
        $service['rating_avg'] = round($ratingData['average'] ?? 0, 1);
        $service['rating_count'] = $ratingData['rating_count'] ?? 0;
        
        $uniqueServices[] = $service;
    }
}
$allServices = $uniqueServices;

// Appliquer le tri
if($sort_column == 'nom') {
    if($order_direction == 'ASC') {
        usort($allServices, function($a, $b) {
            return strcoll(
                mb_strtolower(trim($a['nom']), 'UTF-8'), 
                mb_strtolower(trim($b['nom']), 'UTF-8')
            );
        });
    } else {
        usort($allServices, function($a, $b) {
            return strcoll(
                mb_strtolower(trim($b['nom']), 'UTF-8'), 
                mb_strtolower(trim($a['nom']), 'UTF-8')
            );
        });
    }
} elseif($sort_column == 'popularite') {
    if($order_direction == 'ASC') {
        usort($allServices, function($a, $b) {
            return $a['demandes_count'] - $b['demandes_count'];
        });
    } else {
        usort($allServices, function($a, $b) {
            return $b['demandes_count'] - $a['demandes_count'];
        });
    }
} else {
    if($order_direction == 'ASC') {
        usort($allServices, function($a, $b) {
            $dateA = strtotime($a['date_creation']);
            $dateB = strtotime($b['date_creation']);
            return $dateA - $dateB;
        });
    } else {
        usort($allServices, function($a, $b) {
            $dateA = strtotime($a['date_creation']);
            $dateB = strtotime($b['date_creation']);
            return $dateB - $dateA;
        });
    }
}

// Récupérer les demandes
$sql = "SELECT * FROM demandes ORDER BY date_creation DESC";
$stmt = $db->prepare($sql);
$stmt->execute();
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les fichiers
foreach($demandes as &$demande) {
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
<title>Smart Municipality - Plateforme moderne</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

    body.dark-mode {
        background: #0f172a;
        color: #e2e8f0;
    }

    body.dark-mode .navbar {
        background: #1e293b;
        border-bottom-color: #334155;
    }

    body.dark-mode .nav-links a {
        color: #94a3b8;
    }

    body.dark-mode .nav-links a:hover {
        color: #10b981;
        background: rgba(16, 185, 129, 0.1);
    }

    body.dark-mode .hero {
        background: linear-gradient(135deg, #0f172a, #1e293b);
    }

    body.dark-mode .service-card,
    body.dark-mode .demandes-section,
    body.dark-mode .documents-section,
    body.dark-mode .chart-box,
    body.dark-mode .tabs-container {
        background: #1e293b;
        border-color: #334155;
    }

    body.dark-mode .service-card h4,
    body.dark-mode .demandes-header h3 {
        color: #e2e8f0;
    }

    body.dark-mode .service-card p,
    body.dark-mode .demandes-table td,
    body.dark-mode .demandes-table th {
        color: #cbd5e1;
    }

    body.dark-mode .demandes-table th {
        background: #334155;
        color: #e2e8f0;
    }

    body.dark-mode .demandes-table tr:hover td {
        background: #334155;
    }

    body.dark-mode .sort-bar,
    body.dark-mode .sort-info {
        background: #334155;
        border-color: #475569;
        color: #e2e8f0;
    }

    body.dark-mode .sort-btn {
        color: #94a3b8;
    }

    body.dark-mode .sort-btn.active {
        background: #10b981;
        color: white;
    }

    body.dark-mode .card-icon {
        background: #0f172a;
    }

    body.dark-mode .file-row,
    body.dark-mode .drop-zone {
        background: #334155;
        border-color: #475569;
    }

    body.dark-mode .footer {
        background: linear-gradient(135deg, #0f172a, #1e293b);
    }

    body.dark-mode .notification-dropdown {
        background: #1e293b;
        border-color: #334155;
    }

    body.dark-mode .notification-item {
        border-bottom-color: #334155;
    }

    body.dark-mode .notification-item:hover {
        background: #334155;
    }

    body.dark-mode .notification-item.unread {
        background: #064e3b;
    }

    body.dark-mode .btn-backoffice {
        background: #10b981;
    }

    body.dark-mode .notification-header {
        background: #334155;
    }

    body.dark-mode .notification-header h4 {
        color: #e2e8f0;
    }

    body.dark-mode .empty-notifications {
        color: #94a3b8;
    }

    .notification-link {
        margin-left: 15px;
        color: #10b981;
        text-decoration: none;
        font-size: 11px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .notification-link:hover {
        text-decoration: underline;
        opacity: 0.8;
    }

    body.dark-mode .notification-link {
        color: #4ade80;
    }

    .notification-link i {
        font-size: 10px;
        margin-right: 3px;
    }

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

    :root {
        --primary: #052E16;
        --primary-dark: #022c0f;
        --primary-light: #0a4a22;
        --primary-soft: #e8f3e8;
        --primary-glow: rgba(5, 46, 22, 0.1);
        --primary-gradient: linear-gradient(135deg, #052E16, #0a4a22);
        --primary-gradient-light: linear-gradient(135deg, #0a4a22, #166534);
        --accent: #22c55e;
        --accent-soft: #dcfce7;
        --gray-bg: #f8fafc;
        --card-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        --card-hover-shadow: 0 20px 35px rgba(5, 46, 22, 0.12);
    }

    .navbar {
        background: rgba(255, 255, 255, 0.97);
        backdrop-filter: blur(10px);
        padding: 0 40px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        position: sticky;
        top: 0;
        z-index: 1000;
        border-bottom: 1px solid rgba(5, 46, 22, 0.1);
        transition: all 0.3s ease;
    }

    .nav-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1400px;
        margin: 0 auto;
        padding: 12px 0;
        gap: 20px;
        flex-wrap: wrap;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        flex-shrink: 0;
    }

    .logo-img {
        width: 55px;
        height: 55px;
        object-fit: contain;
        border-radius: 14px;
        transition: transform 0.3s ease;
    }

    .logo-img:hover {
        transform: scale(1.05);
    }

    .logo-text .smart {
        font-size: 22px;
        font-weight: 800;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: -0.5px;
    }

    .logo-text .municipality {
        font-size: 11px;
        font-weight: 500;
        color: #64748b;
        letter-spacing: 0.3px;
    }

    .nav-links {
        display: flex;
        gap: 6px;
        align-items: center;
        flex-wrap: wrap;
        flex: 1;
        justify-content: center;
    }

    .nav-links a {
        text-decoration: none;
        color: #475569;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 40px;
    }

    .nav-links a i {
        font-size: 15px;
        color: #94a3b8;
        transition: color 0.3s;
    }

    .nav-links a:hover {
        background: rgba(5, 46, 22, 0.06);
        color: var(--primary);
    }

    .nav-links a:hover i {
        color: var(--primary);
    }

    .nav-links a.active {
        background: rgba(5, 46, 22, 0.1);
        color: var(--primary);
    }

    .nav-links a.active i {
        color: var(--primary);
    }

    .nav-right {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .notification-bell {
        position: relative;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        transition: all 0.3s;
    }

    .notification-bell:hover {
        background: rgba(5, 46, 22, 0.06);
    }

    .notification-bell i {
        font-size: 22px;
        color: #475569;
        transition: color 0.3s;
    }

    .notification-bell:hover i {
        color: var(--primary);
    }

    .notification-badge {
        position: absolute;
        top: 0;
        right: 0;
        background: #ef4444;
        color: white;
        font-size: 10px;
        font-weight: bold;
        padding: 2px 6px;
        border-radius: 50%;
        min-width: 18px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .notification-dropdown {
        position: absolute;
        top: 55px;
        right: 0;
        width: 420px;
        max-height: 500px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        display: none;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .notification-dropdown.show {
        display: block;
        animation: fadeInDown 0.2s ease;
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .notification-header {
        padding: 15px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
    }

    .notification-header h4 {
        font-size: 15px;
        font-weight: 700;
        margin: 0;
        color: #1e293b;
    }

    .notification-header h4 i {
        color: var(--primary);
        margin-right: 8px;
    }

    .mark-all-read {
        font-size: 12px;
        color: var(--primary);
        cursor: pointer;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s;
    }

    .mark-all-read:hover {
        text-decoration: underline;
        opacity: 0.8;
    }

    .notification-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .notification-item {
        padding: 14px 20px;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.2s;
        cursor: pointer;
    }

    .notification-item:hover {
        background: #f8fafc;
    }

    .notification-item.unread {
        background: #f0fdf4;
        border-left: 3px solid #10b981;
    }

    .notification-item.read {
        opacity: 0.75;
    }

    .notification-message {
        font-size: 13px;
        color: #1e293b;
        margin-bottom: 8px;
        line-height: 1.4;
    }

    .notification-date {
        font-size: 10px;
        color: #94a3b8;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .empty-notifications {
        padding: 40px 20px;
        text-align: center;
        color: #94a3b8;
    }

    .empty-notifications i {
        font-size: 45px;
        margin-bottom: 12px;
        opacity: 0.5;
    }

    .empty-notifications p {
        font-size: 13px;
    }

    .btn-backoffice {
        background: var(--primary-gradient);
        color: white !important;
        padding: 10px 24px !important;
        border-radius: 40px !important;
        box-shadow: 0 2px 8px rgba(5, 46, 22, 0.25);
    }

    .btn-backoffice:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(5, 46, 22, 0.35);
        background: var(--primary-gradient-light);
    }

    .btn-backoffice i {
        color: white !important;
    }

    .hero {
        background: var(--primary-gradient);
        padding: 55px 40px;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.06)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') repeat-x bottom;
        opacity: 0.35;
    }

    .hero h1 {
        font-size: 36px;
        font-weight: 800;
        margin-bottom: 12px;
        position: relative;
        letter-spacing: -0.5px;
    }

    .hero p {
        font-size: 16px;
        opacity: 0.92;
        position: relative;
    }

    .main-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px;
    }

    .tabs-container {
        background: white;
        border-radius: 60px;
        padding: 6px;
        margin-bottom: 35px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(5, 46, 22, 0.08);
        transition: all 0.3s ease;
    }

    .tabs {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .tab-btn {
        padding: 12px 28px;
        border: none;
        background: transparent;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        border-radius: 50px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .tab-btn i {
        font-size: 16px;
    }

    .tab-btn:hover {
        background: rgba(5, 46, 22, 0.06);
        color: var(--primary);
    }

    .tab-btn.active {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 12px rgba(5, 46, 22, 0.3);
    }

    .panel {
        display: none;
        animation: fadeIn 0.4s ease forwards;
    }

    .panel.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .services-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 30px;
    }

    .services-header-left h3 {
        font-size: 28px;
        font-weight: 800;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: -0.5px;
    }

    .services-header-left p {
        color: #64748b;
        font-size: 14px;
        margin-top: 5px;
    }

    .sort-bar {
        background: white;
        padding: 8px 20px;
        border-radius: 50px;
        border: 1px solid rgba(5, 46, 22, 0.1);
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
    }

    .sort-label {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    .sort-buttons {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .sort-group {
        display: flex;
        gap: 4px;
        background: #f1f5f9;
        border-radius: 40px;
        padding: 3px;
    }

    .sort-btn {
        padding: 6px 16px;
        border-radius: 40px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        background: transparent;
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: none;
        cursor: pointer;
    }

    .sort-btn i {
        font-size: 11px;
        transition: transform 0.2s;
    }

    .sort-btn:hover {
        background: rgba(5, 46, 22, 0.08);
        color: var(--primary);
    }

    .sort-btn.active {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 2px 8px rgba(5, 46, 22, 0.25);
    }

    .sort-info {
        background: var(--primary-soft);
        padding: 10px 20px;
        border-radius: 40px;
        margin-bottom: 28px;
        font-size: 13px;
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        border: 1px solid rgba(5, 46, 22, 0.1);
        transition: all 0.3s ease;
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }

    .service-card {
        background: white;
        border-radius: 28px;
        padding: 32px;
        transition: all 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        border: 1px solid rgba(5, 46, 22, 0.06);
        box-shadow: var(--card-shadow);
        position: relative;
        overflow: hidden;
    }

    .service-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--primary-gradient);
        transform: scaleX(0);
        transition: transform 0.4s ease;
        transform-origin: left;
    }

    .service-card:hover::before {
        transform: scaleX(1);
    }

    .service-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--card-hover-shadow);
        border-color: rgba(5, 46, 22, 0.15);
    }

    .card-icon {
        width: 70px;
        height: 70px;
        background: var(--primary-soft);
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
        transition: all 0.3s;
    }

    .service-card:hover .card-icon {
        transform: scale(1.05);
        background: var(--accent-soft);
    }

    .card-icon i {
        font-size: 34px;
        color: var(--primary);
    }

    .service-card h4 {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 12px;
        color: #0f172a;
        letter-spacing: -0.3px;
    }

    .service-card p {
        font-size: 14px;
        color: #64748b;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .service-stats {
        font-size: 12px;
        color: var(--primary);
        background: var(--primary-soft);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 40px;
        margin-bottom: 14px;
        font-weight: 600;
    }

    .service-meta {
        font-size: 12px;
        color: #94a3b8;
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* STYLES POUR LE RATING */
    .rating-container {
        margin: 15px 0;
        padding-top: 10px;
        border-top: 1px solid #eef2ff;
    }

    .rating-display {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }

    .stars-static {
        display: flex;
        gap: 3px;
    }

    .star-filled {
        color: #fbbf24;
    }

    .star-empty {
        color: #cbd5e1;
    }

    .rating-average {
        font-weight: 700;
        font-size: 15px;
        color: var(--primary);
    }

    .rating-count {
        font-size: 11px;
        color: #64748b;
    }

    .stars-input {
        display: flex;
        gap: 5px;
        margin: 10px 0;
        flex-direction: row-reverse;
        justify-content: flex-end;
    }

    .star-input {
        display: none;
    }

    .star-label {
        font-size: 28px;
        color: #cbd5e1;
        cursor: pointer;
        transition: all 0.2s;
    }

    .star-label:hover,
    .star-label:hover ~ .star-label,
    .star-input:checked ~ .star-label {
        color: #fbbf24;
    }

    .rating-comment {
        width: 100%;
        padding: 10px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-family: inherit;
        font-size: 12px;
        resize: vertical;
        margin-top: 10px;
        display: block;
    }

    .btn-rating {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 40px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 10px;
        font-size: 12px;
        transition: all 0.3s;
        display: block;
        width: 100%;
    }

    .btn-rating:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(5, 46, 22, 0.3);
    }

    .btn-delete-rating {
        background: #fee2e2;
        color: #dc2626;
        border: none;
        padding: 5px 12px;
        border-radius: 40px;
        font-size: 11px;
        cursor: pointer;
        margin-left: 10px;
        transition: all 0.2s;
    }

    .btn-delete-rating:hover {
        background: #fecaca;
    }

    .user-rating-info {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        font-size: 13px;
        color: var(--primary);
        background: var(--primary-soft);
        padding: 8px 15px;
        border-radius: 40px;
        margin-top: 10px;
    }

    .rating-list {
        margin-top: 15px;
        max-height: 200px;
        overflow-y: auto;
    }

    .rating-item {
        padding: 10px;
        border-bottom: 1px solid #f1f5f9;
    }

    .rating-item:last-child {
        border-bottom: none;
    }

    .rating-user {
        font-weight: 600;
        font-size: 12px;
    }

    .rating-stars-small {
        display: inline-flex;
        gap: 2px;
        margin-left: 8px;
    }

    .rating-stars-small i {
        font-size: 10px;
    }

    .rating-comment-text {
        font-size: 11px;
        color: #64748b;
        margin-top: 5px;
    }

    .rating-date {
        font-size: 9px;
        color: #94a3b8;
        float: right;
    }

    .loading-rating {
        text-align: center;
        padding: 10px;
        color: #94a3b8;
        font-size: 12px;
    }

    body.dark-mode .rating-container {
        border-top-color: #334155;
    }

    body.dark-mode .rating-comment {
        background: #334155;
        border-color: #475569;
        color: #e2e8f0;
    }

    body.dark-mode .rating-item {
        border-bottom-color: #334155;
    }

    body.dark-mode .user-rating-info {
        background: #064e3b;
        color: #4ade80;
    }

    .card-btn {
        background: transparent;
        border: 1.5px solid #e2e8f0;
        padding: 12px 24px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 14px;
        color: var(--primary);
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
    }

    .card-btn:hover {
        background: var(--primary-gradient);
        color: white;
        border-color: transparent;
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(5, 46, 22, 0.3);
    }

    .card-btn i {
        transition: transform 0.2s;
    }

    .card-btn:hover i {
        transform: translateX(5px);
    }

    .demandes-section {
        background: white;
        border-radius: 28px;
        padding: 32px;
        border: 1px solid rgba(5, 46, 22, 0.08);
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
    }

    .demandes-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .demandes-header h3 {
        font-size: 22px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .demandes-count {
        background: var(--primary-soft);
        color: var(--primary);
        padding: 6px 18px;
        border-radius: 40px;
        font-size: 13px;
        font-weight: 600;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    .demandes-table {
        width: 100%;
        border-collapse: collapse;
    }

    .demandes-table th {
        text-align: left;
        padding: 16px 12px;
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 12px;
        border-bottom: 1px solid #e2e8f0;
    }

    .demandes-table td {
        padding: 16px 12px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
        vertical-align: middle;
    }

    .demande-id {
        background: var(--primary-soft);
        color: var(--primary);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }

    .files-cell {
        min-width: 280px;
    }

    .file-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
        padding: 8px 12px;
        margin-bottom: 8px;
        border-radius: 14px;
        border: 1px solid #eef2ff;
        transition: all 0.3s ease;
    }

    .file-name {
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .file-actions-icons {
        display: flex;
        gap: 6px;
    }

    .file-icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.2s;
        font-size: 13px;
        cursor: pointer;
        border: none;
    }

    .edit-file { background: #fef3c7; color: #d97706; }
    .edit-file:hover { background: #fde68a; transform: scale(1.05); }
    .download-file { background: var(--primary-soft); color: var(--primary); }
    .download-file:hover { background: var(--accent-soft); transform: scale(1.05); }
    .delete-file { background: #fee2e2; color: #dc2626; }
    .delete-file:hover { background: #fecaca; transform: scale(1.05); }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.2s;
        font-size: 14px;
        border: none;
        cursor: pointer;
    }

    .btn-edit-icon { background: #fef3c7; color: #d97706; }
    .btn-edit-icon:hover { background: #fde68a; transform: scale(1.05); }
    .btn-delete-icon { background: #fee2e2; color: #dc2626; }
    .btn-delete-icon:hover { background: #fecaca; transform: scale(1.05); }
    .btn-doc-icon { background: var(--primary-soft); color: var(--primary); }
    .btn-doc-icon:hover { background: var(--accent-soft); transform: scale(1.05); }

    .empty-state {
        text-align: center;
        padding: 60px;
        color: #94a3b8;
    }
    .empty-state i { font-size: 55px; margin-bottom: 18px; opacity: 0.5; }

    .documents-section {
        background: white;
        border-radius: 28px;
        padding: 32px;
        border: 1px solid rgba(5, 46, 22, 0.08);
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
    }

    .footer {
        background: var(--primary-gradient);
        color: white;
        padding: 50px 40px 30px;
        margin-top: 60px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #22c55e, #4ade80, #22c55e);
    }

    .footer-content {
        max-width: 1400px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 40px;
        position: relative;
        z-index: 1;
    }

    .footer-section h4 {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 18px;
        color: #4ade80;
        letter-spacing: -0.2px;
    }

    .footer-section p, .footer-section a {
        color: #bbf7d0;
        font-size: 13px;
        text-decoration: none;
        display: block;
        margin-bottom: 10px;
        transition: all 0.2s;
        opacity: 0.85;
    }

    .footer-section a:hover {
        color: #86efac;
        opacity: 1;
        transform: translateX(5px);
    }

    .footer-bottom {
        text-align: center;
        padding-top: 30px;
        margin-top: 30px;
        border-top: 1px solid rgba(74, 222, 128, 0.2);
        font-size: 12px;
        color: #a7f3d0;
        position: relative;
        z-index: 1;
    }

    @media (max-width: 768px) {
        .navbar { padding: 0 20px; }
        .hero { padding: 40px 20px; }
        .hero h1 { font-size: 28px; }
        .main-container { padding: 20px; }
        .tab-btn { padding: 10px 20px; font-size: 13px; }
        .services-grid { grid-template-columns: 1fr; gap: 20px; }
        .services-header { flex-direction: column; align-items: flex-start; }
        .sort-bar { width: 100%; justify-content: center; flex-wrap: wrap; }
        .sort-buttons { flex-direction: column; align-items: center; width: 100%; }
        .sort-group { width: 100%; justify-content: center; }
        .footer { padding: 35px 20px 25px; }
        .nav-links { justify-content: flex-start; gap: 4px; }
        .nav-links a { padding: 6px 12px; font-size: 12px; }
        .notification-dropdown { width: 350px; right: -60px; }
    }
</style>
<link rel="stylesheet" href="assets/css/chatbot.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="index.php?action=manage" class="logo">
            <img src="assets/images/logo.png" alt="Smart Municipality" class="logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="logo-icon" style="display: none;">
                <i class="fas fa-city"></i>
            </div>
            <div class="logo-text">
                <div class="smart">Smart Municipality</div>
                <div class="municipality">Services en ligne</div>
            </div>
        </a>
        <div class="nav-links">
            <a href="index.php?action=profil"><i class="fas fa-user-circle"></i> Profil</a>
            <a href="index.php?action=evenements"><i class="fas fa-calendar-alt"></i> Événements</a>
            <a href="index.php?action=carte_intelligente"><i class="fas fa-map-marked-alt"></i> Carte</a>
            <a href="index.php?action=blog"><i class="fas fa-newspaper"></i> Blog</a>
            <a href="index.php?action=list_services" class="active"><i class="fas fa-concierge-bell"></i> Services</a>
            <a href="index.php?action=rendez_vous"><i class="fas fa-calendar-check"></i> Rendez-vous</a>
        </div>
        <div class="nav-right">
            <button id="darkModeToggle" class="btn-darkmode">
                <i class="fas fa-moon"></i> <span id="darkModeText">Sombre</span>
            </button>
            <div class="notification-bell" id="notificationBell">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h4><i class="fas fa-bell"></i> Notifications</h4>
                        <span class="mark-all-read" id="markAllRead">Tout marquer comme lu</span>
                    </div>
                    <div class="notification-list" id="notificationList">
                        <div class="empty-notifications">
                            <i class="fas fa-bell-slash"></i>
                            <p>Chargement...</p>
                        </div>
                    </div>
                </div>
            </div>
            <a href="index.php?action=dashboard" class="btn-backoffice"><i class="fas fa-chart-line"></i> Administration</a>
        </div>
    </div>
</nav>

<section class="hero">
    <h1>Bienvenue sur Smart Municipality</h1>
    <p>Votre plateforme digitale pour les démarches administratives</p>
</section>

<main class="main-container">
    <div class="tabs-container">
        <div class="tabs">
            <button class="tab-btn active" data-tab="services"><i class="fas fa-concierge-bell"></i> Services</button>
            <button class="tab-btn" data-tab="demandes"><i class="fas fa-list-alt"></i> Mes demandes <span style="background:#e2e8f0; color:#475569; padding:2px 8px; border-radius:20px; font-size:11px; margin-left:5px;"><?php echo count($demandes); ?></span></button>
            <button class="tab-btn" data-tab="documents"><i class="fas fa-file-alt"></i> Documents</button>
        </div>
    </div>

    <div class="panel active" id="panel-services">
        <div class="services-header">
            <div class="services-header-left">
                <h3>Nos services en ligne</h3>
                <p><?php echo count($allServices); ?> services disponibles pour faciliter vos démarches</p>
            </div>
            <div class="sort-bar">
                <span class="sort-label"><i class="fas fa-sort-amount-down"></i> TRIER PAR</span>
                <div class="sort-buttons">
                    <div class="sort-group">
                        <a href="?sort=nom&order=ASC" class="sort-btn <?php echo ($sort == 'nom' && $order == 'ASC') ? 'active' : ''; ?>">
                            <i class="fas fa-sort-alpha-down"></i> A→Z
                        </a>
                        <a href="?sort=nom&order=DESC" class="sort-btn <?php echo ($sort == 'nom' && $order == 'DESC') ? 'active' : ''; ?>">
                            <i class="fas fa-sort-alpha-up"></i> Z→A
                        </a>
                    </div>
                    <div class="sort-group">
                        <a href="?sort=popularite&order=DESC" class="sort-btn <?php echo ($sort == 'popularite' && $order == 'DESC') ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i> Plus populaire
                        </a>
                        <a href="?sort=popularite&order=ASC" class="sort-btn <?php echo ($sort == 'popularite' && $order == 'ASC') ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i> Moins populaire
                        </a>
                    </div>
                    <div class="sort-group">
                        <a href="?sort=date&order=DESC" class="sort-btn <?php echo ($sort == 'date' && $order == 'DESC') ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-alt"></i> Plus récent
                        </a>
                        <a href="?sort=date&order=ASC" class="sort-btn <?php echo ($sort == 'date' && $order == 'ASC') ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-alt"></i> Plus ancien
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="sort-info">
            <i class="fas fa-chart-simple"></i> Tri actuel : <strong>
                <?php 
                if($sort == 'nom') {
                    echo ($order == 'ASC') ? 'Nom (A → Z)' : 'Nom (Z → A)';
                } elseif($sort == 'popularite') {
                    echo ($order == 'DESC') ? 'Plus populaire d\'abord' : 'Moins populaire d\'abord';
                } else {
                    echo ($order == 'DESC') ? 'Plus récent d\'abord' : 'Plus ancien d\'abord';
                }
                ?>
            </strong> 
            <span style="background: white; padding: 4px 14px; border-radius: 40px; font-size: 11px; font-weight: 600;">
                <i class="fas fa-layer-group"></i> <?php echo count($allServices); ?> service(s)
            </span>
        </div>

        <div class="services-grid">
            <?php if (!empty($allServices) && count($allServices) > 0): ?>
                <?php foreach ($allServices as $service): ?>
                    <div class="service-card" data-service-id="<?php echo $service['id']; ?>">
                        <div class="card-icon">
                            <i class="<?php echo htmlspecialchars($service['icone']); ?>"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($service['nom']); ?></h4>
                        <p><?php echo htmlspecialchars($service['description']); ?></p>
                        <?php if(isset($service['demandes_count']) && $service['demandes_count'] > 0): ?>
                            <div class="service-stats">
                                <i class="fas fa-users"></i> <?php echo $service['demandes_count']; ?> demande(s)
                            </div>
                        <?php endif; ?>
                        <div class="service-meta">
                            <i class="fas fa-calendar-alt"></i> Créé le <?php echo date('d/m/Y', strtotime($service['date_creation'])); ?>
                        </div>
                        
                        <div class="rating-container" id="rating-<?php echo $service['id']; ?>">
                            <div class="rating-display">
                                <div class="stars-static" id="stars-static-<?php echo $service['id']; ?>">
                                    <?php 
                                    $fullStars = floor($service['rating_avg']);
                                    $halfStar = ($service['rating_avg'] - $fullStars) >= 0.5;
                                    for($i = 1; $i <= 5; $i++):
                                        if($i <= $fullStars):
                                    ?>
                                        <i class="fas fa-star star-filled"></i>
                                    <?php elseif($i == $fullStars + 1 && $halfStar): ?>
                                        <i class="fas fa-star-half-alt star-filled"></i>
                                    <?php else: ?>
                                        <i class="far fa-star star-empty"></i>
                                    <?php endif; endfor; ?>
                                </div>
                                <span class="rating-average"><?php echo $service['rating_avg']; ?>/5</span>
                                <span class="rating-count">(<?php echo $service['rating_count']; ?> avis)</span>
                            </div>
                            <div id="rating-input-<?php echo $service['id']; ?>">
                                <div class="loading-rating">Chargement...</div>
                            </div>
                            <div id="rating-list-<?php echo $service['id']; ?>" class="rating-list"></div>
                        </div>
                        
                        <a href="index.php?action=create&service=<?php echo urlencode($service['nom']); ?>" class="card-btn">
                            Accéder au service <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <i class="fas fa-folder-open"></i>
                    <p>Aucun service disponible pour le moment</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel" id="panel-demandes">
        <div class="demandes-section">
            <div class="demandes-header">
                <h3><i class="fas fa-list-alt"></i> Mes demandes soumises</h3>
                <span class="demandes-count"><i class="fas fa-file-alt"></i> <?php echo count($demandes); ?> demande(s)</span>
            </div>
            <?php if (!empty($demandes)): ?>
                <div class="table-wrapper">
                    <table class="demandes-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Service</th>
                                <th>Documents requis</th>
                                <th>Date</th>
                                <th>Fichiers</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($demandes as $demande): ?>
                                <tr data-demande-id="<?php echo $demande['id']; ?>">
                                    <td><span class="demande-id">#<?php echo htmlspecialchars($demande['id']); ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($demande['nom']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($demande['type_service']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($demande['documents'] ?? '', 0, 35)); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($demande['date_creation'])); ?></td>
                                    <td class="files-cell">
                                        <?php if ($demande['fichiers_count'] > 0): ?>
                                            <?php foreach ($demande['fichiers'] as $fichier): ?>
                                                <div class="file-row">
                                                    <span class="file-name">
                                                        <i class="fas fa-file" style="color: var(--primary);"></i>
                                                        <?php echo htmlspecialchars(substr($fichier['nom_fichier'], 0, 25)); ?>
                                                        <small>(<?php echo round($fichier['taille']/1024, 1); ?> KB)</small>
                                                    </span>
                                                    <div class="file-actions-icons">
                                                        <a href="index.php?action=edit_document&id=<?php echo $fichier['id']; ?>" class="file-icon-btn edit-file"><i class="fas fa-edit"></i></a>
                                                        <a href="index.php?action=download_document&id=<?php echo $fichier['id']; ?>" class="file-icon-btn download-file"><i class="fas fa-download"></i></a>
                                                        <a href="index.php?action=delete_document&id=<?php echo $fichier['id']; ?>" class="file-icon-btn delete-file" onclick="return confirm('Supprimer ce document ?')"><i class="fas fa-trash-alt"></i></a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="no-files"><i class="fas fa-folder-open"></i> Aucun fichier</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="index.php?action=edit&id=<?php echo $demande['id']; ?>" class="btn-icon btn-edit-icon"><i class="fas fa-edit"></i></a>
                                        <a href="index.php?action=delete&id=<?php echo $demande['id']; ?>" class="btn-icon btn-delete-icon" onclick="return confirm('Supprimer cette demande ?')"><i class="fas fa-trash-alt"></i></a>
                                        <button class="btn-icon btn-doc-icon" onclick="switchToDocuments(<?php echo $demande['id']; ?>)"><i class="fas fa-file-upload"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Aucune demande pour le moment</p>
                    <p style="font-size:12px;">Cliquez sur l'onglet "Services" pour créer votre première demande</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel" id="panel-documents">
        <div class="documents-section">
            <div class="documents-header" style="margin-bottom:25px; padding-bottom:15px; border-bottom:1px solid #eef2ff;">
                <h3 style="font-size:20px; font-weight:700; background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><i class="fas fa-cloud-upload-alt"></i> Gestion des documents</h3>
                <p style="color:#64748b; font-size:13px;">Sélectionnez une demande pour gérer ses documents</p>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1.5fr; gap:30px;">
                <div style="background:#f8fafc; border-radius:24px; padding:24px;">
                    <h4 style="margin-bottom:18px; font-size:16px;"><i class="fas fa-list-check"></i> Documents nécessaires</h4>
                    <div style="display:flex; flex-direction:column; gap:14px;">
                        <div style="display:flex; align-items:center; gap:15px; padding:14px; background:white; border-radius:16px; border:1px solid #eef2ff;">
                            <i class="fas fa-id-card" style="font-size:22px; color: var(--primary);"></i>
                            <div style="flex:1;"><h5 style="font-size:14px;">Carte d'identité</h5><p style="font-size:11px; color:#64748b;">Recto-verso, en cours de validité</p></div>
                            <div id="status-id"><i class="far fa-circle"></i></div>
                        </div>
                        <div style="display:flex; align-items:center; gap:15px; padding:14px; background:white; border-radius:16px; border:1px solid #eef2ff;">
                            <i class="fas fa-file-signature" style="font-size:22px; color: var(--primary);"></i>
                            <div style="flex:1;"><h5 style="font-size:14px;">Formulaire rempli</h5><p style="font-size:11px; color:#64748b;">Formulaire de demande signé</p></div>
                            <div id="status-form"><i class="far fa-circle"></i></div>
                        </div>
                        <div style="display:flex; align-items:center; gap:15px; padding:14px; background:white; border-radius:16px; border:1px solid #eef2ff;">
                            <i class="fas fa-home" style="font-size:22px; color: var(--primary);"></i>
                            <div style="flex:1;"><h5 style="font-size:14px;">Justificatif de domicile</h5><p style="font-size:11px; color:#64748b;">Moins de 3 mois</p></div>
                            <div id="status-domicile"><i class="far fa-circle"></i></div>
                        </div>
                    </div>
                </div>
                <div style="background:#f8fafc; border-radius:24px; padding:24px;">
                    <h4 style="margin-bottom:18px; font-size:16px;"><i class="fas fa-cloud-upload-alt"></i> Téléverser un document</h4>
                    <form id="uploadForm" enctype="multipart/form-data">
                        <input type="hidden" name="demande_id" id="currentDemandeId" value="">
                        <div class="drop-zone" id="dropZone" style="border:2px dashed #cbd5e1; border-radius:20px; padding:35px; text-align:center; cursor:pointer; background:white; transition:all 0.3s;">
                            <i class="fas fa-cloud-upload-alt" style="font-size:45px; color: var(--primary);"></i>
                            <h4 style="margin:10px 0 5px;">Glissez-déposez votre fichier</h4>
                            <p style="font-size:12px; color:#64748b;">ou cliquez pour sélectionner</p>
                            <input type="file" name="fichier" id="fileInput" style="display:none;" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="file-info" id="fileInfo" style="display:none; margin:15px 0; padding:12px; background:var(--primary-soft); border-radius:14px; border:1px solid var(--primary);">
                            <span id="fileName"></span> - <span id="fileSize"></span>
                            <button type="button" id="removeFile" style="margin-left:10px; background:none; border:none; color:#dc2626; cursor:pointer;">✖</button>
                        </div>
                        <button type="submit" id="uploadBtn" style="width:100%; padding:14px; background:var(--primary-gradient); color:white; border:none; border-radius:50px; font-weight:600; cursor:pointer; transition:all 0.3s;">📤 Téléverser</button>
                    </form>
                    <div class="uploaded-files-list" style="margin-top:20px; padding-top:15px; border-top:1px solid #e2e8f0;">
                        <h5 style="margin-bottom:12px;">📁 Documents téléversés</h5>
                        <div id="uploadedFiles"><p style="color:#94a3b8; text-align:center;">Sélectionnez une demande dans "Mes demandes"</p></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h4><i class="fas fa-city"></i> Smart Municipality</h4>
            <p>Simplifiez vos démarches administratives avec notre plateforme digitale moderne et intuitive.</p>
        </div>
        <div class="footer-section">
            <h4>Liens rapides</h4>
            <a href="index.php?action=manage"><i class="fas fa-home"></i> Accueil</a>
            <a href="#"><i class="fas fa-concierge-bell"></i> Services en ligne</a>
            <a href="#"><i class="fas fa-envelope"></i> Contact</a>
            <a href="#"><i class="fas fa-question-circle"></i> FAQ</a>
        </div>
        <div class="footer-section">
            <h4>Contact</h4>
            <a href="mailto:contact@smartmunicipality.com"><i class="fas fa-envelope"></i> contact@smartmunicipality.com</a>
            <a href="tel:+33123456789"><i class="fas fa-phone"></i> +33 1 23 45 67 89</a>
            <p><i class="fas fa-clock"></i> Lun-Ven: 9h-17h</p>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; 2026 Smart Municipality - Tous droits réservés | Conçu avec <i class="fas fa-heart" style="color:#4ade80;"></i> pour les citoyens
    </div>
</footer>

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

    const USER_ID = <?php echo $user_id; ?>;

    // ========== ONGLETS ==========
    const tabs = document.querySelectorAll('.tab-btn');
    const panels = document.querySelectorAll('.panel');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            tabs.forEach(t => t.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById(`panel-${target}`).classList.add('active');
        });
    });

    // ========== SYSTÈME DE RATING COMPLET ET FONCTIONNEL ==========
    
    // Fonction pour charger le formulaire ou la note existante
    function loadRatingForService(serviceId) {
        const inputDiv = document.getElementById(`rating-input-${serviceId}`);
        if (!inputDiv) return;
        
        inputDiv.innerHTML = '<div class="loading-rating">Chargement...</div>';
        
        fetch(`index.php?action=get_ratings&service_id=${serviceId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour les étoiles statiques
                    updateStarsStatic(serviceId, data.average);
                    
                    // Mettre à jour moyenne et compteur
                    const avgSpan = document.querySelector(`#rating-${serviceId} .rating-average`);
                    const countSpan = document.querySelector(`#rating-${serviceId} .rating-count`);
                    if (avgSpan) avgSpan.textContent = (data.average || 0) + '/5';
                    if (countSpan) countSpan.textContent = '(' + (data.count || 0) + ' avis)';
                    
                    // Afficher formulaire ou note existante
                    if (data.user_rating) {
                        displayUserRating(serviceId, data.user_rating);
                    } else {
                        displayRatingForm(serviceId);
                    }
                    
                    // Charger la liste des avis
                    loadRatingsList(serviceId);
                } else {
                    inputDiv.innerHTML = '<div class="loading-rating">Erreur de chargement</div>';
                }
            })
            .catch(error => {
                console.error('Erreur chargement rating:', error);
                inputDiv.innerHTML = '<div class="loading-rating">Erreur de chargement</div>';
            });
    }
    
    function updateStarsStatic(serviceId, average) {
        const container = document.getElementById(`stars-static-${serviceId}`);
        if (!container) return;
        
        const fullStars = Math.floor(average);
        const hasHalf = (average - fullStars) >= 0.5;
        let starsHtml = '';
        
        for (let i = 1; i <= 5; i++) {
            if (i <= fullStars) {
                starsHtml += '<i class="fas fa-star star-filled"></i>';
            } else if (i === fullStars + 1 && hasHalf) {
                starsHtml += '<i class="fas fa-star-half-alt star-filled"></i>';
            } else {
                starsHtml += '<i class="far fa-star star-empty"></i>';
            }
        }
        container.innerHTML = starsHtml;
    }
    
    function displayRatingForm(serviceId) {
        const inputDiv = document.getElementById(`rating-input-${serviceId}`);
        if (!inputDiv) return;
        
        inputDiv.innerHTML = `
            <div class="stars-input" id="stars-input-${serviceId}">
                <input type="radio" class="star-input" name="rating-${serviceId}" value="5" id="star5-${serviceId}">
                <label for="star5-${serviceId}" class="star-label">★</label>
                <input type="radio" class="star-input" name="rating-${serviceId}" value="4" id="star4-${serviceId}">
                <label for="star4-${serviceId}" class="star-label">★</label>
                <input type="radio" class="star-input" name="rating-${serviceId}" value="3" id="star3-${serviceId}">
                <label for="star3-${serviceId}" class="star-label">★</label>
                <input type="radio" class="star-input" name="rating-${serviceId}" value="2" id="star2-${serviceId}">
                <label for="star2-${serviceId}" class="star-label">★</label>
                <input type="radio" class="star-input" name="rating-${serviceId}" value="1" id="star1-${serviceId}">
                <label for="star1-${serviceId}" class="star-label">★</label>
            </div>
            <textarea class="rating-comment" id="comment-${serviceId}" placeholder="Votre commentaire (optionnel)" rows="2"></textarea>
            <button class="btn-rating" onclick="submitRating(${serviceId})">⭐ Donner mon avis</button>
        `;
    }
    
    function displayUserRating(serviceId, userRating) {
        const inputDiv = document.getElementById(`rating-input-${serviceId}`);
        if (!inputDiv) return;
        
        inputDiv.innerHTML = `
            <div class="user-rating-info">
                <span>⭐ Votre note : ${userRating}/5</span>
                <button class="btn-delete-rating" onclick="deleteRating(${serviceId})">🗑 Supprimer ma note</button>
            </div>
        `;
    }
    
    // Fonction globale pour soumettre une note
    window.submitRating = function(serviceId) {
        const selectedStar = document.querySelector(`input[name="rating-${serviceId}"]:checked`);
        if (!selectedStar) {
            alert('⭐ Veuillez sélectionner une note (1 à 5 étoiles)');
            return;
        }
        
        const rating = selectedStar.value;
        const comment = document.getElementById(`comment-${serviceId}`)?.value || '';
        
        const formData = new FormData();
        formData.append('service_id', serviceId);
        formData.append('rating', rating);
        formData.append('comment', comment);
        
        fetch('index.php?action=add_rating', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour l'affichage
                updateStarsStatic(serviceId, data.average);
                const avgSpan = document.querySelector(`#rating-${serviceId} .rating-average`);
                const countSpan = document.querySelector(`#rating-${serviceId} .rating-count`);
                if (avgSpan) avgSpan.textContent = (data.average || 0) + '/5';
                if (countSpan) countSpan.textContent = '(' + (data.count || 0) + ' avis)';
                displayUserRating(serviceId, data.user_rating);
                loadRatingsList(serviceId);
                alert('✅ Merci pour votre avis !');
            } else {
                alert('❌ Erreur: ' + (data.message || 'Une erreur est survenue'));
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('❌ Erreur lors de l\'envoi de la note');
        });
    };
    
    // Fonction globale pour supprimer une note
    window.deleteRating = function(serviceId) {
        if (!confirm('Voulez-vous vraiment supprimer votre note ?')) return;
        
        const formData = new FormData();
        formData.append('service_id', serviceId);
        
        fetch('index.php?action=delete_rating', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStarsStatic(serviceId, data.average);
                const avgSpan = document.querySelector(`#rating-${serviceId} .rating-average`);
                const countSpan = document.querySelector(`#rating-${serviceId} .rating-count`);
                if (avgSpan) avgSpan.textContent = (data.average || 0) + '/5';
                if (countSpan) countSpan.textContent = '(' + (data.count || 0) + ' avis)';
                displayRatingForm(serviceId);
                loadRatingsList(serviceId);
                alert('✅ Votre note a été supprimée');
            } else {
                alert('❌ Erreur: ' + (data.message || 'Une erreur est survenue'));
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('❌ Erreur lors de la suppression');
        });
    };
    
    function loadRatingsList(serviceId) {
        const listContainer = document.getElementById(`rating-list-${serviceId}`);
        if (!listContainer) return;
        
        listContainer.innerHTML = '<div class="loading-rating">Chargement des avis...</div>';
        
        fetch(`index.php?action=get_ratings&service_id=${serviceId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.ratings && data.ratings.length > 0) {
                    listContainer.innerHTML = data.ratings.map(r => `
                        <div class="rating-item">
                            <div>
                                <span class="rating-user">${escapeHtml(r.user_name || 'Utilisateur')}</span>
                                <span class="rating-stars-small">
                                    ${generateSmallStars(r.rating)}
                                </span>
                                <span class="rating-date">${formatDateRating(r.created_at)}</span>
                            </div>
                            ${r.comment ? `<div class="rating-comment-text">${escapeHtml(r.comment)}</div>` : ''}
                        </div>
                    `).join('');
                } else {
                    listContainer.innerHTML = '<div class="loading-rating">⭐ Aucun avis pour le moment, soyez le premier à donner votre avis !</div>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                listContainer.innerHTML = '<div class="loading-rating">❌ Erreur de chargement des avis</div>';
            });
    }
    
    function generateSmallStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                stars += '<i class="fas fa-star star-filled"></i>';
            } else {
                stars += '<i class="far fa-star star-empty"></i>';
            }
        }
        return stars;
    }
    
    function formatDateRating(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR');
    }
    
    function escapeHtml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // ========== NOTIFICATIONS ==========
    function loadNotifications() {
        fetch(`index.php?action=get_user_notifications&user_id=${USER_ID}`)
            .then(response => response.json())
            .then(notifications => {
                updateNotificationBadge(notifications);
                renderNotificationList(notifications);
            })
            .catch(error => {
                console.error('Erreur chargement notifications:', error);
                document.getElementById('notificationList').innerHTML = `
                    <div class="empty-notifications">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Erreur de chargement</p>
                    </div>
                `;
            });
    }

    function updateNotificationBadge(notifications) {
        const unreadCount = notifications.filter(n => n.statut === 'non_lu').length;
        const badge = document.getElementById('notificationBadge');
        if (unreadCount > 0) {
            badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }

    function renderNotificationList(notifications) {
        const container = document.getElementById('notificationList');
        if (!notifications || notifications.length === 0) {
            container.innerHTML = `
                <div class="empty-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <p>Aucune notification</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = notifications.map(notif => `
            <div class="notification-item ${notif.statut === 'non_lu' ? 'unread' : 'read'}" data-id="${notif.id}">
                <div class="notification-message">${escapeHtml(notif.message)}</div>
                <div class="notification-date">
                    <i class="far fa-clock"></i> ${formatDate(notif.date_creation)}
                </div>
            </div>
        `).join('');
        
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const id = this.dataset.id;
                if (this.classList.contains('unread')) {
                    markAsRead(id);
                }
            });
        });
    }

    function markAsRead(id) {
        fetch('index.php?action=mark_notification_read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) loadNotifications();
        })
        .catch(error => console.error('Erreur:', error));
    }

    function markAllAsRead() {
        fetch('index.php?action=mark_all_notifications_read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `user_id=${USER_ID}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) loadNotifications();
        })
        .catch(error => console.error('Erreur:', error));
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);
        
        if (minutes < 1) return 'À l\'instant';
        if (minutes < 60) return `Il y a ${minutes} min`;
        if (hours < 24) return `Il y a ${hours} h`;
        if (days < 7) return `Il y a ${days} j`;
        return date.toLocaleDateString('fr-FR');
    }

    const bell = document.getElementById('notificationBell');
    const dropdown = document.getElementById('notificationDropdown');

    if (bell) {
        bell.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('show');
            loadNotifications();
        });
    }

    document.addEventListener('click', () => {
        if (dropdown) dropdown.classList.remove('show');
    });

    const markAllBtn = document.getElementById('markAllRead');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            markAllAsRead();
        });
    }

    setInterval(() => {
        if (dropdown && dropdown.classList.contains('show')) {
            loadNotifications();
        } else {
            fetch(`index.php?action=get_notifications_count&user_id=${USER_ID}`)
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notificationBadge');
                    if (data.count > 0) {
                        badge.textContent = data.count > 9 ? '9+' : data.count;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                })
                .catch(console.error);
        }
    }, 30000);

    // ========== GESTION DES DOCUMENTS ==========
    let selectedFile = null;
    let currentDemandeId = null;
    
    function switchToDocuments(demandeId) {
        tabs.forEach(t => t.classList.remove('active'));
        panels.forEach(p => p.classList.remove('active'));
        document.querySelector('[data-tab="documents"]').classList.add('active');
        document.getElementById('panel-documents').classList.add('active');
        currentDemandeId = demandeId;
        document.getElementById('currentDemandeId').value = demandeId;
        loadUploadedFiles(demandeId);
    }
    
    function loadUploadedFiles(demandeId) {
        fetch(`index.php?action=get_documents&demande_id=${demandeId}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('uploadedFiles');
                if (data.length > 0) {
                    container.innerHTML = data.map(file => `
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; background:white; border-radius:14px; margin-bottom:10px; border:1px solid #eef2ff;">
                            <span><i class="fas fa-file" style="color: var(--primary);"></i> ${file.nom_fichier.substring(0, 35)}... <small style="color:#64748b;">(${(file.taille/1024).toFixed(1)} KB)</small></span>
                            <div>
                                <a href="index.php?action=download_document&id=${file.id}" style="color: var(--primary); margin:0 5px;"><i class="fas fa-download"></i></a>
                                <a href="index.php?action=delete_document&id=${file.id}" style="color:#dc2626;" onclick="return confirm('Supprimer ce document ?')"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<p style="color:#94a3b8; text-align:center;">Aucun document téléversé</p>';
                }
            })
            .catch(error => console.error('Erreur:', error));
    }

    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const fileInfo = document.getElementById('fileInfo');
    const fileNameSpan = document.getElementById('fileName');
    const fileSizeSpan = document.getElementById('fileSize');
    const removeBtn = document.getElementById('removeFile');
    const uploadBtn = document.getElementById('uploadBtn');

    if(dropZone) {
        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.style.borderColor = 'var(--primary)'; dropZone.style.background = 'var(--primary-soft)'; });
        dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = '#cbd5e1'; dropZone.style.background = 'white'; });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#cbd5e1';
            dropZone.style.background = 'white';
            if (e.dataTransfer.files.length) handleFile(e.dataTransfer.files[0]);
        });
    }

    if(fileInput) {
        fileInput.addEventListener('change', (e) => { if (e.target.files.length) handleFile(e.target.files[0]); });
    }

    if(removeBtn) {
        removeBtn.addEventListener('click', () => {
            selectedFile = null;
            fileInput.value = '';
            fileInfo.style.display = 'none';
            if(dropZone) dropZone.style.display = 'block';
            if(uploadBtn) uploadBtn.disabled = false;
        });
    }

    function handleFile(file) {
        if (file.size > 5 * 1024 * 1024) {
            alert('❌ Fichier trop volumineux. Maximum 5 Mo');
            return;
        }
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            alert('❌ Format non supporté. Utilisez PDF, JPG ou PNG');
            return;
        }
        selectedFile = file;
        if(fileNameSpan) fileNameSpan.textContent = file.name;
        let size = file.size;
        if (size < 1024) size = size + ' B';
        else if (size < 1048576) size = (size / 1024).toFixed(2) + ' KB';
        else size = (size / 1048576).toFixed(2) + ' MB';
        if(fileSizeSpan) fileSizeSpan.textContent = size;
        if(dropZone) dropZone.style.display = 'none';
        if(fileInfo) fileInfo.style.display = 'block';
        if(uploadBtn) uploadBtn.disabled = false;
    }

    const uploadForm = document.getElementById('uploadForm');
    if(uploadForm) {
        uploadForm.addEventListener('submit', (e) => {
            e.preventDefault();
            if (!currentDemandeId) {
                alert('⚠️ Sélectionnez une demande dans "Mes demandes" d\'abord');
                return;
            }
            if (!selectedFile) {
                alert('⚠️ Sélectionnez un fichier');
                return;
            }

            const formData = new FormData();
            formData.append('demande_id', currentDemandeId);
            formData.append('fichier', selectedFile);

            if(uploadBtn) {
                uploadBtn.disabled = true;
                uploadBtn.innerHTML = '⏳ Téléversement...';
            }

            fetch('index.php?action=upload_document', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Document téléversé avec succès !');
                        loadUploadedFiles(currentDemandeId);
                        selectedFile = null;
                        if(fileInput) fileInput.value = '';
                        if(fileInfo) fileInfo.style.display = 'none';
                        if(dropZone) dropZone.style.display = 'block';
                    } else {
                        alert('❌ Erreur: ' + (data.message || 'Erreur inconnue'));
                    }
                    if(uploadBtn) {
                        uploadBtn.disabled = false;
                        uploadBtn.innerHTML = '📤 Téléverser';
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert('❌ Erreur lors du téléversement');
                    if(uploadBtn) {
                        uploadBtn.disabled = false;
                        uploadBtn.innerHTML = '📤 Téléverser';
                    }
                });
        });
    }

    fetch(`index.php?action=get_notifications_count&user_id=${USER_ID}`)
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notificationBadge');
            if (data.count > 0) {
                badge.textContent = data.count > 9 ? '9+' : data.count;
                badge.style.display = 'block';
            }
        })
        .catch(console.error);

    initDarkMode();
    
    // Initialiser les ratings pour tous les services APRÈS le chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.service-card').forEach(card => {
            const serviceId = card.dataset.serviceId;
            if (serviceId) {
                loadRatingForService(serviceId);
            }
        });
    });
</script>

<?php 
require_once "controllers/ChatbotController.php";
$chatbotController = new ChatbotController();
$chatbotController->widget(); 
?>

<script src="assets/js/chatbot.js"></script>

</body>
</html>