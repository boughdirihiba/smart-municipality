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
        .layout-split { display: flex; gap: 24px; width: 100%; padding: 20px 28px; height: calc(100vh - 60px); overflow: hidden; }

        /* ===== LEFT: WIZARD ===== */
        .left-panel { width: 480px; min-width: 480px; display: flex; flex-direction: column; animation: wizardEntry 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
        @keyframes wizardEntry { from { opacity: 0; transform: translateY(20px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }

        .wizard-card {
            background: var(--white); border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl); overflow: hidden;
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

        .wizard-body { padding: 18px 24px 12px; flex: 1; display: flex; flex-direction: column; overflow: hidden; }
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
        .time-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: var(--stone); margin-bottom: 7px; }
        .time-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; }
        .time-chip { display: flex; align-items: center; justify-content: center; padding: 8px 6px; border-radius: var(--radius-sm); background: var(--pearl); border: 2px solid transparent; text-decoration: none; color: var(--slate); font-size: 12px; font-weight: 600; font-variant-numeric: tabular-nums; transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); cursor: pointer; }
        .time-chip:hover { border-color: var(--mint); background: var(--sage); transform: translateY(-1px); }
        .time-chip.selected { border-color: var(--emerald); background: linear-gradient(135deg, rgba(26,122,78,0.08), rgba(61,220,132,0.08)); color: var(--forest); box-shadow: 0 2px 8px rgba(26,122,78,0.1); }

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
        .right-panel { flex: 1; display: flex; flex-direction: column; overflow: hidden; animation: fadeIn 0.6s ease 0.2s both; }
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
    </style>
</head>
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
                            <?php if ($step == 1): ?>Choisissez le service dont vous avez besoin
                            <?php elseif ($step == 2): ?>Sélectionnez une date et un créneau horaire
                            <?php else: ?>Vérifiez et confirmez votre rendez-vous
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="wizard-progress">
                        <div class="wp-step <?php echo $step >= 1 ? ($step > 1 ? 'done' : 'active') : 'pending'; ?>">
                            <div class="wp-bubble"><?php echo $step > 1 ? '&#10004;' : '1'; ?></div>
                            <div class="wp-label">Service</div>
                        </div>
                        <div class="wp-connector <?php echo $step > 1 ? 'filled' : ''; ?>"></div>
                        <div class="wp-step <?php echo $step >= 2 ? ($step > 2 ? 'done' : 'active') : 'pending'; ?>">
                            <div class="wp-bubble"><?php echo $step > 2 ? '&#10004;' : '2'; ?></div>
                            <div class="wp-label">Date & Heure</div>
                        </div>
                        <div class="wp-connector <?php echo $step > 2 ? 'filled' : ''; ?>"></div>
                        <div class="wp-step <?php echo $step == 3 ? 'active' : 'pending'; ?>">
                            <div class="wp-bubble"><?php echo $step == 3 ? '&#10004;' : '3'; ?></div>
                            <div class="wp-label">Confirmation</div>
                        </div>
                    </div>

                    <div class="wizard-body">
                        <?php if ($step == 1): ?>
                        <div class="wizard-step-panel">
                            <div class="wizard-step-label">Étape 1</div>
                            <div class="wizard-step-title">Quel service recherchez-vous ?</div>
                            <div class="svc-grid">
                                <?php foreach ($categories as $cat): ?>
                                    <a href="rendez-vous.php?categorie_id=<?php echo $cat['id']; ?>" class="svc-option <?php echo ($selectedCategorieId == $cat['id']) ? 'selected' : ''; ?>">
                                        <div class="svc-icon-wrap"><img src="../../assets/icons/<?php echo htmlspecialchars($cat['icone']); ?>" alt=""></div>
                                        <span><?php echo htmlspecialchars($cat['nom']); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php elseif ($step == 2): ?>
                        <div class="wizard-step-panel">
                            <div class="wizard-step-label">Étape 2 — <?php echo htmlspecialchars($selectedCategorieName); ?></div>
                            <div class="wizard-step-title">Choisissez votre créneau</div>
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
                            <div class="cal-nav">
                                <a href="rendez-vous.php?categorie_id=<?php echo $selectedCategorieId; ?>&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="cal-nav-btn">&larr;</a>
                                <span class="cal-month-label"><?php echo $monthName . ' ' . $year; ?></span>
                                <a href="rendez-vous.php?categorie_id=<?php echo $selectedCategorieId; ?>&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="cal-nav-btn">&rarr;</a>
                            </div>
                            <table class="cal-grid">
                                <thead><tr><th>Lu</th><th>Ma</th><th>Me</th><th>Je</th><th>Ve</th><th>Sa</th><th>Di</th></tr></thead>
                                <tbody><tr>
                                <?php
                                $dayCount = 0;
                                for ($i = 1; $i < $firstDay; $i++) { echo "<td></td>"; $dayCount++; }
                                for ($day = 1; $day <= $daysInMonth; $day++) {
                                    $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                    $isSelected = ($selectedDate == $dateStr) ? 'selected-day' : '';
                                    echo '<td><a href="rendez-vous.php?categorie_id=' . $selectedCategorieId . '&date=' . $dateStr . '&month=' . $month . '&year=' . $year . '" class="cal-day ' . $isSelected . '">' . $day . '</a></td>';
                                    $dayCount++;
                                    if ($dayCount % 7 == 0 && $day != $daysInMonth) echo "</tr><tr>";
                                }
                                while ($dayCount % 7 != 0) { echo "<td></td>"; $dayCount++; }
                                ?>
                                </tr></tbody>
                            </table>
                            <div class="time-label">Heure du rendez-vous</div>
                            <div class="time-grid">
                                <?php foreach ($heures as $h): ?>
                                    <a href="rendez-vous.php?categorie_id=<?php echo $selectedCategorieId; ?>&date=<?php echo $selectedDate; ?>&heure=<?php echo $h; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>" class="time-chip <?php echo ($selectedHeure == $h) ? 'selected' : ''; ?>"><?php echo $h; ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="wizard-step-panel">
                            <div class="confirm-panel">
                                <div class="confirm-icon">&#128197;</div>
                                <div class="confirm-heading">Tout est prêt !</div>
                                <div class="confirm-details">
                                    <div class="confirm-row">
                                        <span class="cr-label">Service</span>
                                        <span class="cr-value"><?php echo htmlspecialchars($selectedCategorieName); ?></span>
                                    </div>
                                    <div class="confirm-row">
                                        <span class="cr-label">Date</span>
                                        <span class="cr-value"><?php $ts = strtotime($selectedDate); echo $joursSemaine[date('w', $ts)] . ', ' . date('d', $ts) . ' ' . $moisComplet[date('n', $ts)]; ?></span>
                                    </div>
                                    <div class="confirm-row">
                                        <span class="cr-label">Heure</span>
                                        <span class="cr-value"><?php echo htmlspecialchars($selectedHeure); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="wizard-footer">
                        <?php if ($step == 1): ?><div class="wiz-spacer"></div>
                        <?php elseif ($step == 2): ?><a href="rendez-vous.php" class="wiz-btn wiz-btn-back">&larr; Service</a>
                        <?php else: ?>
                            <?php $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n'); $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y'); ?>
                            <a href="rendez-vous.php?categorie_id=<?php echo $selectedCategorieId; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>" class="wiz-btn wiz-btn-back">&larr; Date</a>
                        <?php endif; ?>
                        <?php if ($step == 3): ?>
                            <form id="rdvForm" action="/smart-municipality/controllers/RendezVousController.php" method="POST" style="flex:1;display:flex;">
                                <input type="hidden" name="action" value="create">
                                <input type="hidden" name="categorie_id" value="<?php echo htmlspecialchars($selectedCategorieId); ?>">
                                <input type="hidden" name="date_rdv" value="<?php echo htmlspecialchars($selectedDate); ?>">
                                <input type="hidden" name="heure" value="<?php echo htmlspecialchars($selectedHeure); ?>">
                                <button type="button" class="wiz-btn wiz-btn-confirm" onclick="confirmRdv()">&#10004; Confirmer</button>
                            </form>
                        <?php else: ?>
                            <button class="wiz-btn wiz-btn-confirm disabled" disabled><?php echo $step == 1 ? 'Choisir un service &rarr;' : 'Choisir date et heure &rarr;'; ?></button>
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

        </div>
    </div>

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
        var slotTaken = <?php echo $slotTaken ? 'true' : 'false'; ?>;

        function confirmRdv() {
            if (typeof Swal === 'undefined') {
                if (slotTaken) { alert("Ce créneau est déjà réservé !"); }
                else { alert("Rendez-vous en attente"); document.getElementById('rdvForm').submit(); }
                return;
            }
            if (slotTaken) {
                Swal.fire({
                    title: "Créneau indisponible !",
                    html: "Ce créneau est <b>déjà réservé</b>.<br><br>Veuillez choisir une <b>autre heure</b> ou une <b>autre date</b>.",
                    icon: "error",
                    confirmButtonColor: "#e74c3c",
                    confirmButtonText: "Changer l'heure",
                    showCancelButton: true,
                    cancelButtonText: "Changer la date",
                    cancelButtonColor: "#f39c12"
                }).then(function(result) {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        window.location.href = "rendez-vous.php?categorie_id=<?php echo $selectedCategorieId; ?>&month=<?php echo isset($month) ? $month : date('n'); ?>&year=<?php echo isset($year) ? $year : date('Y'); ?>";
                    }
                });
            } else {
                Swal.fire({ title: "Rendez-vous en attente", icon: "success", draggable: true }).then(function() {
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

</body>
</html>