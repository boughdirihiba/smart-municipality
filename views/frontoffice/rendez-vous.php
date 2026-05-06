<?php
session_start();

require_once '../../config/database.php';
require_once '../../controllers/RendezVousController.php';

$db = new Database();
$conn = $db->getConnection();
$rdv  = new RendezVous($conn);

$categories = RendezVousController::getAllCategories($rdv);
$mesRdv     = RendezVousController::readByUser($rdv, 1);

// Multi-service selected categories
$selectedCatIds = [];
if (!empty($_GET['cats'])) {
    $selectedCatIds = array_map('intval', (array)$_GET['cats']);
}
if (empty($selectedCatIds) && !empty($_GET['categorie_id'])) {
    $selectedCatIds = [(int)$_GET['categorie_id']];
}

$selectedDate  = $_GET['date']  ?? '';
$selectedHeure = $_GET['heure'] ?? '';
$mode          = $_GET['mode']  ?? ''; // 'A' = time first, 'B' = date first

// Build selected category names
$selectedCatNames = [];
foreach ($categories as $cat) {
    if (in_array((int)$cat['id'], $selectedCatIds)) {
        $selectedCatNames[] = $cat['nom'];
    }
}

// Build chained slots
$GAP_MINUTES  = 20;
$chainedSlots = [];
if (!empty($selectedCatIds) && !empty($selectedDate) && !empty($selectedHeure)) {
    $baseH = (int)substr($selectedHeure, 0, 2);
    $baseM = (int)substr($selectedHeure, 3, 2);
    foreach ($selectedCatIds as $i => $catId) {
        $totalMin = $baseH * 60 + $baseM + ($i * $GAP_MINUTES);
        $h = intdiv($totalMin, 60);
        $m = $totalMin % 60;
        $heureStr = sprintf('%02d:%02d:00', $h, $m);
        $catNom = '';
        foreach ($categories as $c) { if ($c['id'] == $catId) { $catNom = $c['nom']; break; } }
        $chainedSlots[] = [
            'cat_id'        => $catId,
            'cat_nom'       => $catNom,
            'heure'         => $heureStr,
            'heure_display' => sprintf('%02d:%02d', $h, $m)
        ];
    }
}

// Option B: available times for selected date (PHP-side for initial render)
$availableTimes = [];
if ($mode === 'B' && !empty($selectedCatIds) && !empty($selectedDate) && empty($selectedHeure)) {
    $availableTimes = RendezVousController::getAvailableTimes($rdv, $selectedCatIds, $selectedDate);
}

// Step logic
$step = 1;
if (!empty($selectedCatIds) && empty($mode))                                                   $step = 'mode';
if (!empty($selectedCatIds) && !empty($mode) && empty($selectedDate) && empty($selectedHeure)) $step = 2;
if (!empty($selectedCatIds) && $mode === 'B' && !empty($selectedDate) && empty($selectedHeure)) $step = 2;
if (!empty($selectedCatIds) && !empty($mode) && !empty($selectedDate) && !empty($selectedHeure)) $step = 3;

// Build query string helper
function catsQuery($catIds, $extra = '') {
    $q = implode('&', array_map(fn($id) => 'cats[]=' . $id, $catIds));
    return $q . ($extra ? '&' . $extra : '');
}

$joursSemaine = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
$moisNoms     = ['', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
$moisComplet  = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality - Rendez-vous</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --forest: #0B4F30;
            --forest-deep: #073D24;
            --emerald: #1A7A4E;
            --mint: #3DDC84;
            --mint-soft: #B8F0D2;
            --sage: #E8F5E9;
            --cream: #FAFDF7;
            --pearl: #F3F7F0;
            --slate: #2D3436;
            --stone: #636E72;
            --mist: #B2BEC3;
            --white: #FFFFFF;
            --danger: #E74C3C;
            --shadow-sm: 0 1px 3px rgba(11,79,48,0.06);
            --shadow-md: 0 4px 20px rgba(11,79,48,0.08);
            --shadow-lg: 0 12px 40px rgba(11,79,48,0.12);
            --shadow-xl: 0 20px 60px rgba(11,79,48,0.14);
            --radius-sm: 6px;
            --radius-md: 12px;
            --radius-lg: 18px;
            --radius-xl: 24px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--slate);
            height: 100vh;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background:
                radial-gradient(ellipse 80% 60% at 10% 0%, rgba(61,220,132,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 90% 100%, rgba(26,122,78,0.06) 0%, transparent 60%);
            pointer-events: none; z-index: 0;
        }

        /* ===== NAVBAR ===== */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            background: rgba(255,255,255,0.82);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid rgba(11,79,48,0.07);
            padding: 0 32px; height: 60px;
            display: flex; align-items: center; justify-content: space-between;
            transition: box-shadow 0.3s ease;
        }
        .navbar.scrolled { box-shadow: var(--shadow-md); }
        .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-brand img { width: 34px; height: 34px; filter: drop-shadow(0 2px 4px rgba(11,79,48,0.15)); }
        .nav-brand-text { font-family: 'Playfair Display', serif; font-weight: 700; font-size: 16px; color: var(--forest); letter-spacing: -0.3px; }
        .nav-brand-text span { color: var(--emerald); }
        .nav-links { display: flex; align-items: center; gap: 2px; list-style: none; }
        .nav-links li a {
            display: flex; align-items: center; gap: 6px; padding: 7px 14px;
            border-radius: 9px; text-decoration: none; color: var(--stone);
            font-size: 12.5px; font-weight: 500; transition: all 0.25s ease;
        }
        .nav-links li a:hover { color: var(--forest); background: var(--sage); }
        .nav-links li a.active {
            color: var(--white);
            background: linear-gradient(135deg, var(--forest) 0%, var(--emerald) 100%);
            box-shadow: 0 2px 10px rgba(11,79,48,0.25);
        }
        .nav-links li a img { width: 16px; height: 16px; opacity: 0.7; transition: opacity 0.2s; }
        .nav-links li a:hover img { opacity: 0.9; }
        .nav-links li a.active img { opacity: 1; filter: brightness(0) invert(1); }
        .nav-right { display: flex; align-items: center; gap: 12px; }
        .nav-search {
            display: flex; align-items: center; background: var(--pearl);
            border: 1.5px solid transparent; border-radius: 10px;
            padding: 7px 12px; width: 190px; transition: all 0.3s ease;
        }
        .nav-search:focus-within { background: var(--white); border-color: var(--mint); box-shadow: 0 0 0 3px rgba(61,220,132,0.12); width: 230px; }
        .nav-search img { width: 14px; height: 14px; margin-right: 7px; opacity: 0.5; }
        .nav-search input { border: none; outline: none; background: transparent; font-size: 12px; font-family: 'DM Sans', sans-serif; color: var(--slate); width: 100%; }
        .nav-user { display: flex; align-items: center; gap: 8px; padding: 4px 10px 4px 4px; border-radius: 50px; background: var(--pearl); cursor: pointer; transition: background 0.2s; }
        .nav-user:hover { background: var(--sage); }
        .nav-user img { width: 28px; height: 28px; border-radius: 50%; border: 2px solid var(--mint-soft); }
        .nav-user span { font-size: 12px; font-weight: 600; color: var(--slate); }
        .nav-settings-btn { display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: var(--pearl); cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .nav-settings-btn:hover { background: var(--sage); transform: rotate(45deg); }
        .nav-settings-btn img { width: 16px; height: 16px; opacity: 0.6; }
        .mobile-toggle { display: none; background: none; border: none; cursor: pointer; width: 32px; height: 32px; flex-direction: column; align-items: center; justify-content: center; gap: 4px; }
        .mobile-toggle span { display: block; width: 20px; height: 2px; background: var(--forest); border-radius: 3px; }

        /* ===== MAIN LAYOUT ===== */
        .main-content { padding-top: 60px; height: 100vh; display: flex; position: relative; z-index: 1; }
        .layout-split { display: flex; gap: 16px; width: 100%; padding: 16px 20px; height: calc(100vh - 60px); overflow: hidden; box-sizing: border-box; }

        /* ===== LEFT: WIZARD ===== */
        .left-panel { width: 400px; min-width: 400px; display: flex; flex-direction: column; animation: wizardEntry 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; overflow: hidden; }
        @keyframes wizardEntry { from { opacity: 0; transform: translateY(20px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }

        .wizard-card {
            background: var(--white); border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl); overflow: visible;
            border: 1px solid rgba(11,79,48,0.05);
            display: flex; flex-direction: column; height: 100%;
        }
        .wizard-header {
            background: linear-gradient(135deg, var(--forest-deep) 0%, var(--emerald) 100%);
            padding: 20px 24px 18px; position: relative; overflow: hidden; flex-shrink: 0;
        }
        .wizard-header::before { content: ''; position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: radial-gradient(circle, rgba(61,220,132,0.2) 0%, transparent 70%); border-radius: 50%; }
        .wizard-title { font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; color: var(--white); margin-bottom: 2px; position: relative; z-index: 1; }
        .wizard-subtitle { font-size: 12px; color: rgba(255,255,255,0.6); position: relative; z-index: 1; }

        .wizard-progress { display: flex; align-items: flex-start; padding: 0 24px; margin-top: -14px; position: relative; z-index: 2; flex-shrink: 0; }
        .wp-step { display: flex; flex-direction: column; align-items: center; gap: 5px; flex: 1; }
        .wp-bubble { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); position: relative; z-index: 2; }
        .wp-step.done .wp-bubble { background: var(--mint); color: var(--forest-deep); box-shadow: 0 2px 10px rgba(61,220,132,0.4); }
        .wp-step.active .wp-bubble { background: var(--white); color: var(--forest); border: 2.5px solid var(--mint); box-shadow: 0 0 0 5px rgba(61,220,132,0.12), 0 2px 10px rgba(11,79,48,0.1); animation: pulseRing 2s ease infinite; }
        @keyframes pulseRing { 0%, 100% { box-shadow: 0 0 0 5px rgba(61,220,132,0.12), 0 2px 10px rgba(11,79,48,0.1); } 50% { box-shadow: 0 0 0 8px rgba(61,220,132,0.06), 0 2px 10px rgba(11,79,48,0.1); } }
        .wp-step.pending .wp-bubble { background: var(--pearl); color: var(--mist); border: 2px solid var(--mist); }
        .wp-label { font-size: 9.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.6px; color: var(--stone); text-align: center; }
        .wp-step.pending .wp-label { color: var(--mist); }
        .wp-step.active .wp-label { color: var(--forest); font-weight: 700; }
        .wp-step.done .wp-label { color: var(--emerald); }
        .wp-connector { flex: 1; height: 2.5px; border-radius: 3px; margin: 0 -6px; margin-top: 13px; z-index: 1; transition: background 0.5s ease; }
        .wp-connector.filled { background: linear-gradient(90deg, var(--mint), var(--mint-soft)); }
        .wp-connector:not(.filled) { background: #E0E0E0; }

        .wizard-body { padding: 18px 24px 12px; flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .wizard-step-panel { animation: stepSlideIn 0.45s cubic-bezier(0.16, 1, 0.3, 1) both; flex: 1; display: flex; flex-direction: column; }
        @keyframes stepSlideIn { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }
        .wizard-step-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: var(--emerald); margin-bottom: 3px; }
        .wizard-step-title { font-family: 'Playfair Display', serif; font-size: 16px; font-weight: 600; color: var(--forest-deep); margin-bottom: 14px; }

        /* Step 1 */
        .svc-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 7px; }
        .svc-option { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: var(--radius-md); border: 2px solid var(--pearl); background: var(--white); text-decoration: none; color: var(--slate); font-size: 12px; font-weight: 600; transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1); cursor: pointer; }
        .svc-option:hover { border-color: var(--mint); background: var(--sage); transform: translateY(-1px); box-shadow: 0 3px 10px rgba(11,79,48,0.06); }
        .svc-option.selected { border-color: var(--emerald); background: linear-gradient(135deg, rgba(26,122,78,0.06), rgba(61,220,132,0.06)); box-shadow: 0 2px 10px rgba(26,122,78,0.1); }
        .svc-icon-wrap { width: 32px; height: 32px; border-radius: 8px; background: var(--pearl); display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.3s ease; }
        .svc-option.selected .svc-icon-wrap { background: linear-gradient(135deg, var(--forest), var(--emerald)); box-shadow: 0 2px 8px rgba(11,79,48,0.2); }
        .svc-icon-wrap img { width: 16px; height: 16px; transition: filter 0.3s; }
        .svc-option.selected .svc-icon-wrap img { filter: brightness(0) invert(1); }

        /* Step 2 */
        .cal-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
        .cal-nav-btn { display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 8px; background: var(--pearl); color: var(--slate); text-decoration: none; font-size: 14px; font-weight: 700; transition: all 0.2s; }
        .cal-nav-btn:hover { background: var(--sage); color: var(--forest); transform: scale(1.08); }
        .cal-month-label { font-family: 'Playfair Display', serif; font-size: 14px; font-weight: 600; color: var(--forest-deep); }
        .cal-grid { width: 100%; border-collapse: separate; border-spacing: 2px; text-align: center; margin-bottom: 14px; }
        .cal-grid th { padding: 4px 0; font-size: 9.5px; font-weight: 700; color: var(--stone); text-transform: uppercase; letter-spacing: 0.4px; }
        .cal-grid td { padding: 0; }
        .cal-day { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 30px; border-radius: 8px; text-decoration: none; color: var(--slate); font-size: 12px; font-weight: 500; transition: all 0.2s ease; }
        .cal-day:hover { background: var(--sage); color: var(--forest); }
        .cal-day.selected-day { background: linear-gradient(135deg, var(--forest), var(--emerald)); color: var(--white); font-weight: 700; box-shadow: 0 2px 10px rgba(11,79,48,0.25); }
        .time-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: var(--stone); margin-bottom: 10px; }

        /* ===== NUMERIC CLOCK PICKER ===== */
        .clock-wrap {
            background: var(--pearl);
            border-radius: 14px;
            padding: 14px 16px;
            border: 2px solid transparent;
            transition: border-color 0.2s;
        }
        .clock-wrap:focus-within { border-color: var(--mint); }

        .clock-display {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 14px;
        }

        .clock-unit {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .clock-btn {
            width: 32px; height: 28px;
            background: white;
            border: 1.5px solid rgba(11,79,48,0.12);
            border-radius: 7px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            color: var(--emerald);
            display: flex; align-items: center; justify-content: center;
            transition: all 0.15s;
            line-height: 1;
            user-select: none;
        }
        .clock-btn:hover { background: var(--sage); border-color: var(--mint); transform: scale(1.05); }
        .clock-btn:active { transform: scale(0.95); }

        .clock-value {
            font-size: 36px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: var(--forest);
            letter-spacing: -1px;
            min-width: 54px;
            text-align: center;
            line-height: 1;
        }

        .clock-sep {
            font-size: 30px;
            font-weight: 700;
            color: var(--emerald);
            padding: 0 2px;
            margin-top: -8px;
            line-height: 1;
        }

        .clock-unit-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--stone);
            opacity: 0.6;
        }

        .clock-preview {
            text-align: center;
            font-size: 12px;
            color: var(--stone);
            background: white;
            border-radius: 8px;
            padding: 7px 12px;
            border: 1.5px solid rgba(11,79,48,0.1);
        }

        .clock-preview strong {
            color: var(--forest);
            font-size: 13px;
        }

        .clock-confirm-btn {
            display: block;
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            background: linear-gradient(135deg, var(--forest), var(--emerald));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
            text-align: center;
        }
        .clock-confirm-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(11,79,48,0.2); }
        .clock-confirm-btn:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }

        .clock-range-hint {
            font-size: 10.5px;
            color: var(--stone);
            text-align: center;
            margin-top: 8px;
            opacity: 0.7;
        }

        /* Step 3 */
        .confirm-panel { display: flex; flex-direction: column; align-items: center; text-align: center; flex: 1; justify-content: center; }
        .confirm-icon { width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, var(--sage), var(--mint-soft)); display: flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 14px; animation: confirmBounce 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.15s both; }
        @keyframes confirmBounce { from { opacity: 0; transform: scale(0.5); } 50% { transform: scale(1.1); } to { opacity: 1; transform: scale(1); } }
        .confirm-heading { font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; color: var(--forest-deep); margin-bottom: 16px; }
        .confirm-details { width: 100%; max-width: 340px; background: var(--pearl); border-radius: var(--radius-lg); padding: 16px 20px; text-align: left; }
        .confirm-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; }
        .confirm-row:not(:last-child) { border-bottom: 1px solid rgba(11,79,48,0.06); }
        .confirm-row .cr-label { font-size: 10px; font-weight: 600; color: var(--stone); text-transform: uppercase; letter-spacing: 0.7px; }
        .confirm-row .cr-value { font-size: 12.5px; font-weight: 700; color: var(--slate); }

        .wizard-footer { padding: 10px 24px 18px; display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .wiz-btn { padding: 10px 20px; border-radius: var(--radius-md); font-family: 'DM Sans', sans-serif; font-size: 12px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); border: none; }
        .wiz-btn-back { background: var(--pearl); color: var(--stone); }
        .wiz-btn-back:hover { background: var(--sage); color: var(--forest); transform: translateX(-2px); }
        .wiz-btn-confirm { background: linear-gradient(135deg, var(--forest) 0%, var(--emerald) 100%); color: var(--white); box-shadow: 0 3px 14px rgba(11,79,48,0.3); flex: 1; justify-content: center; }
        .wiz-btn-confirm:hover { transform: translateY(-1px); box-shadow: 0 5px 20px rgba(11,79,48,0.35); }
        .wiz-btn-confirm:active { transform: translateY(0); }
        .wiz-btn-confirm.disabled { background: var(--mist); color: var(--white); cursor: not-allowed; box-shadow: none; opacity: 0.5; }
        .wiz-btn-confirm.disabled:hover { transform: none; box-shadow: none; }
        .wiz-spacer { flex: 1; }

        /* ===== RIGHT PANEL ===== */
        .right-panel { flex: 1; min-width: 0; display: flex; flex-direction: column; overflow: hidden; animation: fadeIn 0.6s ease 0.2s both; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

        .section-rdv-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-shrink: 0; }
        .section-rdv-title-area { display: flex; align-items: center; gap: 10px; }
        .section-rdv-header h2 { font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; color: var(--forest-deep); }
        .rdv-count-badge { background: linear-gradient(135deg, var(--forest), var(--emerald)); color: var(--white); padding: 4px 14px; border-radius: 50px; font-size: 11px; font-weight: 700; box-shadow: 0 2px 8px rgba(11,79,48,0.2); }

        /* ===== VIEW TOGGLE ===== */
        .view-toggle {
            display: flex;
            align-items: center;
            gap: 0;
            background: var(--pearl);
            border-radius: 10px;
            padding: 3px;
        }

        .view-toggle-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px; height: 28px;
            border: none;
            border-radius: 7px;
            background: transparent;
            cursor: pointer;
            transition: all 0.25s ease;
            position: relative;
        }

        .view-toggle-btn:hover { background: rgba(11,79,48,0.05); }

        .view-toggle-btn.active {
            background: var(--white);
            box-shadow: 0 1px 4px rgba(11,79,48,0.1);
        }

        .view-toggle-btn svg {
            width: 16px; height: 16px;
            fill: var(--mist);
            transition: fill 0.25s;
        }

        .view-toggle-btn:hover svg { fill: var(--stone); }
        .view-toggle-btn.active svg { fill: var(--forest); }

        /* Size slider */
        .size-slider-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-left: 8px;
        }

        .size-slider-wrap svg {
            flex-shrink: 0;
            fill: var(--mist);
        }

        .size-slider {
            -webkit-appearance: none;
            appearance: none;
            width: 70px;
            height: 4px;
            border-radius: 4px;
            background: var(--mist);
            outline: none;
            transition: background 0.2s;
        }

        .size-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 14px; height: 14px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--forest), var(--emerald));
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(11,79,48,0.3);
            transition: transform 0.15s;
        }

        .size-slider::-webkit-slider-thumb:hover { transform: scale(1.2); }

        .size-slider::-moz-range-thumb {
            width: 14px; height: 14px; border-radius: 50%; border: none;
            background: linear-gradient(135deg, var(--forest), var(--emerald));
            cursor: pointer; box-shadow: 0 1px 4px rgba(11,79,48,0.3);
        }

        .rdv-controls { display: flex; align-items: center; gap: 4px; }

        /* ===== SCROLLABLE LIST ===== */
        .rdv-scroll { flex: 1; overflow-y: auto; padding-right: 4px; }
        .rdv-scroll::-webkit-scrollbar { width: 5px; }
        .rdv-scroll::-webkit-scrollbar-track { background: transparent; }
        .rdv-scroll::-webkit-scrollbar-thumb { background: var(--mist); border-radius: 10px; }
        .rdv-scroll::-webkit-scrollbar-thumb:hover { background: var(--stone); }

        /* ===== RDV CONTAINER (switches layout) ===== */
        .rdv-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: all 0.3s ease;
        }

        /* Grid mode */
        .rdv-container.view-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        /* ===== RDV CARD ===== */
        .rdv-card {
            background: var(--white); border-radius: var(--radius-lg); overflow: hidden;
            box-shadow: var(--shadow-sm); border: 1px solid rgba(11,79,48,0.04);
            transition: all 0.3s ease; animation: cardIn 0.5s ease both;
        }
        .rdv-card:nth-child(1) { animation-delay: 0.25s; }
        .rdv-card:nth-child(2) { animation-delay: 0.30s; }
        .rdv-card:nth-child(3) { animation-delay: 0.35s; }
        .rdv-card:nth-child(4) { animation-delay: 0.40s; }
        .rdv-card:nth-child(5) { animation-delay: 0.45s; }
        .rdv-card:nth-child(6) { animation-delay: 0.50s; }
        @keyframes cardIn { from { opacity: 0; transform: translateY(12px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .rdv-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }

        /* -- List view card body -- */
        .rdv-card-body {
            padding: 14px 16px;
            display: flex;
            gap: 12px;
            align-items: center;
            transition: all 0.3s ease;
        }

        .rdv-date-block {
            min-width: 46px; text-align: center;
            background: linear-gradient(160deg, var(--sage), var(--mint-soft));
            border-radius: var(--radius-md); padding: 8px 6px; flex-shrink: 0;
            transition: all 0.3s ease;
        }
        .rdv-date-block .day-num { font-size: 20px; font-weight: 700; color: var(--forest); line-height: 1; transition: font-size 0.3s; }
        .rdv-date-block .month-abbr { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--emerald); margin-top: 1px; transition: font-size 0.3s; }

        .rdv-meta { flex: 1; min-width: 0; transition: all 0.3s ease; }
        .rdv-service-name { font-size: 13px; font-weight: 700; color: var(--slate); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; transition: font-size 0.3s; }
        .rdv-detail-line { font-size: 11px; color: var(--stone); margin-bottom: 5px; transition: font-size 0.3s; }
        .rdv-status-pill { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 50px; font-size: 10px; font-weight: 700; transition: all 0.3s; }
        .rdv-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }

        .status-en-attente { background: #FFF8E1; color: #F57F17; }
        .status-en-attente .rdv-dot { background: #F39C12; }
        .status-confirme { background: #E8F5E9; color: #2E7D32; }
        .status-confirme .rdv-dot { background: #27AE60; }
        .status-annule { background: #FFEBEE; color: #C62828; }
        .status-annule .rdv-dot { background: #E74C3C; }

        .rdv-card-actions { display: flex; border-top: 1px solid rgba(11,79,48,0.06); transition: all 0.3s; }
        .rdv-card-actions a { flex: 1; text-align: center; padding: 9px; text-decoration: none; font-size: 11px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
        .rdv-action-modify { color: var(--emerald); border-right: 1px solid rgba(11,79,48,0.06); }
        .rdv-action-modify:hover { background: var(--sage); }
        .rdv-action-delete { color: var(--danger); }
        .rdv-action-delete:hover { background: #FFF0F0; }

        /* -- Grid view overrides -- */
        .view-grid .rdv-card-body {
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 8px;
        }

        .view-grid .rdv-date-block {
            min-width: 56px;
            padding: 10px 14px;
        }

        .view-grid .rdv-service-name {
            white-space: normal;
            text-align: center;
        }

        .view-grid .rdv-meta {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* -- Size scaling via CSS custom property -- */
        .rdv-container[data-scale="1"] .rdv-date-block .day-num { font-size: 18px; }
        .rdv-container[data-scale="1"] .rdv-date-block .month-abbr { font-size: 8px; }
        .rdv-container[data-scale="1"] .rdv-service-name { font-size: 12px; }
        .rdv-container[data-scale="1"] .rdv-detail-line { font-size: 10px; }
        .rdv-container[data-scale="1"] .rdv-card-body { padding: 10px 12px; gap: 8px; }
        .rdv-container[data-scale="1"] .rdv-date-block { padding: 6px 5px; min-width: 38px; }
        .rdv-container[data-scale="1"] .rdv-card-actions a { padding: 7px; font-size: 10px; }
        .rdv-container[data-scale="1"] .rdv-status-pill { font-size: 9px; padding: 2px 8px; }

        .rdv-container[data-scale="3"] .rdv-date-block .day-num { font-size: 24px; }
        .rdv-container[data-scale="3"] .rdv-date-block .month-abbr { font-size: 10px; }
        .rdv-container[data-scale="3"] .rdv-service-name { font-size: 15px; }
        .rdv-container[data-scale="3"] .rdv-detail-line { font-size: 12px; margin-bottom: 7px; }
        .rdv-container[data-scale="3"] .rdv-card-body { padding: 18px 20px; gap: 14px; }
        .rdv-container[data-scale="3"] .rdv-date-block { padding: 12px 10px; min-width: 56px; }
        .rdv-container[data-scale="3"] .rdv-card-actions a { padding: 11px; font-size: 12px; }
        .rdv-container[data-scale="3"] .rdv-status-pill { font-size: 11px; padding: 4px 12px; }

        .rdv-container.view-grid[data-scale="1"] { grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .rdv-container.view-grid[data-scale="2"] { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .rdv-container.view-grid[data-scale="3"] { grid-template-columns: 1fr 1fr; gap: 12px; }

        .empty-rdv-box {
            text-align: center; padding: 40px 24px; color: var(--stone); font-size: 13px;
            background: var(--white); border-radius: var(--radius-lg); border: 2px dashed rgba(11,79,48,0.1);
        }
        .empty-rdv-box::before { content: '\1F4C5'; display: block; font-size: 30px; margin-bottom: 8px; }
        .view-grid .empty-rdv-box { grid-column: 1 / -1; }

        /* ===== FLASH ===== */
        .flash-messages { padding: 0 28px; padding-top: 8px; flex-shrink: 0; }
        .message-success, .message-error { padding: 10px 16px; border-radius: var(--radius-md); margin-bottom: 8px; font-size: 12px; font-weight: 500; box-shadow: var(--shadow-sm); border: none; animation: slideDown 0.4s ease; }
        .message-success { background: linear-gradient(135deg, #d4edda, #c3e6cb); color: #155724; }
        .message-error { background: linear-gradient(135deg, #f8d7da, #f5c6cb); color: #721c24; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 960px) {
            body { height: auto; overflow: auto; }
            .layout-split { flex-direction: column; height: auto; overflow: visible; }
            .left-panel { width: 100%; min-width: 0; }
            .wizard-card { height: auto; }
            .right-panel { overflow: visible; }
            .rdv-scroll { overflow: visible; }
        }
        @media (max-width: 768px) {
            .mobile-toggle { display: flex; }
            .nav-links { display: none; position: absolute; top: 60px; left: 0; right: 0; background: rgba(255,255,255,0.97); backdrop-filter: blur(20px); flex-direction: column; padding: 10px 16px 16px; border-bottom: 1px solid rgba(11,79,48,0.07); box-shadow: var(--shadow-md); gap: 2px; }
            .nav-links.open { display: flex; }
            .nav-links li a { padding: 10px 14px; }
            .navbar { padding: 0 16px; }
            .layout-split { padding: 14px 16px; }
            .svc-grid { grid-template-columns: 1fr; }
            .nav-search { display: none; }
            .rdv-container.view-grid { grid-template-columns: 1fr; }
            .rdv-container.view-grid[data-scale="1"] { grid-template-columns: repeat(2, 1fr); }
            .size-slider-wrap { display: none; }
        }
        /* ===== MULTI-SERVICE SELECTION ===== */
        .multi-hint {
            font-size: 11.5px; color: var(--stone);
            background: linear-gradient(135deg, rgba(61,220,132,0.08), rgba(26,122,78,0.06));
            border: 1px solid rgba(61,220,132,0.2);
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 12px;
            display: flex; align-items: center; gap: 8px;
        }
        .multi-hint::before { content: '☑'; font-size: 14px; }

        .svc-option {
            position: relative;
        }
        .svc-option .svc-check {
            position: absolute;
            top: 7px; right: 7px;
            width: 18px; height: 18px;
            border-radius: 50%;
            border: 2px solid rgba(11,79,48,0.2);
            background: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 10px;
            color: white;
            transition: all 0.2s;
        }
        .svc-option.selected .svc-check {
            background: var(--emerald);
            border-color: var(--emerald);
        }
        .svc-option.selected .svc-check::after { content: '✓'; }

        .multi-counter {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid rgba(11,79,48,0.08);
        }
        .multi-counter-text {
            font-size: 12px; color: var(--stone);
        }
        .multi-counter-text strong { color: var(--forest); }

        .multi-continue-btn {
            padding: 9px 20px;
            background: linear-gradient(135deg, var(--forest), var(--emerald));
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .multi-continue-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(11,79,48,0.2); }
        .multi-continue-btn:disabled { opacity: 0.35; cursor: not-allowed; transform: none; box-shadow: none; }

        /* Chained slots in step 3 */
        .chain-list { display: flex; flex-direction: column; gap: 8px; margin: 14px 0; }
        .chain-item {
            display: flex; align-items: center; gap: 12px;
            background: var(--pearl);
            border-radius: 10px;
            padding: 10px 14px;
            border: 1.5px solid rgba(11,79,48,0.08);
        }
        .chain-num {
            width: 24px; height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--forest), var(--emerald));
            color: white;
            font-size: 11px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .chain-info { flex: 1; }
        .chain-name { font-size: 13px; font-weight: 600; color: var(--slate); }
        .chain-time { font-size: 11px; color: var(--stone); margin-top: 1px; }
        .chain-badge {
            font-size: 12px; font-weight: 700;
            color: var(--forest);
            background: rgba(61,220,132,0.15);
            padding: 3px 9px;
            border-radius: 50px;
        }
        .chain-connector {
            width: 2px; height: 8px;
            background: linear-gradient(to bottom, var(--emerald), rgba(61,220,132,0.3));
            margin: 0 auto;
            margin-left: 19px;
        }

        /* ===== MODE PICKER ===== */
        .mode-picker { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin: 16px 0; }
        .mode-card {
            display: flex; flex-direction: column; align-items: center;
            gap: 10px; padding: 20px 16px;
            background: var(--pearl);
            border: 2px solid transparent;
            border-radius: 14px;
            text-decoration: none; color: var(--slate);
            transition: all 0.25s cubic-bezier(0.16,1,0.3,1);
            text-align: center;
        }
        .mode-card:hover {
            border-color: var(--mint);
            background: var(--sage);
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(11,79,48,0.1);
        }
        .mode-card-icon {
            width: 52px; height: 52px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px;
            background: linear-gradient(135deg, var(--sage), var(--mint-soft));
        }
        .mode-card-title { font-size: 13.5px; font-weight: 700; color: var(--forest); }
        .mode-card-desc  { font-size: 11px; color: var(--stone); line-height: 1.4; }

        /* ===== AVAILABLE TIME CHIPS (Option B) ===== */
        .avail-times-label {
            font-size: 9px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1.2px; color: var(--stone); margin: 14px 0 8px;
        }
        .avail-times-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;
        }
        .avail-time-chip {
            display: flex; align-items: center; justify-content: center;
            padding: 8px 4px; border-radius: 8px;
            background: var(--pearl); border: 2px solid transparent;
            text-decoration: none; color: var(--slate);
            font-size: 12px; font-weight: 600;
            font-variant-numeric: tabular-nums;
            transition: all 0.2s;
        }
        .avail-time-chip:hover { border-color: var(--mint); background: var(--sage); transform: translateY(-1px); }
        .avail-time-chip.selected { border-color: var(--emerald); background: linear-gradient(135deg, rgba(26,122,78,0.08), rgba(61,220,132,0.08)); color: var(--forest); }
        .avail-times-loading { font-size: 12px; color: var(--stone); padding: 16px 0; text-align: center; }
        .avail-times-empty   { font-size: 12px; color: #e74c3c; padding: 12px; background: #fde8e8; border-radius: 8px; text-align: center; margin-top: 8px; }

        /* ===== Option A: highlighted calendar days ===== */
        .cal-day.available  { background: rgba(61,220,132,0.15); color: var(--forest); font-weight: 700; }
        .cal-day.unavailable{ opacity: 0.25; pointer-events: none; }
        .cal-day.loading-av { opacity: 0.5; }
        .avail-legend {
            display: flex; gap: 14px; margin: 8px 0 4px;
            font-size: 10.5px; color: var(--stone); align-items: center;
        }
        .avail-legend span { display: flex; align-items: center; gap: 4px; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; }

        /* ===== MINI MAP ===== */
        .map-panel {
            width: 280px; min-width: 280px;
            display: flex; flex-direction: column;
            animation: fadeIn 0.6s ease 0.3s both;
            height: 100%;
        }
        .map-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(11,79,48,0.05);
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .map-card-header {
            padding: 14px 16px 12px;
            border-bottom: 1px solid rgba(11,79,48,0.06);
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--forest-deep) 0%, var(--emerald) 100%);
        }
        .map-card-title { font-size: 13px; font-weight: 700; color: white; display: flex; align-items: center; gap: 8px; }
        .map-card-title svg { width:16px; height:16px; stroke:rgba(255,255,255,0.85); fill:none; stroke-width:2; }
        .map-card-subtitle { font-size: 11px; color: rgba(255,255,255,0.7); margin-top: 3px; }
        .map-locating {
            display: flex; align-items: center; gap: 6px;
            font-size: 11px; color: rgba(255,255,255,0.8); margin-top: 6px;
        }
        .map-locating-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: white;
            animation: pulseDot 1.4s ease infinite;
        }
        #municipalityMap {
            flex: 1;
            min-height: 380px;
            z-index: 1;
        }
        .map-legend {
            padding: 10px 14px;
            border-top: 1px solid rgba(11,79,48,0.08);
            display: flex; gap: 14px; flex-wrap: wrap;
            flex-shrink: 0;
        }
        .map-legend-item {
            display: flex; align-items: center; gap: 5px;
            font-size: 10.5px; color: var(--stone);
        }
        .map-legend-dot {
            width: 10px; height: 10px; border-radius: 50%;
        }
        @media(max-width:1100px) { .map-panel { display:none; } }

        /* ===== SUGGESTION BOX ===== */
        .suggest-wrap {
            position: relative;
            margin-bottom: 18px;
        }
        .suggest-input-row {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--white);
            border: 2px solid rgba(11,79,48,0.15);
            border-radius: 12px;
            padding: 10px 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .suggest-input-row:focus-within {
            border-color: var(--emerald);
            box-shadow: 0 0 0 3px rgba(26,122,78,0.1);
        }
        .suggest-input-row .suggest-icon {
            font-size: 18px;
            flex-shrink: 0;
            opacity: 0.6;
        }
        .suggest-input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--slate);
            background: transparent;
        }
        .suggest-input::placeholder { color: var(--mist); }
        .suggest-spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(26,122,78,0.2);
            border-top-color: var(--emerald);
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            display: none;
            flex-shrink: 0;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .suggest-spinner.active { display: block; }

        .suggest-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            left: 0; right: 0;
            background: var(--white);
            border: 1.5px solid rgba(11,79,48,0.12);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(11,79,48,0.1);
            overflow: hidden;
            z-index: 200;
            display: none;
            animation: dropIn 0.18s ease;
        }
        @keyframes dropIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
        .suggest-dropdown.open { display: block; }

        .suggest-label {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--stone);
            padding: 10px 14px 6px;
            opacity: 0.6;
        }

        .suggest-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            cursor: pointer;
            transition: background 0.15s;
            text-decoration: none;
            color: inherit;
            border-top: 1px solid rgba(11,79,48,0.05);
        }
        .suggest-item:hover, .suggest-item:focus { background: rgba(26,122,78,0.05); }
        .suggest-item-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .suggest-item-icon img { width: 18px; height: 18px; }
        .suggest-item-name { font-size: 13px; font-weight: 600; color: var(--slate); }
        .suggest-item-desc { font-size: 11px; color: var(--stone); margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }
        .suggest-confidence {
            margin-left: auto;
            font-size: 10px;
            font-weight: 700;
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            color: var(--forest);
            padding: 2px 8px;
            border-radius: 50px;
            flex-shrink: 0;
        }
        .suggest-no-result {
            padding: 14px;
            font-size: 12.5px;
            color: var(--stone);
            text-align: center;
        }
        .suggest-hint {
            font-size: 11.5px;
            color: var(--stone);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .suggest-hint::before { content: '💡'; font-size: 13px; }
    </style>
<body>

    <!-- ====== NAVBAR ====== -->
    <nav class="navbar" id="navbar">
        <a class="nav-brand" href="#">
            <img src="../../assets/icons/logo.png" alt="Logo">
            <span class="nav-brand-text">Smart <span>Municipality</span></span>
        </a>
        <button class="mobile-toggle" onclick="document.querySelector('.nav-links').classList.toggle('open')">
            <span></span><span></span><span></span>
        </button>
        <ul class="nav-links">
            <li><a href="profil.php"><img src="../../assets/icons/profil.svg" alt=""> <span>Profil</span></a></li>
            <li><a href="evenements.php"><img src="../../assets/icons/alertes.svg" alt=""> <span>Événements</span></a></li>
            <li><a href="carte.php"><img src="../../assets/icons/carte.svg" alt=""> <span>Carte</span></a></li>
            <li><a href="blog.php"><img src="../../assets/icons/blog.svg" alt=""> <span>Blog</span></a></li>
            <li><a href="services.php"><img src="../../assets/icons/services.svg" alt=""> <span>Services</span></a></li>
            <li><a href="rendez-vous.php" class="active"><img src="../../assets/icons/rdv.svg" alt=""> <span>Rendez-vous</span></a></li>
        </ul>
        <div class="nav-right">
            <div class="nav-search"><img src="../../assets/icons/search.svg" alt=""><input type="text" placeholder="Rechercher..."></div>
            <a href="parametres.php" class="nav-settings-btn"><img src="../../assets/icons/parametres.svg" alt=""></a>
            <div class="nav-user"><img src="../../assets/icons/avatar.svg" alt=""><span>Eliza Thorne</span></div>
        </div>
    </nav>

    <!-- ====== MAIN ====== -->
    <div class="main-content">
        <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
        <div class="flash-messages">
            <?php if (isset($_SESSION['success'])): ?><div class="message-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div><?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?><div class="message-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div><?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="layout-split">

            <!-- LEFT: WIZARD -->
            <div class="left-panel">
                <div class="wizard-card">
                    <div class="wizard-header">
                        <div class="wizard-title">Prendre un Rendez-vous</div>
                        <div class="wizard-subtitle">
                            <?php if ($step == 1): ?>Choisissez le(s) service(s) dont vous avez besoin
                            <?php elseif ($step == 'mode'): ?>Comment souhaitez-vous réserver ?
                            <?php elseif ($step == 2 && $mode == 'A'): ?>Choisissez l'heure — le calendrier s'adaptera
                            <?php elseif ($step == 2 && $mode == 'B'): ?>Choisissez la date — les créneaux disponibles apparaîtront
                            <?php else: ?>Vérifiez et confirmez votre rendez-vous
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="wizard-progress">
                        <div class="wp-step <?php echo ($step != 1) ? 'done' : 'active'; ?>">
                            <div class="wp-bubble"><?php echo ($step != 1) ? '&#10004;' : '1'; ?></div>
                            <div class="wp-label">Service</div>
                        </div>
                        <div class="wp-connector <?php echo ($step == 3) ? 'filled' : ''; ?>"></div>
                        <div class="wp-step <?php echo ($step == 3) ? 'done' : (($step == 2 || $step == 'mode') ? 'active' : 'pending'); ?>">
                            <div class="wp-bubble"><?php echo ($step == 3) ? '&#10004;' : '2'; ?></div>
                            <div class="wp-label">Date & Heure</div>
                        </div>
                        <div class="wp-connector <?php echo ($step == 3) ? 'filled' : ''; ?>"></div>
                        <div class="wp-step <?php echo ($step == 3) ? 'active' : 'pending'; ?>">
                            <div class="wp-bubble">3</div>
                            <div class="wp-label">Confirmation</div>
                        </div>
                    </div>

                    <div class="wizard-body">
                        <?php if ($step == 1): ?>
                        <div class="wizard-step-panel">
                            <div class="wizard-step-label">Étape 1</div>
                            <div class="wizard-step-title">Quel(s) service(s) souhaitez-vous ?</div>

                            <!-- SUGGESTION BOX -->
                            <div class="suggest-hint">Décrivez votre besoin et on vous suggère le bon service</div>
                            <div class="suggest-wrap" id="suggestWrap">
                                <div class="suggest-input-row">
                                    <span class="suggest-icon">🔍</span>
                                    <input type="text" class="suggest-input" id="suggestInput" placeholder="Ex: je veux légaliser un document..." autocomplete="off">
                                    <div class="suggest-spinner" id="suggestSpinner"></div>
                                </div>
                                <div class="suggest-dropdown" id="suggestDropdown">
                                    <div class="suggest-label">Suggestions</div>
                                    <div id="suggestResults"></div>
                                </div>
                            </div>

                            <div class="multi-hint">Sélectionnez un ou plusieurs services — nous optimiserons votre visite</div>

                            <div class="svc-grid" id="svcGrid">
                                <?php foreach ($categories as $cat): ?>
                                    <div class="svc-option <?php echo in_array((int)$cat['id'], $selectedCatIds) ? 'selected' : ''; ?>"
                                         data-id="<?php echo $cat['id']; ?>"
                                         onclick="toggleCat(<?php echo $cat['id']; ?>, this)">
                                        <div class="svc-check"></div>
                                        <div class="svc-icon-wrap"><img src="../../assets/icons/<?php echo htmlspecialchars($cat['icone']); ?>" alt=""></div>
                                        <span><?php echo htmlspecialchars($cat['nom']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="multi-counter">
                                <span class="multi-counter-text">
                                    <strong id="selCount"><?php echo count($selectedCatIds); ?></strong> service(s) sélectionné(s)
                                </span>
                            </div>
                        </div>
                        <?php elseif ($step == 'mode'): ?>
                        <!-- ===== MODE PICKER ===== -->
                        <div class="wizard-step-panel">
                            <div class="wizard-step-label">Étape 2</div>
                            <div class="wizard-step-title">Comment voulez-vous réserver ?</div>
                            <div class="mode-picker">
                                <a href="rendez-vous.php?<?php echo catsQuery($selectedCatIds, 'mode=A'); ?>"
                                   class="mode-card">
                                    <div class="mode-card-icon">🕐</div>
                                    <div class="mode-card-title">Je choisis l'heure</div>
                                    <div class="mode-card-desc">Le calendrier vous montre uniquement les dates disponibles pour votre visite</div>
                                </a>
                                <a href="rendez-vous.php?<?php echo catsQuery($selectedCatIds, 'mode=B'); ?>"
                                   class="mode-card">
                                    <div class="mode-card-icon">📅</div>
                                    <div class="mode-card-title">Je choisis la date</div>
                                    <div class="mode-card-desc">La plateforme vous propose les créneaux disponibles pour cette date</div>
                                </a>
                            </div>
                        </div>

                        <?php elseif ($step == 2 && $mode == 'A'): ?>
                        <!-- ===== OPTION A: clock → calendar highlights valid dates ===== -->
                        <div class="wizard-step-panel">
                            <div class="wizard-step-label">Option A — Choisissez l'heure de départ</div>
                            <div class="wizard-step-title">Le calendrier s'adapte en temps réel</div>
                            <?php if (count($selectedCatIds) > 1): ?>
                            <div class="multi-hint" style="margin-bottom:10px;">
                                <?php echo count($selectedCatIds); ?> services — départ à l'heure choisie, enchaînés toutes les 20 min
                            </div>
                            <?php endif; ?>

                            <div class="time-label">Heure de départ</div>
                            <div class="clock-wrap">
                                <div class="clock-display">
                                    <div class="clock-unit">
                                        <button type="button" class="clock-btn" onclick="changeHour(1)">▲</button>
                                        <div class="clock-value" id="clockHourDisplay">09</div>
                                        <button type="button" class="clock-btn" onclick="changeHour(-1)">▼</button>
                                        <div class="clock-unit-label">Heure</div>
                                    </div>
                                    <div class="clock-sep">:</div>
                                    <div class="clock-unit">
                                        <button type="button" class="clock-btn" onclick="changeMin(5)">▲</button>
                                        <div class="clock-value" id="clockMinDisplay">00</div>
                                        <button type="button" class="clock-btn" onclick="changeMin(-5)">▼</button>
                                        <div class="clock-unit-label">Min</div>
                                    </div>
                                </div>
                                <div class="clock-preview">Départ à <strong id="clockPreviewText">09:00</strong></div>
                                <div class="clock-range-hint">Horaires disponibles : 08h00 – 17h00</div>
                            </div>

                            <!-- Calendar — days colored by AJAX -->
                            <?php
                            $month    = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
                            $year     = isset($_GET['year'])  ? (int)$_GET['year']  : date('Y');
                            $catQS    = catsQuery($selectedCatIds, 'mode=A');
                            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                            $firstDay    = date('N', mktime(0, 0, 0, $month, 1, $year));
                            $prevMonth = $month - 1; $prevYear = $year;
                            if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
                            $nextMonth = $month + 1; $nextYear = $year;
                            if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
                            ?>
                            <div class="cal-nav" style="margin-top:14px;">
                                <a href="rendez-vous.php?<?php echo $catQS; ?>&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="cal-nav-btn">&larr;</a>
                                <span class="cal-month-label"><?php echo $moisComplet[$month] . ' ' . $year; ?></span>
                                <a href="rendez-vous.php?<?php echo $catQS; ?>&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="cal-nav-btn">&rarr;</a>
                            </div>
                            <div class="avail-legend">
                                <span><span class="legend-dot" style="background:rgba(61,220,132,0.5);"></span> Disponible</span>
                                <span><span class="legend-dot" style="background:#ccc;"></span> Indisponible</span>
                            </div>
                            <table class="cal-grid" id="calGridA">
                                <thead><tr><th>Lu</th><th>Ma</th><th>Me</th><th>Je</th><th>Ve</th><th>Sa</th><th>Di</th></tr></thead>
                                <tbody><tr>
                                <?php
                                $dayCount = 0;
                                for ($i = 1; $i < $firstDay; $i++) { echo "<td></td>"; $dayCount++; }
                                for ($day = 1; $day <= $daysInMonth; $day++) {
                                    $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                    echo '<td><a href="#" class="cal-day loading-av" data-date="' . $dateStr . '" data-href="rendez-vous.php?' . $catQS . '&date=' . $dateStr . '&month=' . $month . '&year=' . $year . '&heure=HEURE">' . $day . '</a></td>';
                                    $dayCount++;
                                    if ($dayCount % 7 == 0 && $day != $daysInMonth) echo "</tr><tr>";
                                }
                                while ($dayCount % 7 != 0) { echo "<td></td>"; $dayCount++; }
                                ?>
                                </tr></tbody>
                            </table>
                        </div>

                        <?php elseif ($step == 2 && $mode == 'B'): ?>
                        <!-- ===== OPTION B: calendar → shows available times ===== -->
                        <div class="wizard-step-panel">
                            <div class="wizard-step-label">Option B — Choisissez la date</div>
                            <div class="wizard-step-title">Les créneaux disponibles apparaîtront</div>
                            <?php if (count($selectedCatIds) > 1): ?>
                            <div class="multi-hint" style="margin-bottom:10px;">
                                <?php echo count($selectedCatIds); ?> services — seuls les créneaux où toute la chaîne est libre sont affichés
                            </div>
                            <?php endif; ?>
                            <?php
                            $month    = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
                            $year     = isset($_GET['year'])  ? (int)$_GET['year']  : date('Y');
                            $catQS    = catsQuery($selectedCatIds, 'mode=B');
                            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                            $firstDay    = date('N', mktime(0, 0, 0, $month, 1, $year));
                            $prevMonth = $month - 1; $prevYear = $year;
                            if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
                            $nextMonth = $month + 1; $nextYear = $year;
                            if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
                            ?>
                            <div class="cal-nav">
                                <a href="rendez-vous.php?<?php echo $catQS; ?>&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="cal-nav-btn">&larr;</a>
                                <span class="cal-month-label"><?php echo $moisComplet[$month] . ' ' . $year; ?></span>
                                <a href="rendez-vous.php?<?php echo $catQS; ?>&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="cal-nav-btn">&rarr;</a>
                            </div>
                            <table class="cal-grid">
                                <thead><tr><th>Lu</th><th>Ma</th><th>Me</th><th>Je</th><th>Ve</th><th>Sa</th><th>Di</th></tr></thead>
                                <tbody><tr>
                                <?php
                                $dayCount = 0;
                                for ($i = 1; $i < $firstDay; $i++) { echo "<td></td>"; $dayCount++; }
                                for ($day = 1; $day <= $daysInMonth; $day++) {
                                    $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                    $isSel = ($selectedDate == $dateStr) ? 'selected-day' : '';
                                    echo '<td><a href="rendez-vous.php?' . $catQS . '&date=' . $dateStr . '&month=' . $month . '&year=' . $year . '" class="cal-day ' . $isSel . '">' . $day . '</a></td>';
                                    $dayCount++;
                                    if ($dayCount % 7 == 0 && $day != $daysInMonth) echo "</tr><tr>";
                                }
                                while ($dayCount % 7 != 0) { echo "<td></td>"; $dayCount++; }
                                ?>
                                </tr></tbody>
                            </table>

                            <?php if (!empty($selectedDate)): ?>
                            <div class="avail-times-label">Créneaux disponibles pour <?php echo date('d/m/Y', strtotime($selectedDate)); ?></div>
                            <?php if (!empty($availableTimes)): ?>
                            <div class="avail-times-grid" id="availTimesGrid">
                                <?php foreach ($availableTimes as $t): ?>
                                    <a href="rendez-vous.php?<?php echo $catQS; ?>&date=<?php echo $selectedDate; ?>&heure=<?php echo urlencode($t . ':00'); ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>"
                                       class="avail-time-chip <?php echo (substr($selectedHeure, 0, 5) == $t) ? 'selected' : ''; ?>">
                                        <?php echo $t; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="avail-times-empty">
                                Aucun créneau disponible pour cette date.<br>
                                <small>Choisissez une autre date dans le calendrier.</small>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="wizard-step-panel">
                            <div class="confirm-panel">
                                <div class="confirm-icon">&#128197;</div>
                                <div class="confirm-heading">
                                    <?php echo count($chainedSlots) > 1 ? 'Votre visite optimisée !' : 'Tout est prêt !'; ?>
                                </div>

                                <?php if (count($chainedSlots) > 1): ?>
                                <!-- MULTI-SERVICE CHAIN -->
                                <div style="font-size:12px;color:var(--stone);margin-bottom:12px;">
                                    <?php $ts = strtotime($selectedDate); echo $joursSemaine[date('w',$ts)] . ', ' . date('d',$ts) . ' ' . $moisComplet[date('n',$ts)]; ?>
                                    — <?php echo count($chainedSlots); ?> services enchaînés
                                </div>
                                <div class="chain-list">
                                    <?php foreach ($chainedSlots as $i => $slot): ?>
                                        <?php if ($i > 0): ?><div class="chain-connector"></div><?php endif; ?>
                                        <div class="chain-item">
                                            <div class="chain-num"><?php echo $i + 1; ?></div>
                                            <div class="chain-info">
                                                <div class="chain-name"><?php echo htmlspecialchars($slot['cat_nom']); ?></div>
                                                <div class="chain-time"><?php echo $joursSemaine[date('w', strtotime($selectedDate))]; ?> · <?php echo $selectedDate; ?></div>
                                            </div>
                                            <div class="chain-badge"><?php echo $slot['heure_display']; ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <!-- SINGLE SERVICE (unchanged) -->
                                <div class="confirm-details">
                                    <div class="confirm-row">
                                        <span class="cr-label">Service</span>
                                        <span class="cr-value"><?php echo htmlspecialchars($selectedCatNames[0] ?? ''); ?></span>
                                    </div>
                                    <div class="confirm-row">
                                        <span class="cr-label">Date</span>
                                        <span class="cr-value"><?php $ts = strtotime($selectedDate); echo $joursSemaine[date('w', $ts)] . ', ' . date('d', $ts) . ' ' . $moisComplet[date('n', $ts)]; ?></span>
                                    </div>
                                    <div class="confirm-row">
                                        <span class="cr-label">Heure</span>
                                        <span class="cr-value"><?php echo htmlspecialchars($chainedSlots[0]['heure_display'] ?? $selectedHeure); ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="wizard-footer">
                        <?php
                        $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
                        $year  = isset($_GET['year'])  ? (int)$_GET['year']  : date('Y');
                        $catQS = catsQuery($selectedCatIds);
                        ?>
                        <?php if ($step == 1): ?>
                            <div class="wiz-spacer"></div>
                        <?php elseif ($step == 'mode'): ?>
                            <a href="rendez-vous.php" class="wiz-btn wiz-btn-back">&larr; Services</a>
                        <?php elseif ($step == 2): ?>
                            <a href="rendez-vous.php?<?php echo $catQS; ?>" class="wiz-btn wiz-btn-back">&larr; Mode</a>
                        <?php else: ?>
                            <a href="rendez-vous.php?<?php echo catsQuery($selectedCatIds, 'mode=' . $mode . '&month=' . $month . '&year=' . $year . ($mode == 'B' && !empty($selectedDate) ? '&date=' . $selectedDate : '')); ?>" class="wiz-btn wiz-btn-back">&larr; Modifier</a>
                        <?php endif; ?>

                        <?php if ($step == 3): ?>
                            <form id="rdvForm" action="/smart-municipality/controllers/RendezVousController.php" method="POST" style="flex:1;display:flex;">
                                <input type="hidden" name="action"   value="create_multi">
                                <input type="hidden" name="date_rdv" value="<?php echo htmlspecialchars($selectedDate); ?>">
                                <?php foreach ($chainedSlots as $slot): ?>
                                    <input type="hidden" name="slots[]" value="<?php echo $slot['cat_id'] . '|' . $slot['heure']; ?>">
                                <?php endforeach; ?>
                                <button type="button" class="wiz-btn wiz-btn-confirm" onclick="confirmRdv()">&#10004; Confirmer</button>
                            </form>
                        <?php elseif ($step == 1): ?>
                            <button id="footerContinueBtn"
                                    class="wiz-btn wiz-btn-confirm <?php echo empty($selectedCatIds) ? 'disabled' : ''; ?>"
                                    <?php echo empty($selectedCatIds) ? 'disabled' : ''; ?>
                                    onclick="goToStep2()">
                                Choisir les services &rarr;
                            </button>
                        <?php elseif ($step == 'mode'): ?>
                            <button class="wiz-btn wiz-btn-confirm disabled" disabled>Choisissez une option &rarr;</button>
                        <?php elseif ($step == 2 && $mode == 'A'): ?>
                            <button class="wiz-btn wiz-btn-confirm" onclick="confirmTime()">
                                Voir les dates disponibles &rarr;
                            </button>
                        <?php elseif ($step == 2 && $mode == 'B'): ?>
                            <button class="wiz-btn wiz-btn-confirm disabled" disabled>Choisissez un créneau &rarr;</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- RIGHT: MES RENDEZ-VOUS -->
            <div class="right-panel">
                <div class="section-rdv-header">
                    <div class="section-rdv-title-area">
                        <h2>Mes Rendez-vous</h2>
                        <span class="rdv-count-badge"><?php echo count($mesRdv); ?> rdv</span>
                    </div>

                    <div class="rdv-controls">
                        <!-- View toggle: list / grid -->
                        <div class="view-toggle">
                            <button class="view-toggle-btn active" data-view="list" onclick="setView('list')" title="Vue liste">
                                <svg viewBox="0 0 16 16"><rect x="1" y="2" width="14" height="2.5" rx="0.8"/><rect x="1" y="6.75" width="14" height="2.5" rx="0.8"/><rect x="1" y="11.5" width="14" height="2.5" rx="0.8"/></svg>
                            </button>
                            <button class="view-toggle-btn" data-view="grid" onclick="setView('grid')" title="Vue grille">
                                <svg viewBox="0 0 16 16"><rect x="1" y="1" width="6" height="6" rx="1.2"/><rect x="9" y="1" width="6" height="6" rx="1.2"/><rect x="1" y="9" width="6" height="6" rx="1.2"/><rect x="9" y="9" width="6" height="6" rx="1.2"/></svg>
                            </button>
                        </div>

                        <!-- Size slider -->
                        <div class="size-slider-wrap">
                            <svg width="12" height="12" viewBox="0 0 16 16"><rect x="3" y="3" width="10" height="10" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>
                            <input type="range" min="1" max="3" value="2" class="size-slider" id="sizeSlider" oninput="setScale(this.value)">
                            <svg width="16" height="16" viewBox="0 0 16 16"><rect x="1" y="1" width="14" height="14" rx="2.5" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>
                        </div>
                    </div>
                </div>

                <div class="rdv-scroll">
                    <div class="rdv-container view-list" id="rdvContainer" data-scale="2">
                        <?php if (empty($mesRdv)): ?>
                            <div class="empty-rdv-box">Aucun rendez-vous pour le moment.</div>
                        <?php else: ?>
                            <?php foreach ($mesRdv as $item):
                                $ts = strtotime($item['date_rdv']);
                                $dayNum = date('d', $ts);
                                $monthShort = $moisNoms[date('n', $ts)];
                                $dayName = $joursSemaine[date('w', $ts)];
                                $statusClass = 'status-' . str_replace('_', '-', $item['statut']);
                                $statusLabel = str_replace('_', ' ', ucfirst($item['statut']));
                            ?>
                                <div class="rdv-card">
                                    <div class="rdv-card-body">
                                        <div class="rdv-date-block">
                                            <div class="day-num"><?php echo $dayNum; ?></div>
                                            <div class="month-abbr"><?php echo $monthShort; ?></div>
                                        </div>
                                        <div class="rdv-meta">
                                            <div class="rdv-service-name"><?php echo htmlspecialchars($item['service_nom']); ?></div>
                                            <div class="rdv-detail-line"><?php echo $dayName; ?> &bull; <?php echo substr($item['heure'], 0, 5); ?></div>
                                            <span class="rdv-status-pill <?php echo $statusClass; ?>">
                                                <span class="rdv-dot"></span>
                                                <?php echo $statusLabel; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="rdv-card-actions">
                                        <?php if ($item['statut'] == 'en_attente'): ?>
                                            <a href="rendez-vous.php?categorie_id=<?php echo $item['categorie_id']; ?>&date=<?php echo $item['date_rdv']; ?>&heure=<?php echo substr($item['heure'], 0, 5); ?>&month=<?php echo date('n', $ts); ?>&year=<?php echo date('Y', $ts); ?>" class="rdv-action-modify">&#9998; Modifier</a>
                                        <?php else: ?>
                                            <a href="#" class="rdv-action-modify" style="color:#ccc;cursor:default;">&#9998; Modifier</a>
                                        <?php endif; ?>
                                        <a href="#" class="rdv-action-delete" onclick="deleteMyRdv(<?php echo $item['id']; ?>)">&#10006; Supprimer</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- MAP: MAIRIES PROCHES -->
            <div class="map-panel">
                <div class="map-card">
                    <div class="map-card-header">
                        <div class="map-card-title">
                            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            Mairies à proximité
                        </div>
                        <div class="map-card-subtitle">Cliquez sur un marqueur pour prendre RDV</div>
                        <div class="map-locating" id="mapLocating">
                            <span class="map-locating-dot"></span>
                            Localisation en cours...
                        </div>
                    </div>
                    <div id="municipalityMap" style="flex:1;min-height:200px;"></div>
                    <div class="map-legend">
                        <div class="map-legend-item">
                            <div class="map-legend-dot" style="background:#2FA084;"></div>Mairie proche
                        </div>
                        <div class="map-legend-item">
                            <div class="map-legend-dot" style="background:#3b82f6;"></div>Ma position
                        </div>
                    </div>
                </div>
            </div><!-- end map-panel -->

        </div><!-- end layout-split -->

    <script>
        window.addEventListener('scroll', function() {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
        });

        /* ===== VIEW TOGGLE ===== */
        function setView(mode) {
            var container = document.getElementById('rdvContainer');
            var btns = document.querySelectorAll('.view-toggle-btn');

            btns.forEach(function(b) { b.classList.remove('active'); });
            document.querySelector('[data-view="' + mode + '"]').classList.add('active');

            container.classList.remove('view-list', 'view-grid');
            container.classList.add('view-' + mode);

            localStorage.setItem('rdv_view', mode);
        }

        function setScale(val) {
            var container = document.getElementById('rdvContainer');
            container.setAttribute('data-scale', val);
            localStorage.setItem('rdv_scale', val);
        }

        // Restore from localStorage
        (function() {
            var savedView = localStorage.getItem('rdv_view') || 'list';
            var savedScale = localStorage.getItem('rdv_scale') || '2';
            setView(savedView);
            setScale(savedScale);
            document.getElementById('sizeSlider').value = savedScale;
        })();

        /* ===== RDV LOGIC ===== */
        var slotTaken = false; // multi-service: slot check done server-side on submit

        function confirmRdv() {
            if (typeof Swal === 'undefined') {
                document.getElementById('rdvForm').submit();
                return;
            }
            Swal.fire({
                title: "Confirmer vos rendez-vous ?",
                html: "<?php echo count($chainedSlots) > 1 ? count($chainedSlots) . ' rendez-vous seront créés.' : '1 rendez-vous sera créé.'; ?>",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#135D36",
                cancelButtonColor: "#aaa",
                confirmButtonText: "✓ Confirmer",
                cancelButtonText: "Annuler"
            }).then(function(result) {
                if (result.isConfirmed) {
                    document.getElementById('rdvForm').submit();
                }
            });
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
                }).then(function(result) {
                    if (result.isConfirmed) {
                        Swal.fire({ title: "Rendez-vous supprimé !", icon: "success", draggable: true }).then(function() {
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

    <!-- ===== NUMERIC CLOCK PICKER — JS (Option A) ===== -->
    <script>
    (function() {
        var h      = 9;
        var m      = 0;
        var mode   = '<?php echo $mode; ?>';
        var catIds = <?php echo json_encode(array_map('intval', $selectedCatIds)); ?>;
        var date   = '<?php echo addslashes($selectedDate); ?>';
        var month  = <?php echo $month ?? date("n"); ?>;
        var year   = <?php echo $year ?? date("Y"); ?>;

        function pad(n) { return String(n).padStart(2, '0'); }

        function render() {
            var hEl = document.getElementById('clockHourDisplay');
            var mEl = document.getElementById('clockMinDisplay');
            var pEl = document.getElementById('clockPreviewText');
            if (hEl) hEl.textContent = pad(h);
            if (mEl) mEl.textContent = pad(m);
            if (pEl) pEl.textContent = pad(h) + ':' + pad(m);

            // Option A: update calendar when time changes
            if (mode === 'A') updateCalendarA();
        }

        window.changeHour = function(delta) {
            h = h + delta;
            if (h < 8)  h = 17;
            if (h > 17) h = 8;
            render();
        };

        window.changeMin = function(delta) {
            m = m + delta;
            if (m < 0)  m = 55;
            if (m > 55) m = 0;
            render();
        };

        // Option A: fetch available dates and color the calendar
        var calTimer;
        function updateCalendarA() {
            clearTimeout(calTimer);
            calTimer = setTimeout(function() {
                var timeStr = pad(h) + ':' + pad(m) + ':00';
                var catPart = catIds.map(function(id){ return 'cats[]=' + id; }).join('&');
                var url = '../../ajax/available_dates.php?' + catPart + '&heure=' + encodeURIComponent(timeStr);

                // Gray out all days while loading
                document.querySelectorAll('.cal-day').forEach(function(el) {
                    el.classList.remove('available', 'unavailable');
                    el.classList.add('loading-av');
                });

                fetch(url)
                    .then(function(r) { return r.json(); })
                    .then(function(availDates) {
                        document.querySelectorAll('.cal-day').forEach(function(el) {
                            el.classList.remove('loading-av');
                            var d = el.getAttribute('data-date');
                            if (!d) return;
                            if (availDates.indexOf(d) !== -1) {
                                el.classList.add('available');
                                el.classList.remove('unavailable');
                                // Set correct href with current time
                                var href = el.getAttribute('data-href');
                                if (href) el.href = href.replace('HEURE', encodeURIComponent(timeStr));
                            } else {
                                el.classList.add('unavailable');
                                el.classList.remove('available');
                                el.removeAttribute('href');
                            }
                        });
                    })
                    .catch(function() {
                        document.querySelectorAll('.cal-day').forEach(function(el) {
                            el.classList.remove('loading-av');
                        });
                    });
            }, 400);
        }

        // Option A: "Voir les dates disponibles →" button sets time in URL
        window.confirmTime = function() {
            if (!date && mode === 'A') {
                // Just update the calendar — no date selected yet
                updateCalendarA();
                return;
            }
            var timeStr = pad(h) + ':' + pad(m) + ':00';
            var catPart = catIds.map(function(id){ return 'cats[]=' + id; }).join('&');
            var url = 'rendez-vous.php?' + catPart
                    + '&mode=' + mode
                    + '&heure=' + encodeURIComponent(timeStr)
                    + '&month=' + month
                    + '&year='  + year;
            window.location.href = url;
        };

        render();
        // Trigger calendar update on load for Option A
        if (mode === 'A') setTimeout(updateCalendarA, 200);
    })();
    </script>

    <!-- ===== MULTI-SERVICE SELECTION — JS ===== -->
    <script>
    (function() {
        var selected = <?php echo json_encode(array_map('intval', $selectedCatIds)); ?>;

        window.toggleCat = function(id, el) {
            var idx = selected.indexOf(id);
            if (idx === -1) {
                selected.push(id);
                el.classList.add('selected');
            } else {
                selected.splice(idx, 1);
                el.classList.remove('selected');
            }
            var count   = selected.length;
            var isEmpty = (count === 0);

            // Update counter text
            var selCount = document.getElementById('selCount');
            if (selCount) selCount.textContent = count;

            // Enable / disable the inline counter button
            var inlineBtn = document.getElementById('continueBtn');
            if (inlineBtn) {
                inlineBtn.disabled = isEmpty;
                inlineBtn.classList.toggle('disabled', isEmpty);
            }

            // Enable / disable the FOOTER button (the one the user sees)
            var footerBtn = document.getElementById('footerContinueBtn');
            if (footerBtn) {
                footerBtn.disabled = isEmpty;
                footerBtn.classList.toggle('disabled', isEmpty);
            }
        };

        window.goToStep2 = function() {
            if (selected.length === 0) return;
            var url = 'rendez-vous.php?' + selected.map(function(id){ return 'cats[]=' + id; }).join('&');
            window.location.href = url;
        };
    })();
    </script>

    <!-- ===== SUGGESTION DE CATÉGORIE — JS ===== -->
    <script>
    (function() {
        const input    = document.getElementById('suggestInput');
        const dropdown = document.getElementById('suggestDropdown');
        const results  = document.getElementById('suggestResults');
        const spinner  = document.getElementById('suggestSpinner');

        if (!input) return;

        let debounceTimer;
        const BASE = '../../ajax/suggest_category.php';

        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const q = this.value.trim();

            if (q.length < 2) {
                dropdown.classList.remove('open');
                return;
            }

            spinner.classList.add('active');
            debounceTimer = setTimeout(function() {
                fetch(BASE + '?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        spinner.classList.remove('active');
                        renderResults(data, q);
                    })
                    .catch(() => {
                        spinner.classList.remove('active');
                        dropdown.classList.remove('open');
                    });
            }, 320);
        });

        function renderResults(data, q) {
            results.innerHTML = '';

            if (!data || data.length === 0) {
                results.innerHTML = '<div class="suggest-no-result">Aucune suggestion trouvée — choisissez manuellement ci-dessous</div>';
                dropdown.classList.add('open');
                return;
            }

            data.forEach(function(item) {
                const a = document.createElement('a');
                a.className     = 'suggest-item';
                a.href = 'rendez-vous.php?cats[]=' + item.id;
                a.setAttribute('tabindex', '0');

                const conf = Math.min(item.confidence, 99);
                const desc = item.description
                    ? item.description.substring(0, 45) + (item.description.length > 45 ? '…' : '')
                    : '';

                a.innerHTML = `
                    <div class="suggest-item-icon">
                        <img src="../../assets/icons/${item.icone}" alt="" onerror="this.style.display='none'">
                    </div>
                    <div style="min-width:0;flex:1;">
                        <div class="suggest-item-name">${item.nom}</div>
                        ${desc ? '<div class="suggest-item-desc">' + desc + '</div>' : ''}
                    </div>
                    <div class="suggest-confidence">${conf}%</div>
                `;

                results.appendChild(a);
            });

            dropdown.classList.add('open');
        }

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!document.getElementById('suggestWrap').contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });

        // Keyboard navigation
        input.addEventListener('keydown', function(e) {
            const items = dropdown.querySelectorAll('.suggest-item');
            if (e.key === 'ArrowDown' && items.length) { e.preventDefault(); items[0].focus(); }
            if (e.key === 'Escape') dropdown.classList.remove('open');
        });

        dropdown.addEventListener('keydown', function(e) {
            const items = [...dropdown.querySelectorAll('.suggest-item')];
            const idx   = items.indexOf(document.activeElement);
            if (e.key === 'ArrowDown' && idx < items.length - 1) { e.preventDefault(); items[idx + 1].focus(); }
            if (e.key === 'ArrowUp') { e.preventDefault(); idx > 0 ? items[idx - 1].focus() : input.focus(); }
            if (e.key === 'Escape') { dropdown.classList.remove('open'); input.focus(); }
        });
    })();
    </script>

    <!-- ===== MINI MAP — Mairies à proximité ===== -->
    <script>
    (function() {
        // Custom green marker icon
        var greenIcon = L.divIcon({
            className: '',
            html: '<div style="width:14px;height:14px;background:#2FA084;border:2.5px solid white;border-radius:50%;box-shadow:0 2px 6px rgba(47,160,132,0.5);"></div>',
            iconSize: [14, 14],
            iconAnchor: [7, 7]
        });

        var blueIcon = L.divIcon({
            className: '',
            html: '<div style="width:14px;height:14px;background:#3b82f6;border:2.5px solid white;border-radius:50%;box-shadow:0 2px 6px rgba(59,130,246,0.5);"></div>',
            iconSize: [14, 14],
            iconAnchor: [7, 7]
        });

        // Default center: Tunisia (fallback if geolocation fails)
        var defaultLat = 36.8189;
        var defaultLng = 10.1658;

        var map = L.map('municipalityMap', {
            zoomControl: true,
            attributionControl: false
        }).setView([defaultLat, defaultLng], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18
        }).addTo(map);

        var userMarker = null;
        var municipalityMarkers = [];

        function loadingDone(text) {
            var el = document.getElementById('mapLocating');
            if (el) el.innerHTML = '<span style="font-size:11px;color:var(--stone);">&#10004; ' + text + '</span>';
        }

        function fetchNearbyMunicipalities(lat, lng) {
            var radius = 10000; // 10km
            var query = '[out:json][timeout:25];(node["amenity"="townhall"](around:' + radius + ',' + lat + ',' + lng + ');way["amenity"="townhall"](around:' + radius + ',' + lat + ',' + lng + '););out center;';
            var url = 'https://overpass-api.de/api/interpreter?data=' + encodeURIComponent(query);

            fetch(url)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    // Remove old markers
                    municipalityMarkers.forEach(function(m) { map.removeLayer(m); });
                    municipalityMarkers = [];

                    if (!data.elements || data.elements.length === 0) {
                        loadingDone('Aucune mairie trouvée dans un rayon de 10 km');
                        return;
                    }

                    data.elements.forEach(function(el) {
                        var elLat = el.lat || (el.center && el.center.lat);
                        var elLng = el.lon || (el.center && el.center.lon);
                        if (!elLat || !elLng) return;

                        var name = el.tags && (el.tags.name || el.tags['name:fr']) ? (el.tags.name || el.tags['name:fr']) : 'Mairie';

                        var marker = L.marker([elLat, elLng], { icon: greenIcon })
                            .addTo(map)
                            .bindPopup(
                                '<div style="font-family:Inter,sans-serif;min-width:160px;">' +
                                '<strong style="font-size:13px;color:#0F3B2C;">' + name + '</strong><br>' +
                                '<span style="font-size:11px;color:#666;">Mairie municipale</span><br><br>' +
                                '<a href="rendez-vous.php" style="display:inline-block;padding:6px 14px;background:linear-gradient(135deg,#135D36,#2FA084);color:white;border-radius:20px;text-decoration:none;font-size:11.5px;font-weight:600;">&#128197; Prendre un RDV</a>' +
                                '</div>',
                                { maxWidth: 200 }
                            );

                        municipalityMarkers.push(marker);
                    });

                    loadingDone(data.elements.length + ' mairie(s) trouvée(s)');
                })
                .catch(function() {
                    loadingDone('Impossible de charger les mairies');
                });
        }

        // Try geolocation
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    var lat = pos.coords.latitude;
                    var lng = pos.coords.longitude;

                    map.setView([lat, lng], 13);

                    // User marker
                    userMarker = L.marker([lat, lng], { icon: blueIcon })
                        .addTo(map)
                        .bindPopup('<div style="font-family:Inter,sans-serif;font-size:12px;"><strong style="color:#3b82f6;">&#128205; Votre position</strong></div>')
                        .openPopup();

                    // Draw radius circle
                    L.circle([lat, lng], {
                        color: 'rgba(59,130,246,0.3)',
                        fillColor: 'rgba(59,130,246,0.05)',
                        fillOpacity: 1,
                        weight: 1.5,
                        radius: 10000
                    }).addTo(map);

                    fetchNearbyMunicipalities(lat, lng);
                },
                function() {
                    // Geolocation denied — use default (Tunisia)
                    fetchNearbyMunicipalities(defaultLat, defaultLng);
                    loadingDone('Position par défaut (Tunis)');
                },
                { timeout: 8000 }
            );
        } else {
            fetchNearbyMunicipalities(defaultLat, defaultLng);
            loadingDone('Géolocalisation non supportée');
        }
    })();
    </script>

</body>
</html>