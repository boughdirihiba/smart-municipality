<?php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/RendezVousController.php';

$db   = new Database();
$conn = $db->getConnection();
$rdv  = new RendezVous($conn);

// Path to the icons folder (relative to this file)
$iconsDir = __DIR__ . '/../../assets/icons/';

// Handle icon upload (POST from the hidden upload form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_icon' && isset($_FILES['icon_file'])) {
    $file = $_FILES['icon_file'];
    $allowedExt = ['svg', 'png', 'jpg', 'jpeg', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file['error'] === UPLOAD_ERR_OK && in_array($ext, $allowedExt) && $file['size'] < 2 * 1024 * 1024) {
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '-', basename($file['name']));
        if (!is_dir($iconsDir)) { @mkdir($iconsDir, 0755, true); }
        $dest = $iconsDir . $safeName;
        if (file_exists($dest)) {
            $nameBase = pathinfo($safeName, PATHINFO_FILENAME);
            $safeName = $nameBase . '-' . time() . '.' . $ext;
            $dest = $iconsDir . $safeName;
        }
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $_SESSION['success'] = 'Icône importée : ' . $safeName;
            $_SESSION['just_uploaded_icon'] = $safeName;
        } else {
            $_SESSION['error'] = "Erreur lors de l'enregistrement du fichier.";
        }
    } else {
        $_SESSION['error'] = "Fichier invalide. Formats acceptés : SVG, PNG, JPG, WEBP (max 2 Mo).";
    }

    $q = [];
    if (!empty($_GET['edit'])) $q[] = 'edit=' . urlencode($_GET['edit']);
    if (!empty($_GET['new'])) $q[] = 'new=1';
    if (!empty($_GET['search'])) $q[] = 'search=' . urlencode($_GET['search']);
    $redirect = (defined('BASE_URL') ? BASE_URL : '') . '/index.php?action=rdv_categories' . ($q ? '&' . implode('&', $q) : '');
    header('Location: ' . $redirect);
    exit;
}

$categories = RendezVousController::getAllCategories($rdv);

// If editing, load the selected category via the model
$editId = $_GET['edit'] ?? '';
$selectedCat = null;
if (!empty($editId)) {
    $selectedCat = RendezVousController::getCategoryById($rdv, $editId);
}
$isCreating = isset($_GET['new']);

// Search
$search = $_GET['search'] ?? '';
$filteredCategories = $categories;
if (!empty($search)) {
    $filteredCategories = array_filter($categories, function($c) use ($search) {
        return stripos($c['nom'], $search) !== false
            || stripos($c['description'] ?? '', $search) !== false;
    });
}

// Dynamically scan the icons folder
$availableIcons = [];
if (is_dir($iconsDir)) {
    foreach (glob($iconsDir . '*.{svg,png,jpg,jpeg,webp,SVG,PNG,JPG,JPEG,WEBP}', GLOB_BRACE) as $file) {
        $availableIcons[] = basename($file);
    }
    $availableIcons = array_unique($availableIcons);
    sort($availableIcons);
}
if (empty($availableIcons)) {
    $availableIcons = ['rdv.svg'];
}

$justUploaded = $_SESSION['just_uploaded_icon'] ?? '';
unset($_SESSION['just_uploaded_icon']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality - Gestion des Catégories</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        html, body { height: 100%; overflow: hidden; }
        body { background-color: #f5f5f5; color: #333; }

        /* ===== SIDEBAR (existing admin style) ===== */
        .page-wrapper { display: flex; height: 100vh; overflow: hidden; }

        .sidebar {
            width: 220px; min-width: 220px;
            background: linear-gradient(180deg, #135D36, #0F3B2C);
            color: white;
            display: flex; flex-direction: column;
            padding: 20px 15px;
        }
        .sidebar-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 5px; }
        .sidebar-logo img { width: 35px; height: 35px; }
        .sidebar-logo h2 { font-size: 15px; line-height: 1.3; }
        .sidebar-logo span { font-size: 11px; color: #aaa; }
        .sidebar hr { border: none; border-top: 1px solid rgba(255,255,255,0.15); margin: 15px 0; }

        .nav-menu ul, .nav-bottom ul { list-style: none; }
        .nav-menu ul li, .nav-bottom ul li {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 10px; border-radius: 8px; cursor: pointer; margin-bottom: 2px;
        }
        .nav-menu ul li:hover, .nav-bottom ul li:hover { background-color: rgba(255,255,255,0.1); }
        .nav-menu ul li.active { background: linear-gradient(135deg, #2FA084, #27ae60); border-radius: 10px; }
        .nav-menu ul li img, .nav-bottom ul li img { width: 18px; height: 18px; }
        .nav-menu ul li a, .nav-bottom ul li a { color: white; text-decoration: none; font-size: 13px; }
        .nav-menu { flex-grow: 1; }

        .sub-nav { list-style: none; margin-left: 26px; margin-top: 2px; margin-bottom: 4px; }
        .sub-nav li { padding: 6px 10px !important; font-size: 12px; }
        .sub-nav li.sub-active {
            background: rgba(255,255,255,0.14) !important;
            border-radius: 6px;
        }
        .sub-nav li a { font-size: 12px !important; }

        /* ===== MAIN ===== */
        .main-content {
            flex: 1; padding: 18px 24px;
            display: flex; flex-direction: column;
            overflow: hidden;
        }

        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-shrink: 0; }
        .top-bar-left h1 { font-size: 22px; color: #333; font-weight: 600; }
        .top-bar-left .breadcrumb { font-size: 12px; color: #888; margin-top: 2px; }
        .top-bar-left .breadcrumb a { color: #135D36; text-decoration: none; font-weight: 500; }
        .top-bar-left .breadcrumb a:hover { text-decoration: underline; }

        .admin-info { display: flex; align-items: center; gap: 10px; font-size: 13px; }
        .admin-info img { width: 32px; height: 32px; border-radius: 50%; }
        .admin-info span { font-weight: bold; }
        .admin-info small { color: #888; font-size: 11px; }

        /* ===== FLASH ===== */
        .flash { padding: 10px 14px; border-radius: 8px; margin-bottom: 10px; font-size: 13px; flex-shrink: 0; }
        .flash.success { background-color: #e8f8ef; color: #27ae60; border: 1px solid #c3e6cb; }
        .flash.error { background-color: #fde8e8; color: #e74c3c; border: 1px solid #f5c6cb; }

        /* ===== WORKSPACE ===== */
        .workspace {
            display: flex; gap: 18px;
            flex: 1; overflow: hidden;
        }

        /* ---- LEFT: GALLERY ---- */
        .gallery-panel {
            flex: 1;
            background: white;
            border-radius: 14px;
            padding: 18px 20px;
            display: flex; flex-direction: column;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .gallery-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 14px; flex-shrink: 0;
        }

        .gallery-title-wrap { display: flex; align-items: center; gap: 10px; }
        .gallery-title { font-size: 16px; font-weight: 700; color: #333; }
        .count-pill {
            background: #135D36; color: white;
            padding: 3px 10px; border-radius: 50px;
            font-size: 11px; font-weight: 700;
        }

        .new-cat-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px 5px 8px;
            background: linear-gradient(135deg, #135D36, #2FA084);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-size: 11.5px;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(19, 93, 54, 0.2);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            margin-left: 4px;
        }

        .new-cat-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(19, 93, 54, 0.3);
        }

        .new-cat-plus {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 300;
            line-height: 1;
            transition: transform 0.3s ease;
        }

        .new-cat-btn:hover .new-cat-plus {
            transform: rotate(90deg);
        }

        .search-wrap {
            position: relative;
            width: 240px;
        }
        .search-wrap input {
            width: 100%;
            padding: 8px 12px 8px 32px;
            border: 1.5px solid #e8e8e8;
            border-radius: 8px;
            font-size: 12.5px;
            font-family: inherit;
            outline: none;
            background: #fafafa;
            transition: all 0.2s;
        }
        .search-wrap input:focus { border-color: #2FA084; background: white; box-shadow: 0 0 0 3px rgba(47,160,132,0.08); }
        .search-wrap::before {
            content: '🔍';
            position: absolute;
            left: 10px; top: 50%;
            transform: translateY(-50%);
            font-size: 11px;
            opacity: 0.4;
        }

        .gallery-grid-wrap {
            flex: 1;
            overflow-y: auto;
            padding-right: 4px;
        }
        .gallery-grid-wrap::-webkit-scrollbar { width: 5px; }
        .gallery-grid-wrap::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
        }

        /* ---- Category Card ---- */
        .cat-card {
            background: white;
            border: 2px solid #eee;
            border-radius: 12px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
            color: inherit;
            display: flex; flex-direction: column;
            position: relative;
            animation: cardIn 0.4s ease both;
            overflow: hidden;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(10px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .cat-card:hover {
            border-color: #2FA084;
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(19,93,54,0.1);
        }

        .cat-card.selected {
            border-color: #135D36;
            background: linear-gradient(135deg, #fafffc, #f0f8f4);
            box-shadow: 0 4px 16px rgba(19,93,54,0.12);
        }

        .cat-card.selected::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #135D36, #2FA084);
        }

        .cat-card-head {
            display: flex; align-items: flex-start; gap: 12px;
            margin-bottom: 10px;
        }

        .cat-icon-bubble {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .cat-card.selected .cat-icon-bubble {
            background: linear-gradient(135deg, #135D36, #2FA084);
            box-shadow: 0 3px 10px rgba(19,93,54,0.25);
        }

        .cat-icon-bubble img { width: 22px; height: 22px; transition: filter 0.3s; }
        .cat-card.selected .cat-icon-bubble img { filter: brightness(0) invert(1); }

        .cat-id-badge {
            font-size: 10px; font-weight: 700;
            color: #999;
            background: #f5f5f5;
            padding: 2px 8px;
            border-radius: 50px;
            margin-left: auto;
        }
        .cat-card.selected .cat-id-badge { background: #135D36; color: white; }

        .cat-name {
            font-size: 14px; font-weight: 700;
            color: #333; margin-bottom: 4px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        .cat-desc {
            font-size: 11.5px; color: #888;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 32px;
        }

        .cat-card-foot {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: 10px; padding-top: 10px;
            border-top: 1px solid #f0f0f0;
        }

        .cat-icon-name {
            font-size: 10.5px; color: #999;
            font-family: 'Courier New', monospace;
            background: #fafafa;
            padding: 2px 7px;
            border-radius: 4px;
        }

        .cat-card-actions {
            display: flex; gap: 4px;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .cat-card:hover .cat-card-actions, .cat-card.selected .cat-card-actions { opacity: 1; }

        .mini-action {
            width: 24px; height: 24px; border-radius: 6px;
            border: none; background: #f5f5f5; color: #666;
            cursor: pointer; font-size: 11px;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.15s;
        }
        .mini-action:hover { transform: scale(1.1); }
        .mini-action.danger:hover { background: #fde8e8; color: #e74c3c; }

        /* ---- ADD NEW TILE ---- */
        .cat-add-card {
            border: 2px dashed #ccc;
            background: #fafafa;
            display: flex; align-items: center; justify-content: center;
            flex-direction: column; gap: 8px;
            cursor: pointer;
            min-height: 140px;
            transition: all 0.25s;
            text-decoration: none;
            color: #888;
        }
        .cat-add-card:hover {
            border-color: #2FA084;
            background: #f0f8f4;
            color: #135D36;
            transform: translateY(-3px);
        }
        .cat-add-icon {
            width: 44px; height: 44px; border-radius: 50%;
            background: white;
            border: 2px solid currentColor;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; font-weight: 300;
            transition: transform 0.3s;
        }
        .cat-add-card:hover .cat-add-icon { transform: rotate(90deg); }
        .cat-add-label { font-size: 12px; font-weight: 700; }

        .empty-search {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px 20px;
            color: #999;
            font-size: 13px;
        }

        /* ---- RIGHT: EDITOR PANEL ---- */
        .editor-panel {
            width: 380px; min-width: 380px;
            background: white;
            border-radius: 14px;
            display: flex; flex-direction: column;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .editor-header {
            background: linear-gradient(135deg, #135D36, #2FA084);
            padding: 16px 20px;
            color: white;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }
        .editor-header::before {
            content: ''; position: absolute;
            top: -30px; right: -30px;
            width: 120px; height: 120px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        .editor-mode-tag {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1.3px;
            background: rgba(255,255,255,0.2);
            padding: 3px 10px;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 6px;
            position: relative; z-index: 1;
        }
        .editor-title {
            font-size: 17px; font-weight: 700;
            position: relative; z-index: 1;
        }
        .editor-subtitle {
            font-size: 11.5px; color: rgba(255,255,255,0.7);
            margin-top: 2px;
            position: relative; z-index: 1;
        }

        .editor-scroll {
            flex: 1; overflow-y: auto;
            padding: 18px 20px;
        }
        .editor-scroll::-webkit-scrollbar { width: 5px; }
        .editor-scroll::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }

        /* ---- LIVE PREVIEW ---- */
        .preview-label {
            font-size: 9px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1.3px;
            color: #888; margin-bottom: 8px;
            display: flex; align-items: center; gap: 6px;
        }
        .preview-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, #ddd, transparent);
        }

        .preview-pulse {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #2FA084;
            animation: pulseDot 2s ease infinite;
        }
        @keyframes pulseDot {
            0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(47,160,132,0.6); }
            50% { opacity: 0.7; box-shadow: 0 0 0 6px rgba(47,160,132,0); }
        }

        .preview-card-wrap {
            background: #fafafa;
            border: 1.5px solid #eee;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 20px;
        }

        .preview-card {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 2px solid #2FA084;
            background: linear-gradient(135deg, rgba(26,122,78,0.06), rgba(61,220,132,0.06));
            box-shadow: 0 2px 10px rgba(26,122,78,0.1);
            transition: all 0.3s ease;
        }

        .preview-icon-wrap {
            width: 32px; height: 32px; border-radius: 8px;
            background: linear-gradient(135deg, #135D36, #2FA084);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(19,93,54,0.2);
        }
        .preview-icon-wrap img {
            width: 16px; height: 16px;
            filter: brightness(0) invert(1);
        }

        .preview-text {
            font-size: 12px; font-weight: 600; color: #135D36;
        }

        .preview-note {
            font-size: 10px; color: #999;
            margin-top: 8px;
            font-style: italic;
            text-align: center;
        }

        /* ---- FORM FIELDS ---- */
        .form-group { margin-bottom: 14px; }
        .form-label {
            display: block;
            font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.8px;
            color: #555; margin-bottom: 5px;
        }
        .form-input, .form-textarea {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #e5e5e5;
            border-radius: 8px;
            font-size: 13px;
            font-family: inherit;
            background: #fafafa;
            outline: none;
            transition: all 0.2s;
            color: #333;
        }
        .form-textarea {
            resize: vertical;
            min-height: 60px;
            line-height: 1.5;
        }
        .form-input:focus, .form-textarea:focus {
            border-color: #2FA084;
            background: white;
            box-shadow: 0 0 0 3px rgba(47,160,132,0.08);
        }
        .form-hint {
            font-size: 10.5px; color: #999;
            margin-top: 3px;
        }

        /* ---- Icon Picker ---- */
        .icon-picker-current {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px;
            background: #fafafa;
            border: 1.5px solid #e5e5e5;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 6px;
            transition: all 0.2s;
        }
        .icon-picker-current:hover { border-color: #2FA084; }
        .icon-picker-current-img {
            width: 26px; height: 26px;
            padding: 5px;
            background: white;
            border-radius: 6px;
            border: 1px solid #eee;
        }
        .icon-picker-current-name {
            flex: 1;
            font-size: 12px;
            font-family: 'Courier New', monospace;
            color: #555;
        }
        .icon-picker-toggle {
            font-size: 10px; color: #888;
        }

        .icon-picker-grid {
            display: none;
            grid-template-columns: repeat(5, 1fr);
            gap: 6px;
            padding: 10px;
            background: #fafafa;
            border: 1.5px solid #e5e5e5;
            border-radius: 8px;
            max-height: 140px;
            overflow-y: auto;
            animation: slideDown 0.25s ease;
        }
        .icon-picker-grid.open { display: grid; }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .icon-option {
            aspect-ratio: 1;
            background: white;
            border: 1.5px solid transparent;
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: all 0.15s;
            padding: 6px;
        }
        .icon-option:hover { border-color: #2FA084; transform: scale(1.08); }
        .icon-option.chosen {
            border-color: #135D36;
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            box-shadow: 0 2px 6px rgba(19,93,54,0.15);
        }
        .icon-option img { width: 100%; height: 100%; object-fit: contain; }

        /* ---- Upload tile inside icon picker ---- */
        .icon-upload-tile {
            aspect-ratio: 1;
            background: white !important;
            border: 2px dashed #2FA084 !important;
            border-radius: 7px;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 2px;
            cursor: pointer;
            transition: all 0.2s;
            color: #135D36;
            position: relative;
        }
        .icon-upload-tile:hover {
            background: linear-gradient(135deg, #f0f8f4, #e8f5e9) !important;
            transform: scale(1.08);
            border-color: #135D36 !important;
        }
        .icon-upload-tile .upload-symbol {
            font-size: 15px;
            font-weight: 700;
            line-height: 1;
        }
        .icon-upload-tile .upload-label {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .icon-option.just-new {
            animation: newIconFlash 1.4s ease;
        }
        @keyframes newIconFlash {
            0% { box-shadow: 0 0 0 0 rgba(47,160,132,0.7); transform: scale(1.15); }
            60% { box-shadow: 0 0 0 12px rgba(47,160,132,0); transform: scale(1); }
            100% { box-shadow: 0 0 0 0 rgba(47,160,132,0); transform: scale(1); }
        }

        /* ---- Footer Actions ---- */
        .editor-footer {
            padding: 14px 20px;
            border-top: 1px solid #eee;
            display: flex; gap: 8px;
            flex-shrink: 0;
            background: #fafafa;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 8px;
            border: none;
            font-family: inherit;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #135D36, #2FA084);
            color: white;
            box-shadow: 0 3px 10px rgba(19,93,54,0.25);
            flex: 1;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 5px 14px rgba(19,93,54,0.3); }

        .btn-danger {
            background: #fde8e8; color: #e74c3c;
        }
        .btn-danger:hover { background: #e74c3c; color: white; }

        .btn-ghost {
            background: transparent; color: #666;
            border: 1.5px solid #e5e5e5;
        }
        .btn-ghost:hover { background: #f5f5f5; color: #333; }

        /* ---- EMPTY EDITOR ---- */
        .editor-empty {
            flex: 1;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 30px 24px;
            text-align: center;
        }
        .editor-empty-icon {
            width: 64px; height: 64px; border-radius: 50%;
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
            margin-bottom: 14px;
            animation: floaty 3s ease infinite;
        }
        @keyframes floaty {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .editor-empty-title { font-size: 15px; font-weight: 700; color: #333; margin-bottom: 6px; }
        .editor-empty-text { font-size: 12.5px; color: #888; line-height: 1.5; margin-bottom: 16px; max-width: 240px; }
        .editor-empty .btn { padding: 9px 16px; }

        /* ---- RESPONSIVE ---- */
        @media (max-width: 1100px) {
            .editor-panel { width: 340px; min-width: 340px; }
        }
        @media (max-width: 900px) {
            html, body { overflow: auto; }
            .page-wrapper { flex-direction: column; height: auto; }
            .sidebar { width: 100%; min-width: 0; }
            .main-content { overflow: visible; }
            .workspace { flex-direction: column; overflow: visible; }
            .editor-panel { width: 100%; min-width: 0; }
        }
    </style>
</head>
<body>

    <!-- Hidden form for icon upload (kept outside the category form) -->
    <?php
        $uploadQ = [];
        if (!empty($editId)) $uploadQ[] = 'edit=' . urlencode($editId);
        if ($isCreating) $uploadQ[] = 'new=1';
        if (!empty($search)) $uploadQ[] = 'search=' . urlencode($search);
        $uploadAction = 'categories.php' . ($uploadQ ? '?' . implode('&', $uploadQ) : '');
    ?>
    <form id="iconUploadForm" action="<?php echo $uploadAction; ?>" method="POST" enctype="multipart/form-data" style="display:none;">
        <input type="hidden" name="action" value="upload_icon">
        <input type="file" id="iconFileInput" name="icon_file"
               accept=".svg,.png,.jpg,.jpeg,.webp,image/svg+xml,image/png,image/jpeg,image/webp"
               onchange="handleIconUpload(this);">
    </form>

    <div class="page-wrapper">

        <!-- ===== SIDEBAR ===== -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <img src="<?php echo BASE_URL; ?>/assets/icons/logo.png" alt="Logo">
                <div>
                    <h2>Smart Ville</h2>
                    <span>Admin</span>
                </div>
            </div>
            <hr>
            <nav class="nav-menu">
                <ul>
                    <li><img src="<?php echo BASE_URL; ?>/assets/icons/profil.svg" alt=""><a href="<?php echo BASE_URL; ?>/index.php?action=profil">Profile</a></li>
                    <li><img src="<?php echo BASE_URL; ?>/assets/icons/alertes.svg" alt=""><a href="<?php echo BASE_URL; ?>/index.php?action=evenements">Événements</a></li>
                    <li><img src="<?php echo BASE_URL; ?>/assets/icons/carte.svg" alt=""><a href="<?php echo BASE_URL; ?>/index.php?route=home/index">Carte intelligente</a></li>
                    <li><img src="<?php echo BASE_URL; ?>/assets/icons/blog.svg" alt=""><a href="<?php echo BASE_URL; ?>/index.php?action=blog">Blog</a></li>
                    <li><img src="<?php echo BASE_URL; ?>/assets/icons/services.svg" alt=""><a href="<?php echo BASE_URL; ?>/index.php?action=list_services">Services en ligne</a></li>
                    <li class="active"><img src="<?php echo BASE_URL; ?>/assets/icons/rdv.svg" alt=""><a href="<?php echo BASE_URL; ?>/index.php?action=rdv_backoffice">Rendez-vous</a></li>
                </ul>
                <ul class="sub-nav">
                    <li><a href="<?php echo BASE_URL; ?>/index.php?action=rdv_backoffice">&rsaquo; Liste RDV</a></li>
                    <li class="sub-active"><a href="<?php echo BASE_URL; ?>/index.php?action=rdv_categories">&rsaquo; Catégories</a></li>
                </ul>
            </nav>
            <hr>
            <div class="nav-bottom">
                <ul>
                    <li><img src="<?php echo BASE_URL; ?>/assets/icons/parametres.svg" alt=""><a href="<?php echo BASE_URL; ?>/index.php?route=home/index">Paramètres</a></li>
                    <li><img src="<?php echo BASE_URL; ?>/assets/icons/deconnexion.svg" alt=""><a href="<?php echo BASE_URL; ?>/logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </aside>

        <!-- ===== MAIN ===== -->
        <main class="main-content">

            <header class="top-bar">
                <div class="top-bar-left">
                    <h1>Gestion des Catégories</h1>
                    <div class="breadcrumb"><a href="<?php echo BASE_URL; ?>/index.php?action=rdv_backoffice">Rendez-vous</a> &rsaquo; Catégories de service</div>
                </div>
                <div class="admin-info">
                    <div>
                        <span>Admin</span><br>
                        <small>Sarah B.</small>
                    </div>
                    <img src="<?php echo BASE_URL; ?>/assets/icons/avatar.svg" alt="">
                </div>
            </header>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="flash success">&#10004; <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="flash error">&#10006; <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- ===== WORKSPACE ===== -->
            <div class="workspace">

                <!-- ===== GALLERY PANEL ===== -->
                <section class="gallery-panel">
                    <div class="gallery-header">
                        <div class="gallery-title-wrap">
                            <span class="gallery-title">Toutes les catégories</span>
                            <span class="count-pill"><?php echo count($categories); ?></span>
                            <a href="<?php echo BASE_URL; ?>/index.php?action=rdv_categories&new=1<?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" class="new-cat-btn">
                                <span class="new-cat-plus">+</span>
                                <span>Nouvelle</span>
                            </a>
                        </div>
                        <form method="GET" class="search-wrap" onsubmit="return true;">
                            <input type="text" name="search" placeholder="Rechercher une catégorie..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
                            <?php if (!empty($editId)): ?><input type="hidden" name="edit" value="<?php echo htmlspecialchars($editId); ?>"><?php endif; ?>
                        </form>
                    </div>

                    <div class="gallery-grid-wrap">
                        <div class="gallery-grid">

                            <?php if (empty($filteredCategories) && !empty($search)): ?>
                                <div class="empty-search">Aucune catégorie ne correspond à "<?php echo htmlspecialchars($search); ?>".</div>
                            <?php else: ?>
                                <?php foreach ($filteredCategories as $cat):
                                    $isSel = ($selectedCat && $selectedCat['id'] == $cat['id']);
                                ?>
                                <a href="<?php echo BASE_URL; ?>/index.php?action=rdv_categories&edit=<?php echo $cat['id']; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" class="cat-card <?php echo $isSel ? 'selected' : ''; ?>">
                                    <div class="cat-card-head">
                                        <div class="cat-icon-bubble">
                                            <img src="<?php echo BASE_URL; ?>/assets/icons/<?php echo htmlspecialchars($cat['icone']); ?>" alt="">
                                        </div>
                                        <span class="cat-id-badge">#<?php echo $cat['id']; ?></span>
                                    </div>
                                    <div class="cat-name"><?php echo htmlspecialchars($cat['nom']); ?></div>
                                    <div class="cat-desc"><?php echo !empty($cat['description']) ? htmlspecialchars($cat['description']) : '<em style="color:#bbb;">Aucune description</em>'; ?></div>
                                    <div class="cat-card-foot">
                                        <span class="cat-icon-name"><?php echo htmlspecialchars($cat['icone']); ?></span>
                                        <div class="cat-card-actions">
                                            <button class="mini-action danger" title="Supprimer" onclick="event.preventDefault(); event.stopPropagation(); deleteCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars(addslashes($cat['nom'])); ?>');">&#10006;</button>
                                        </div>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        </div>
                    </div>
                </section>

                <!-- ===== EDITOR PANEL ===== -->
                <section class="editor-panel">

                    <?php if ($isCreating || $selectedCat): ?>

                    <div class="editor-header">
                        <span class="editor-mode-tag">
                            <?php echo $isCreating ? '&#43; Création' : '&#9998; Modification'; ?>
                        </span>
                        <div class="editor-title">
                            <?php echo $isCreating ? 'Nouvelle catégorie' : htmlspecialchars($selectedCat['nom']); ?>
                        </div>
                        <div class="editor-subtitle">
                            <?php echo $isCreating ? 'Remplissez les champs ci-dessous' : 'ID #' . $selectedCat['id'] . ' · Modifiez puis enregistrez'; ?>
                        </div>
                    </div>

                    <form class="editor-scroll" id="categoryForm"
                          action="<?php echo BASE_URL; ?>/index.php"
                          method="POST">
                        <input type="hidden" name="action" value="<?php echo $isCreating ? 'rdv_create_category' : 'rdv_update_category'; ?>">
                        <?php if (!$isCreating): ?>
                            <input type="hidden" name="id" value="<?php echo $selectedCat['id']; ?>">
                        <?php endif; ?>

                        <!-- LIVE PREVIEW -->
                        <div class="preview-label">
                            <span class="preview-pulse"></span>
                            Aperçu en direct
                        </div>
                        <div class="preview-card-wrap">
                            <div class="preview-card">
                                <div class="preview-icon-wrap">
                                    <img id="previewIcon" src="<?php echo BASE_URL; ?>/assets/icons/<?php echo htmlspecialchars($selectedCat['icone'] ?? 'rdv.svg'); ?>" alt="">
                                </div>
                                <div class="preview-text" id="previewName">
                                    <?php echo htmlspecialchars($selectedCat['nom'] ?? 'Nom de la catégorie'); ?>
                                </div>
                            </div>
                            <div class="preview-note">Ainsi apparaîtra la catégorie pour les citoyens</div>
                        </div>

                        <!-- NAME -->
                        <div class="form-group">
                            <label class="form-label" for="f_nom">Nom de la catégorie</label>
                            <input type="text" class="form-input" id="f_nom" name="nom"
                                   value="<?php echo htmlspecialchars($selectedCat['nom'] ?? ''); ?>"
                                   placeholder="Ex: État Civil" required
                                   oninput="document.getElementById('previewName').textContent = this.value || 'Nom de la catégorie';">
                        </div>

                        <!-- DESCRIPTION -->
                        <div class="form-group">
                            <label class="form-label" for="f_desc">Description</label>
                            <textarea class="form-textarea" id="f_desc" name="description"
                                      placeholder="Décrivez brièvement les services offerts..."><?php echo htmlspecialchars($selectedCat['description'] ?? ''); ?></textarea>
                            <div class="form-hint">Visible dans la liste pour guider les citoyens</div>
                        </div>

                        <!-- ICON PICKER -->
                        <div class="form-group">
                            <label class="form-label">Icône</label>
                            <div class="icon-picker-current" onclick="toggleIconPicker()">
                                <img class="icon-picker-current-img" id="iconPickerImg"
                                     src="<?php echo BASE_URL; ?>/assets/icons/<?php echo htmlspecialchars($selectedCat['icone'] ?? 'rdv.svg'); ?>" alt="">
                                <span class="icon-picker-current-name" id="iconPickerName">
                                    <?php echo htmlspecialchars($selectedCat['icone'] ?? 'rdv.svg'); ?>
                                </span>
                                <span class="icon-picker-toggle">▼</span>
                            </div>
                            <div class="icon-picker-grid" id="iconPickerGrid">
                                <!-- Upload tile (triggers hidden file input) -->
                                <div class="icon-upload-tile" onclick="document.getElementById('iconFileInput').click()" title="Importer une icône depuis mon PC">
                                    <span class="upload-symbol">&uarr;</span>
                                    <span class="upload-label">Importer</span>
                                </div>
                                <?php foreach ($availableIcons as $icon):
                                    $isChosen = ($selectedCat['icone'] ?? '') == $icon;
                                    $isJust = ($justUploaded === $icon);
                                ?>
                                    <div class="icon-option <?php echo $isChosen ? 'chosen' : ''; ?> <?php echo $isJust ? 'just-new' : ''; ?>"
                                         onclick="pickIcon('<?php echo $icon; ?>')"
                                         title="<?php echo $icon; ?>">
                                        <img src="<?php echo BASE_URL; ?>/assets/icons/<?php echo $icon; ?>" alt="">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="icone" id="iconeInput" value="<?php echo htmlspecialchars($selectedCat['icone'] ?? 'rdv.svg'); ?>">
                            <div class="form-hint">Cliquez pour choisir une icône</div>
                        </div>
                    </form>

                    <div class="editor-footer">
                        <?php if (!$isCreating): ?>
                            <button class="btn btn-danger" onclick="deleteCategory(<?php echo $selectedCat['id']; ?>, '<?php echo htmlspecialchars(addslashes($selectedCat['nom'])); ?>');">🗑</button>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>/index.php?action=rdv_categories" class="btn btn-ghost">Annuler</a>
                        <button class="btn btn-primary" onclick="saveCategory()">
                            &#10004; <?php echo $isCreating ? 'Créer' : 'Enregistrer'; ?>
                        </button>
                    </div>

                    <?php else: ?>

                    <!-- ===== EMPTY STATE ===== -->
                    <div class="editor-empty">
                        <div class="editor-empty-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="56" height="56" fill="none">
                                <!-- Top-left card -->
                                <rect x="4" y="4" width="24" height="24" rx="5" fill="#135D36" opacity="0.15"/>
                                <rect x="4" y="4" width="24" height="24" rx="5" stroke="#135D36" stroke-width="2.2"/>
                                <line x1="11" y1="13" x2="21" y2="13" stroke="#135D36" stroke-width="2" stroke-linecap="round"/>
                                <line x1="11" y1="19" x2="18" y2="19" stroke="#135D36" stroke-width="2" stroke-linecap="round"/>
                                <!-- Top-right card -->
                                <rect x="36" y="4" width="24" height="24" rx="5" fill="#135D36" opacity="0.08"/>
                                <rect x="36" y="4" width="24" height="24" rx="5" stroke="#135D36" stroke-width="2.2" stroke-dasharray="4 2"/>
                                <!-- Bottom-left card -->
                                <rect x="4" y="36" width="24" height="24" rx="5" fill="#135D36" opacity="0.08"/>
                                <rect x="4" y="36" width="24" height="24" rx="5" stroke="#135D36" stroke-width="2.2" stroke-dasharray="4 2"/>
                                <!-- Bottom-right card (highlighted) -->
                                <rect x="36" y="36" width="24" height="24" rx="5" fill="#2FA084" opacity="0.18"/>
                                <rect x="36" y="36" width="24" height="24" rx="5" stroke="#2FA084" stroke-width="2.2"/>
                                <line x1="43" y1="45" x2="53" y2="45" stroke="#2FA084" stroke-width="2" stroke-linecap="round"/>
                                <line x1="43" y1="51" x2="50" y2="51" stroke="#2FA084" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="editor-empty-text">
                            Sélectionnez une catégorie à gauche pour la modifier, ou créez-en une nouvelle.
                        </div>
                        <a href="<?php echo BASE_URL; ?>/index.php?action=rdv_categories&new=1" class="btn btn-primary">
                            &#43; Créer une catégorie
                        </a>
                    </div>

                    <?php endif; ?>

                </section>

            </div>

        </main>

    </div>

    <script>
        function toggleIconPicker() {
            document.getElementById('iconPickerGrid').classList.toggle('open');
        }

        function handleIconUpload(input) {
            if (!input.files || !input.files[0]) return;

            var file = input.files[0];
            var maxSize = 2 * 1024 * 1024;
            var allowed = ['svg', 'png', 'jpg', 'jpeg', 'webp'];
            var ext = file.name.split('.').pop().toLowerCase();

            if (!allowed.includes(ext)) {
                Swal.fire({
                    title: "Format non supporté",
                    text: "Formats acceptés : SVG, PNG, JPG, WEBP",
                    icon: "warning",
                    confirmButtonColor: "#135D36"
                });
                input.value = '';
                return;
            }

            if (file.size > maxSize) {
                Swal.fire({
                    title: "Fichier trop volumineux",
                    text: "Taille maximale : 2 Mo",
                    icon: "warning",
                    confirmButtonColor: "#135D36"
                });
                input.value = '';
                return;
            }

            // Show loading and submit
            Swal.fire({
                title: "Importation en cours...",
                text: file.name,
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });

            document.getElementById('iconUploadForm').submit();
        }

        function pickIcon(iconName) {
            document.getElementById('iconeInput').value = iconName;
            document.getElementById('iconPickerImg').src = '../../assets/icons/' + iconName;
            document.getElementById('iconPickerName').textContent = iconName;
            document.getElementById('previewIcon').src = '../../assets/icons/' + iconName;

            document.querySelectorAll('.icon-option').forEach(function(el) { el.classList.remove('chosen'); });
            event.currentTarget.classList.add('chosen');

            // Auto-close after selection
            setTimeout(function() {
                document.getElementById('iconPickerGrid').classList.remove('open');
            }, 200);
        }

        function saveCategory() {
            var form = document.getElementById('categoryForm');
            var nom = form.querySelector('#f_nom').value.trim();

            if (!nom) {
                Swal.fire({
                    title: "Nom requis",
                    text: "Le nom de la catégorie ne peut pas être vide",
                    icon: "warning",
                    confirmButtonColor: "#135D36"
                });
                return;
            }

            var isNew = form.querySelector('input[name="action"]').value === 'create_category';

            Swal.fire({
                title: isNew ? "Créer cette catégorie ?" : "Enregistrer les modifications ?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#135D36",
                cancelButtonColor: "#aaa",
                confirmButtonText: isNew ? "Oui, créer" : "Oui, enregistrer",
                cancelButtonText: "Annuler"
            }).then(function(result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        function deleteCategory(id, nom) {
            Swal.fire({
                title: "Supprimer cette catégorie ?",
                html: "La catégorie <b>" + nom + "</b> sera supprimée définitivement.<br><small>Les rendez-vous liés pourraient être affectés.</small>",
                icon: "error",
                showCancelButton: true,
                confirmButtonColor: "#e74c3c",
                cancelButtonColor: "#aaa",
                confirmButtonText: "Oui, supprimer",
                cancelButtonText: "Annuler"
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Catégorie supprimée !",
                        icon: "success",
                        timer: 1200,
                        showConfirmButton: false
                    }).then(function() {
                        window.location.href = "<?php echo BASE_URL; ?>/index.php?action=rdv_delete_category&id=" + id;
                    });
                }
            });
        }

        // Close icon picker when clicking outside
        document.addEventListener('click', function(e) {
            var picker = document.getElementById('iconPickerGrid');
            var trigger = document.querySelector('.icon-picker-current');
            if (picker && trigger && !picker.contains(e.target) && !trigger.contains(e.target)) {
                picker.classList.remove('open');
            }
        });
    </script>

</body>
</html>