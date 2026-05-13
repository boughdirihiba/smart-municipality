<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/controllers/EvenementC.php';
require_once __DIR__ . '/controllers/ParticipationC.php';
require_once __DIR__ . '/controllers/CategorieEvenementC.php';

$userId     = $_SESSION['user']['id']     ?? $_SESSION['user_id']   ?? null;
$userRole   = $_SESSION['user']['role']   ?? $_SESSION['role']      ?? 'citoyen';
$isLoggedIn = $userId !== null;
$isAdmin    = $userRole === 'admin';
$userName   = $_SESSION['user']['prenom'] ?? $_SESSION['prenom']    ?? 'Invité';

$evenementC = new EvenementC();
$participationC = new ParticipationC();
$categorieC = new CategorieEvenementC();

$categorie_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$categorie = $categorieC->afficherCategorieParId($categorie_id);

if (!$categorie_id || !$categorie) {
    // No category ID → show all categories listing
    $toutes_categories = $categorieC->afficherCategories();
    foreach ($toutes_categories as &$cat) {
        $cat['nb_evenements'] = $categorieC->compterEvenementsParCategorie($cat['id']);
    }
    unset($cat);
    $title = 'Événements';
    require BASE_PATH . '/views/App/Views/layouts/header.php';
    ?>
    <style>
        .cat-hero{background:linear-gradient(135deg,#0f3b2c,#1a7a4e);color:#fff;padding:60px 20px 40px;text-align:center;}
        .cat-hero h1{font-size:2rem;font-weight:700;margin-bottom:10px;}
        .cat-hero p{opacity:.85;}
        .cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px;max-width:1200px;margin:40px auto;padding:0 20px;}
        .cat-card{border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1);transition:transform .2s;background:#fff;}
        .cat-card:hover{transform:translateY(-6px);}
        .cat-card img{width:100%;height:200px;object-fit:cover;}
        .cat-card-body{padding:20px;}
        .cat-card-title{font-size:1.2rem;font-weight:700;color:#0f3b2c;margin-bottom:8px;text-transform:capitalize;}
        .cat-card-desc{color:#64748b;font-size:.88rem;margin-bottom:12px;}
        .cat-card-count{color:#1a7a4e;font-size:.85rem;font-weight:600;margin-bottom:16px;}
        .btn-voir{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#0f3b2c,#1a7a4e);color:#fff;padding:10px 22px;border-radius:40px;text-decoration:none;font-weight:600;}
        .btn-voir:hover{opacity:.9;color:#fff;}
        .cat-page-header{max-width:1200px;margin:40px auto 0;padding:0 20px;}
        .cat-hero-title{font-size:2rem;font-weight:700;margin-bottom:10px;}
    </style>
    <div class="cat-hero">
        <h1>Catégories d'événements</h1>
        <p>Choisissez une catégorie pour voir tous les événements associés</p>
    </div>
    <div class="cat-page-header"><h2 style="font-size:1.5rem;font-weight:700;color:#1e293b;margin-bottom:8px;">Explorez les événements par catégorie</h2></div>
    <div class="cat-grid">
    <?php foreach ($toutes_categories as $cat): ?>
        <div class="cat-card">
            <?php if (!empty($cat['image_url'])): ?>
                <img src="<?= htmlspecialchars($cat['image_url']) ?>" alt="<?= htmlspecialchars($cat['nom']) ?>">
            <?php else: ?>
                <img src="https://placehold.co/640x200/1a7a4e/white?text=<?= urlencode($cat['nom']) ?>" alt="">
            <?php endif; ?>
            <div class="cat-card-body">
                <div class="cat-card-title"><?= htmlspecialchars($cat['nom']) ?></div>
                <div class="cat-card-desc"><?= htmlspecialchars($cat['description'] ?? '') ?></div>
                <div class="cat-card-count"><i class="fas fa-calendar-alt"></i> <?= (int)$cat['nb_evenements'] ?> événement(s)</div>
                <a href="<?= BASE_URL ?>/index.php?action=evenements&id=<?= $cat['id'] ?>" class="btn-voir">
                    <i class="fas fa-arrow-right"></i> Voir les événements
                </a>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    <?php
    require BASE_PATH . '/views/App/Views/layouts/footer.php';
    exit();
}

$tousEvenements = $evenementC->afficherEvenements();
$evenements = array_filter($tousEvenements, function($event) use ($categorie_id) {
    return $event['categorie_id'] == $categorie_id;
});

// Recherche
$recherche = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($recherche)) {
    $evenements = array_filter($evenements, function($event) use ($recherche) {
        return stripos($event['titre'], $recherche) !== false || 
               stripos($event['description'], $recherche) !== false ||
               stripos($event['lieu'], $recherche) !== false;
    });
}

// Tri
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'date';
$sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] === 'asc' ? 'asc' : 'desc';

$evenementsArray = array_values($evenements);
usort($evenementsArray, function($a, $b) use ($sort_by, $sort_order, $participationC) {
    if ($sort_by == 'date') {
        $val1 = strtotime($a['date_evenement']);
        $val2 = strtotime($b['date_evenement']);
    } elseif ($sort_by == 'titre') {
        $val1 = strtolower($a['titre']);
        $val2 = strtolower($b['titre']);
    } elseif ($sort_by == 'lieu') {
        $val1 = strtolower($a['lieu']);
        $val2 = strtolower($b['lieu']);
    } elseif ($sort_by == 'places') {
        $val1 = $a['max_participants'] - $participationC->compterParticipationsValidees($a['id']);
        $val2 = $b['max_participants'] - $participationC->compterParticipationsValidees($b['id']);
    } else {
        $val1 = strtotime($a['date_evenement']);
        $val2 = strtotime($b['date_evenement']);
    }
    
    if ($sort_order === 'asc') {
        return $val1 <=> $val2;
    } else {
        return $val2 <=> $val1;
    }
});

$message = '';
$messageType = '';
if (isset($_GET['success']) && $_GET['success'] == 'inscrit') {
    $message = '✅ Votre inscription a été envoyée ! En attente de validation.';
    $messageType = 'success';
}
if (isset($_GET['error'])) {
    $message = '❌ ' . htmlspecialchars($_GET['error']);
    $messageType = 'danger';
}

$nbEvenements = count($evenementsArray);
$totalPlaces = array_sum(array_column($evenementsArray, 'max_participants'));

// Préparer les événements pour le calendrier
$calendarEvents = [];
foreach ($evenementsArray as $e) {
    $placesRestantes = $e['max_participants'] - $participationC->compterParticipationsValidees($e['id']);
    $estComplet = $placesRestantes <= 0;
    $estInscrit = false;
    if ($isLoggedIn) {
        $estInscrit = $participationC->estInscrit($userId, $e['id']);
    }
    
    $calendarEvents[] = [
        'id' => $e['id'],
        'title' => $e['titre'],
        'start' => $e['date_evenement'],
        'lieu' => $e['lieu'],
        'heure' => $e['heure'],
        'places_restantes' => $placesRestantes,
        'places_total' => $e['max_participants'],
        'est_complet' => $estComplet,
        'est_inscrit' => $estInscrit,
        'color' => $estComplet ? '#9e9e9e' : ($estInscrit ? '#2e7d32' : '#1a5e2a'),
        'textColor' => 'white'
    ];
}
?>

<?php
$mesParticipations = $isLoggedIn ? $participationC->afficherParticipationsParUser($userId) : [];
$title       = 'Événements';
$hideSidebar = true;
$flash       = function_exists('get_flash') ? get_flash() : null;
require BASE_PATH . '/views/App/Views/layouts/header.php';
?>
<style>
/* ── Reset scoped to page ── */
.ce-page *, .ce-page *::before, .ce-page *::after { box-sizing: border-box; }

/* ── Design tokens ── */
.ce-page {
    --cp: #135D36;
    --cp-dark: #0d3b1a;
    --cp-light: #e8f5e9;
    --cp-mid: #2FA084;
    --grad: linear-gradient(135deg, #135D36, #2FA084);
    --sh1: 0 2px 8px rgba(0,0,0,.06);
    --sh2: 0 8px 24px rgba(0,0,0,.1);
    --r: 14px;
    font-family: 'Inter', sans-serif;
    background: #f4f7f4;
    min-height: 60vh;
}

/* ── Hero ── */
.ce-hero {
    background: var(--grad);
    padding: 44px 0 36px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.ce-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 260px; height: 260px;
    border-radius: 50%;
    background: rgba(255,255,255,.07);
}
.ce-hero::after {
    content: '';
    position: absolute;
    bottom: -80px; left: -40px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: rgba(255,255,255,.05);
}
.ce-hero-inner { max-width: 1200px; margin: 0 auto; padding: 0 24px; position: relative; z-index: 1; }
.ce-back {
    display: inline-flex; align-items: center; gap: 7px;
    color: rgba(255,255,255,.85); text-decoration: none;
    font-size: .78rem; font-weight: 500;
    background: rgba(255,255,255,.15); padding: 5px 14px;
    border-radius: 30px; margin-bottom: 20px;
    transition: background .2s;
}
.ce-back:hover { background: rgba(255,255,255,.25); color: #fff; }
.ce-hero-title { font-size: 2rem; font-weight: 800; margin: 0 0 6px; letter-spacing: -.5px; }
.ce-hero-desc  { font-size: .88rem; opacity: .85; margin: 0 0 20px; }
.ce-hero-pills { display: flex; flex-wrap: wrap; gap: 10px; }
.ce-hero-pill {
    background: rgba(255,255,255,.18); backdrop-filter: blur(8px);
    padding: 5px 16px; border-radius: 30px;
    font-size: .75rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: 6px;
}

/* ── Main layout ── */
.ce-layout {
    max-width: 1200px; margin: 0 auto;
    padding: 28px 24px;
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 24px;
    align-items: start;
}

/* ── Search / Sort bar ── */
.ce-toolbar {
    background: #fff; border-radius: var(--r);
    padding: 14px 16px; margin-bottom: 16px;
    box-shadow: var(--sh1);
    display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
}
.ce-search {
    flex: 1; display: flex; align-items: center;
    gap: 8px; background: #f4f7f4; border-radius: 10px;
    padding: 8px 14px; min-width: 180px;
}
.ce-search i { color: #999; font-size: .85rem; }
.ce-search input {
    border: none; background: transparent; outline: none;
    font-size: .85rem; width: 100%; font-family: inherit; color: #333;
}
.ce-search-btn {
    background: var(--grad); border: none; color: #fff;
    padding: 8px 18px; border-radius: 10px; font-size: .8rem;
    font-weight: 600; cursor: pointer; white-space: nowrap;
    transition: opacity .2s;
}
.ce-search-btn:hover { opacity: .88; }

.ce-sorts {
    background: #fff; border-radius: var(--r);
    padding: 10px 16px; margin-bottom: 16px;
    box-shadow: var(--sh1);
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
}
.ce-sort-lbl { font-size: .73rem; font-weight: 700; color: var(--cp); margin-right: 4px; }
.ce-sort-btn {
    background: #f4f7f4; border: none; cursor: pointer; text-decoration: none;
    padding: 5px 14px; border-radius: 20px; font-size: .72rem; font-weight: 500;
    color: #555; transition: all .15s;
}
.ce-sort-btn:hover, .ce-sort-btn.active { background: var(--cp); color: #fff; }
.ce-add-btn {
    margin-left: auto;
    background: var(--grad); border: none; color: #fff; text-decoration: none;
    padding: 6px 16px; border-radius: 20px; font-size: .72rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: 6px; transition: opacity .2s;
}
.ce-add-btn:hover { opacity: .88; color: #fff; }

/* ── Event cards ── */
.ce-events { display: flex; flex-direction: column; gap: 16px; }
.ce-card {
    background: #fff; border-radius: var(--r);
    box-shadow: var(--sh1); border: 1px solid rgba(0,0,0,.04);
    overflow: hidden; transition: transform .25s, box-shadow .25s;
}
.ce-card:hover { transform: translateY(-3px); box-shadow: var(--sh2); }
.ce-card-body { padding: 18px 20px 16px; }
.ce-card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 10px; }
.ce-card-title { font-size: 1rem; font-weight: 700; color: var(--cp-dark); margin: 0; line-height: 1.3; }
.ce-cat-pill {
    background: var(--cp-light); color: var(--cp);
    padding: 3px 10px; border-radius: 20px;
    font-size: .66rem; font-weight: 700; white-space: nowrap; flex-shrink: 0;
}
.ce-card-meta { display: flex; flex-wrap: wrap; gap: 14px; margin-bottom: 10px; }
.ce-meta-item { display: flex; align-items: center; gap: 5px; font-size: .73rem; color: #666; }
.ce-meta-item i { color: var(--cp); width: 14px; font-size: .75rem; }
.ce-card-desc {
    font-size: .73rem; color: #888; line-height: 1.5;
    margin-bottom: 14px;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.ce-progress-row { display: flex; justify-content: space-between; font-size: .68rem; color: #999; margin-bottom: 6px; }
.ce-progress-track { height: 5px; background: #eee; border-radius: 5px; overflow: hidden; margin-bottom: 14px; }
.ce-progress-fill { height: 100%; background: var(--grad); border-radius: 5px; transition: width .4s; }
.ce-card-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.ce-btn-sub {
    flex: 1; background: var(--cp-light); border: 1.5px solid var(--cp);
    color: var(--cp); padding: 8px 14px; border-radius: 10px;
    font-size: .76rem; font-weight: 700; cursor: pointer;
    transition: all .2s; display: inline-flex; align-items: center; justify-content: center; gap: 6px;
}
.ce-btn-sub:hover { background: var(--cp); color: #fff; }
.ce-status {
    flex: 1; padding: 8px 14px; border-radius: 10px; text-align: center;
    font-size: .74rem; font-weight: 700;
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
}
.ce-status-pending  { background: #fef3c7; color: #b45309; }
.ce-status-ok       { background: #dcfce7; color: #16a34a; }
.ce-status-refused  { background: #fee2e2; color: #dc2626; }
.ce-status-full     { background: #f1f5f9; color: #64748b; }
.ce-btn-share {
    background: #e8f0fe; color: #1877f2; border: none; cursor: pointer;
    padding: 8px 12px; border-radius: 10px; font-size: .73rem;
    font-weight: 600; transition: all .2s;
    display: inline-flex; align-items: center; gap: 5px;
}
.ce-btn-share:hover { background: #1877f2; color: #fff; }
.ce-admin-row { display: flex; gap: 6px; margin-top: 8px; }
.ce-admin-btn {
    padding: 5px 12px; border-radius: 8px; font-size: .68rem;
    font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;
}
.ce-btn-edit   { background: #fef3c7; color: #b45309; }
.ce-btn-users  { background: #dbeafe; color: #2563eb; }
.ce-btn-del    { background: #fee2e2; color: #dc2626; }

/* ── Empty state ── */
.ce-empty {
    text-align: center; padding: 50px 24px;
    background: #fff; border-radius: var(--r); box-shadow: var(--sh1);
}
.ce-empty i { font-size: 2.8rem; color: #ccc; margin-bottom: 12px; display: block; }
.ce-empty p { color: #888; font-size: .88rem; }

/* ── Calendar ── */
.ce-calendar-wrap {
    background: #fff; border-radius: var(--r);
    padding: 20px; margin-top: 20px;
    box-shadow: var(--sh1);
}
.ce-calendar-title {
    font-size: .88rem; font-weight: 700; color: var(--cp);
    margin-bottom: 14px; display: flex; align-items: center; gap: 7px;
}
.fc { font-family: 'Inter', sans-serif; }
.fc-event { cursor: pointer; border-radius: 6px; font-size: .68rem; padding: 3px 6px; transition: all .2s; }
.fc-event:hover { opacity: .85; }
.fc-col-header-cell-cushion { font-weight: 700; color: var(--cp); text-transform: uppercase; font-size: .65rem; }
.fc-toolbar-title { font-size: .9rem !important; font-weight: 800 !important; color: var(--cp-dark) !important; }
.fc-button { background: var(--cp) !important; border: none !important; border-radius: 8px !important; padding: 4px 10px !important; font-size: .68rem !important; }
.fc-button:hover { background: var(--cp-dark) !important; }
.fc-day-today { background: var(--cp-light) !important; }

/* ── Sidebar: Mes participations ── */
.ce-sidebar { position: sticky; top: 80px; }
.ce-side-panel {
    background: #fff; border-radius: var(--r);
    box-shadow: var(--sh1); overflow: hidden;
}
.ce-side-header {
    background: var(--grad); color: #fff;
    padding: 14px 18px; display: flex; align-items: center; gap: 8px;
}
.ce-side-header h3 { margin: 0; font-size: .9rem; font-weight: 700; }
.ce-side-header .count-badge {
    background: rgba(255,255,255,.25); border-radius: 20px;
    padding: 2px 9px; font-size: .72rem; font-weight: 700; margin-left: auto;
}
.ce-side-body { padding: 14px; max-height: 520px; overflow-y: auto; }
.ce-part-item {
    border-radius: 10px; border: 1px solid #f0f0f0;
    padding: 12px 14px; margin-bottom: 10px;
    transition: background .15s;
}
.ce-part-item:last-child { margin-bottom: 0; }
.ce-part-item:hover { background: #f9fdf9; }
.ce-part-title { font-size: .83rem; font-weight: 700; color: #1a1a1a; margin-bottom: 5px; }
.ce-part-meta  { font-size: .72rem; color: #888; margin-bottom: 7px; display: flex; flex-direction: column; gap: 2px; }
.ce-part-foot  { display: flex; align-items: center; justify-content: space-between; }
.ce-pbadge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px; font-size: .68rem; font-weight: 700;
}
.ce-pb-ok      { background: #dcfce7; color: #16a34a; }
.ce-pb-wait    { background: #fef3c7; color: #b45309; }
.ce-pb-no      { background: #fee2e2; color: #dc2626; }
.ce-cancel-btn {
    font-size: .68rem; color: #dc2626; text-decoration: none; font-weight: 600;
    background: #fee2e2; padding: 3px 9px; border-radius: 8px; transition: all .15s;
}
.ce-cancel-btn:hover { background: #dc2626; color: #fff; }
.ce-no-part {
    text-align: center; padding: 30px 16px; color: #bbb;
}
.ce-no-part i { font-size: 2rem; margin-bottom: 8px; display: block; }
.ce-no-part p { font-size: .78rem; }
.ce-side-login {
    text-align: center; padding: 30px 16px;
}
.ce-side-login i { font-size: 2rem; color: #ddd; margin-bottom: 10px; display: block; }
.ce-side-login p { font-size: .78rem; color: #aaa; margin-bottom: 12px; }
.ce-login-link {
    display: inline-block; background: var(--grad); color: #fff;
    padding: 7px 18px; border-radius: 10px; font-size: .78rem;
    font-weight: 600; text-decoration: none; transition: opacity .2s;
}
.ce-login-link:hover { opacity: .88; color: #fff; }

/* ── Modal ── */
.ce-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.5); backdrop-filter: blur(4px);
    z-index: 10000; align-items: center; justify-content: center;
}
.ce-modal {
    background: #fff; border-radius: 20px;
    max-width: 420px; width: 92%;
    overflow: hidden; animation: ceModalIn .22s ease;
    box-shadow: 0 24px 64px rgba(0,0,0,.2);
}
@keyframes ceModalIn { from { transform: translateY(24px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.ce-modal-head {
    background: var(--grad); padding: 18px;
    text-align: center; color: #fff;
}
.ce-modal-head i { font-size: 2rem; margin-bottom: 4px; display: block; }
.ce-modal-head h3 { margin: 0; font-size: 1rem; font-weight: 700; }
.ce-modal-body { padding: 20px; }
.ce-modal-info {
    background: var(--cp-light); border-radius: 10px;
    padding: 12px 14px; margin-bottom: 16px; font-size: .8rem; line-height: 1.7;
}
.ce-modal-info p { margin: 0; }
.ce-modal-label { font-size: .78rem; font-weight: 700; margin-bottom: 5px; display: block; color: #333; }
.ce-modal-input {
    width: 100%; border: 1.5px solid #e0e0e0; border-radius: 9px;
    padding: 9px 12px; font-size: .85rem; outline: none; font-family: inherit;
    transition: border-color .2s;
}
.ce-modal-input:focus { border-color: var(--cp); }
.ce-modal-hint { font-size: .7rem; color: #999; margin-top: 4px; }
.ce-modal-alert {
    background: #fef3c7; border-radius: 8px;
    padding: 8px 12px; font-size: .74rem; color: #92400e; margin-top: 10px;
}
.ce-modal-foot {
    padding: 12px 20px; display: flex; gap: 10px;
    justify-content: flex-end; border-top: 1px solid #f0f0f0;
}
.ce-modal-cancel {
    background: #f1f5f9; border: none; border-radius: 9px;
    padding: 8px 18px; font-size: .8rem; font-weight: 600;
    color: #64748b; cursor: pointer;
}
.ce-modal-confirm {
    background: var(--grad); border: none; border-radius: 9px;
    padding: 8px 20px; font-size: .8rem; font-weight: 700;
    color: #fff; cursor: pointer; transition: opacity .2s;
}
.ce-modal-confirm:hover { opacity: .88; }

/* ── Toast ── */
.ce-toast {
    position: fixed; top: 72px; right: 20px; z-index: 10001;
    animation: ceSlideIn .3s ease;
}
@keyframes ceSlideIn { from { transform: translateX(110%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
.ce-toast-inner {
    display: flex; align-items: center; gap: 10px;
    background: #fff; border-radius: 12px; padding: 12px 18px;
    box-shadow: 0 8px 28px rgba(0,0,0,.14);
    font-size: .82rem; font-weight: 600; border-left: 4px solid #16a34a;
}
.ce-toast-inner.error { border-color: #dc2626; }
.ce-toast-icon { font-size: 1.1rem; }

/* ── Responsive ── */
@media (max-width: 900px) {
    .ce-layout { grid-template-columns: 1fr; }
    .ce-sidebar { position: static; }
    .ce-side-body { max-height: 300px; }
    .ce-hero-title { font-size: 1.5rem; }
}
</style>

<?php if ($message): ?>
<div class="ce-toast" id="ceToast">
    <div class="ce-toast-inner <?= $messageType === 'danger' ? 'error' : '' ?>">
        <span class="ce-toast-icon"><?= $messageType === 'success' ? '✅' : '❌' ?></span>
        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
    </div>
</div>
<?php endif; ?>

<!-- Modal d'inscription -->
<div id="inscriptionModal" class="ce-modal-overlay">
    <div class="ce-modal">
        <div class="ce-modal-head">
            <i class="fas fa-ticket-alt"></i>
            <h3>Confirmer l'inscription</h3>
        </div>
        <div class="ce-modal-body">
            <div class="ce-modal-info">
                <p><strong id="modalTitle">—</strong></p>
                <p><i class="fas fa-map-marker-alt" style="color:#135D36;width:16px;"></i> <span id="modalLieu">—</span></p>
                <p><i class="fas fa-calendar-day" style="color:#135D36;width:16px;"></i> <span id="modalDate">—</span> &nbsp;·&nbsp; <span id="modalHeure">—</span></p>
            </div>
            <label class="ce-modal-label">Nombre de participants</label>
            <input type="number" id="nbParticipants" class="ce-modal-input" min="1" max="10" value="1">
            <div class="ce-modal-hint">Maximum 10 personnes par inscription</div>
            <div class="ce-modal-alert">
                <i class="fas fa-info-circle"></i>
                Places disponibles : <strong id="placesRestantes">—</strong>
            </div>
        </div>
        <div class="ce-modal-foot">
            <button class="ce-modal-cancel" id="closeModalBtn">Annuler</button>
            <button class="ce-modal-confirm" id="confirmModalBtn"><i class="fas fa-check"></i> Confirmer</button>
        </div>
    </div>
</div>

<div class="ce-page">

<!-- Hero -->
<div class="ce-hero">
    <div class="ce-hero-inner">
        <a href="<?= BASE_URL ?>/index.php?action=evenements" class="ce-back">
            <i class="fas fa-arrow-left"></i> Toutes les catégories
        </a>
        <h1 class="ce-hero-title">
            <i class="fas <?php
                $n = $categorie['nom'];
                if ($n === 'Culture')       echo 'fa-music';
                elseif ($n === 'Sport')     echo 'fa-futbol';
                elseif ($n === 'Environnement') echo 'fa-leaf';
                elseif ($n === 'Social')    echo 'fa-handshake';
                elseif ($n === 'Technologie') echo 'fa-microchip';
                else echo 'fa-tag';
            ?>" style="margin-right:10px;"></i><?= htmlspecialchars($categorie['nom']) ?>
        </h1>
        <?php if (!empty($categorie['description'])): ?>
            <p class="ce-hero-desc"><?= htmlspecialchars($categorie['description']) ?></p>
        <?php endif; ?>
        <div class="ce-hero-pills">
            <span class="ce-hero-pill"><i class="fas fa-calendar-alt"></i> <?= $nbEvenements ?> événement(s)</span>
            <span class="ce-hero-pill"><i class="fas fa-users"></i> <?= $totalPlaces ?> places totales</span>
            <?php if ($isLoggedIn): ?>
                <span class="ce-hero-pill"><i class="fas fa-ticket-alt"></i> <?= count($mesParticipations) ?> réservation(s)</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Layout -->
<div class="ce-layout">

    <!-- ── Left: events ── -->
    <div class="ce-main">

        <!-- Search + sort toolbar -->
        <form method="GET" style="margin-bottom:0;">
            <input type="hidden" name="id" value="<?= $categorie_id ?>">
            <div class="ce-toolbar">
                <div class="ce-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Rechercher un événement…" value="<?= htmlspecialchars($recherche) ?>">
                </div>
                <button type="submit" class="ce-search-btn"><i class="fas fa-search"></i> Rechercher</button>
                <?php if ($recherche): ?>
                    <a href="?action=evenements_categorie&id=<?= $categorie_id ?>" style="color:#dc2626;font-size:.78rem;text-decoration:none;">✕ Effacer</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="ce-sorts">
            <span class="ce-sort-lbl"><i class="fas fa-sort"></i> Trier :</span>
            <?php
            $sorts = ['date' => 'Date', 'titre' => 'Titre', 'lieu' => 'Lieu'];
            foreach ($sorts as $key => $label):
                $nextOrder = ($sort_by === $key && $sort_order === 'asc') ? 'desc' : 'asc';
                $arrow     = $sort_by === $key ? ($sort_order === 'asc' ? ' ↑' : ' ↓') : '';
                $q = "?action=evenements_categorie&id=$categorie_id&sort_by=$key&sort_order=$nextOrder" . ($recherche ? '&search='.urlencode($recherche) : '');
            ?>
            <a href="<?= $q ?>" class="ce-sort-btn <?= $sort_by === $key ? 'active' : '' ?>"><?= "$label$arrow" ?></a>
            <?php endforeach; ?>
            <?php if ($isAdmin): ?>
                <a href="<?= BASE_URL ?>/views/evenement/ajouter.php?categorie=<?= $categorie_id ?>" class="ce-add-btn">
                    <i class="fas fa-plus-circle"></i> Ajouter un événement
                </a>
            <?php endif; ?>
        </div>

        <!-- Events list -->
        <?php if (empty($evenementsArray)): ?>
        <div class="ce-empty">
            <i class="fas fa-calendar-times"></i>
            <p style="font-size:1rem;font-weight:700;color:#444;margin-bottom:6px;">Aucun événement trouvé</p>
            <p>Aucun événement dans la catégorie "<?= htmlspecialchars($categorie['nom']) ?>"</p>
            <a href="?action=evenements_categorie&id=<?= $categorie_id ?>" style="display:inline-block;margin-top:14px;background:linear-gradient(135deg,#135D36,#2FA084);color:#fff;padding:8px 20px;border-radius:10px;font-size:.8rem;font-weight:600;text-decoration:none;">Réinitialiser</a>
        </div>
        <?php else: ?>
        <div class="ce-events">
            <?php foreach ($evenementsArray as $event):
                $placesTotal   = $event['max_participants'];
                $placesValidees= $participationC->compterParticipationsValidees($event['id']);
                $placesRest    = $placesTotal - $placesValidees;
                $pct           = $placesTotal > 0 ? round($placesValidees / $placesTotal * 100) : 0;
                $estComplet    = $placesRest <= 0;
                $estInscrit    = false;
                $statutVal     = null;
                if ($isLoggedIn) {
                    $estInscrit = $participationC->estInscrit($userId, $event['id']);
                    $statutVal  = $participationC->getStatutValidation($userId, $event['id']);
                }
            ?>
            <div class="ce-card event-card-wrapper"
                 data-id="<?= $event['id'] ?>"
                 data-title="<?= htmlspecialchars($event['titre']) ?>"
                 data-lieu="<?= htmlspecialchars($event['lieu']) ?>"
                 data-date="<?= date('d/m/Y', strtotime($event['date_evenement'])) ?>"
                 data-heure="<?= htmlspecialchars($event['heure'] ?? '') ?>"
                 data-places="<?= $placesRest ?>">
                <div class="ce-card-body">
                    <div class="ce-card-top">
                        <h5 class="ce-card-title"><?= htmlspecialchars($event['titre']) ?></h5>
                        <span class="ce-cat-pill"><?= htmlspecialchars($event['categorie_nom'] ?? $categorie['nom']) ?></span>
                    </div>
                    <div class="ce-card-meta">
                        <span class="ce-meta-item"><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($event['lieu']) ?></span>
                        <span class="ce-meta-item"><i class="fas fa-calendar-day"></i><?= date('d/m/Y', strtotime($event['date_evenement'])) ?></span>
                        <span class="ce-meta-item"><i class="fas fa-clock"></i><?= htmlspecialchars($event['heure'] ?? '—') ?></span>
                    </div>
                    <?php if (!empty($event['description'])): ?>
                    <div class="ce-card-desc"><?= htmlspecialchars($event['description']) ?></div>
                    <?php endif; ?>
                    <div class="ce-progress-row">
                        <span><i class="fas fa-users" style="color:#135D36;margin-right:4px;"></i><?= $placesValidees ?>/<?= $placesTotal ?> inscrits</span>
                        <span><?= $placesRest ?> place(s) restante(s)</span>
                    </div>
                    <div class="ce-progress-track">
                        <div class="ce-progress-fill" style="width:<?= $pct ?>%;"></div>
                    </div>
                    <div class="ce-card-actions">
                        <?php if ($estInscrit): ?>
                            <?php if ($statutVal === 'valide'): ?>
                                <div class="ce-status ce-status-ok"><i class="fas fa-check-circle"></i> Inscrit</div>
                            <?php elseif ($statutVal === 'en_attente'): ?>
                                <div class="ce-status ce-status-pending"><i class="fas fa-clock"></i> En attente</div>
                            <?php else: ?>
                                <div class="ce-status ce-status-refused"><i class="fas fa-times-circle"></i> Refusé</div>
                            <?php endif; ?>
                        <?php elseif ($estComplet): ?>
                            <div class="ce-status ce-status-full"><i class="fas fa-ban"></i> Complet</div>
                        <?php else: ?>
                            <button class="ce-btn-sub btn-inscrire"><i class="fas fa-ticket-alt"></i> S'inscrire</button>
                        <?php endif; ?>
                        <button onclick="partagerFacebook(<?= $event['id'] ?>, '<?= htmlspecialchars(addslashes($event['titre'])) ?>', '<?= htmlspecialchars(addslashes($event['lieu'])) ?>', '<?= date('d/m/Y', strtotime($event['date_evenement'])) ?>', '')" class="ce-btn-share">
                            <i class="fab fa-facebook-f"></i>
                        </button>
                        <?php if ($isAdmin): ?>
                        <div class="ce-admin-row" style="width:100%;">
                            <a href="<?= BASE_URL ?>/views/evenement/modifier.php?id=<?= $event['id'] ?>" class="ce-admin-btn ce-btn-edit"><i class="fas fa-edit"></i> Modifier</a>
                            <a href="<?= BASE_URL ?>/views/evenement/participants.php?id=<?= $event['id'] ?>" class="ce-admin-btn ce-btn-users"><i class="fas fa-users"></i> Participants</a>
                            <a href="<?= BASE_URL ?>/views/evenement/supprimer.php?id=<?= $event['id'] ?>" class="ce-admin-btn ce-btn-del" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Calendar -->
        <div class="ce-calendar-wrap">
            <div class="ce-calendar-title"><i class="fas fa-calendar-alt"></i> Calendrier des événements</div>
            <div id="calendar"></div>
        </div>

    </div><!-- /ce-main -->

    <!-- ── Right: Mes participations ── -->
    <aside class="ce-sidebar">
        <div class="ce-side-panel">
            <div class="ce-side-header">
                <i class="fas fa-ticket-alt"></i>
                <h3>Mes réservations</h3>
                <?php if ($isLoggedIn): ?>
                    <span class="count-badge"><?= count($mesParticipations) ?></span>
                <?php endif; ?>
            </div>
            <div class="ce-side-body">
                <?php if (!$isLoggedIn): ?>
                    <div class="ce-side-login">
                        <i class="fas fa-lock"></i>
                        <p>Connectez-vous pour voir vos réservations</p>
                        <a href="<?= BASE_URL ?>/index.php?route=login" class="ce-login-link">Se connecter</a>
                    </div>
                <?php elseif (empty($mesParticipations)): ?>
                    <div class="ce-no-part">
                        <i class="fas fa-calendar-times"></i>
                        <p>Vous n'avez aucune réservation pour le moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($mesParticipations as $mp): ?>
                    <div class="ce-part-item">
                        <div class="ce-part-title"><?= htmlspecialchars($mp['titre']) ?></div>
                        <div class="ce-part-meta">
                            <span><i class="fas fa-calendar-day" style="width:14px;color:#135D36;"></i> <?= date('d/m/Y', strtotime($mp['date_evenement'])) ?></span>
                            <span><i class="fas fa-map-marker-alt" style="width:14px;color:#135D36;"></i> <?= htmlspecialchars($mp['lieu']) ?></span>
                            <span><i class="fas fa-users" style="width:14px;color:#135D36;"></i> <?= (int)$mp['nombre_participants'] ?> participant(s)</span>
                        </div>
                        <div class="ce-part-foot">
                            <?php if ($mp['statut_validation'] === 'valide'): ?>
                                <span class="ce-pbadge ce-pb-ok"><i class="fas fa-check"></i> Validé</span>
                            <?php elseif ($mp['statut_validation'] === 'en_attente'): ?>
                                <span class="ce-pbadge ce-pb-wait"><i class="fas fa-clock"></i> En attente</span>
                            <?php else: ?>
                                <span class="ce-pbadge ce-pb-no"><i class="fas fa-times"></i> Refusé</span>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/index.php?action=participation_annuler&event_id=<?= (int)$mp['event_id'] ?>"
                               class="ce-cancel-btn"
                               onclick="return confirm('Annuler cette réservation ?')">
                                Annuler
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div style="text-align:center;margin-top:10px;">
                        <a href="<?= BASE_URL ?>/index.php?action=mes_participations" style="font-size:.75rem;color:#135D36;font-weight:600;text-decoration:none;">
                            Voir toutes mes participations →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </aside>

</div><!-- /ce-layout -->
</div><!-- /ce-page -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const modal    = document.getElementById('inscriptionModal');
const closeBtn = document.getElementById('closeModalBtn');
const confirmBtn = document.getElementById('confirmModalBtn');
const nbInput  = document.getElementById('nbParticipants');
let currentEventId = null, currentPlaces = 0;

function openModal(eventId, title, lieu, date, heure, places) {
    currentEventId = eventId;
    currentPlaces  = parseInt(places) || 0;
    document.getElementById('modalTitle').textContent      = title;
    document.getElementById('modalLieu').textContent       = lieu;
    document.getElementById('modalDate').textContent       = date;
    document.getElementById('modalHeure').textContent      = heure || '—';
    document.getElementById('placesRestantes').textContent = places;
    nbInput.max   = Math.min(10, currentPlaces);
    nbInput.value = 1;
    modal.style.display = 'flex';
}
function closeModal() { modal.style.display = 'none'; currentEventId = null; }

function confirmInscription() {
    if (!currentEventId) return;
    let nb = parseInt(nbInput.value);
    if (isNaN(nb) || nb < 1) nb = 1;
    if (nb > currentPlaces) { alert(`⚠️ Il ne reste que ${currentPlaces} place(s).`); return; }
    window.location.href = `<?= BASE_URL ?>/index.php?action=participation_inscrire&event_id=${currentEventId}&nb_participants=${nb}&categorie_id=<?= $categorie_id ?>`;
}

if (closeBtn)   closeBtn.onclick   = closeModal;
if (confirmBtn) confirmBtn.onclick = confirmInscription;
modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

document.querySelectorAll('.btn-inscrire').forEach(btn => {
    btn.addEventListener('click', function () {
        const w = this.closest('.event-card-wrapper');
        if (w) openModal(w.dataset.id, w.dataset.title, w.dataset.lieu, w.dataset.date, w.dataset.heure, w.dataset.places);
    });
});

// Calendar
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;
    var events = <?= json_encode($calendarEvents) ?>;
    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'fr', initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
        buttonText: { today: "Aujourd'hui", month: 'Mois', week: 'Semaine', list: 'Liste' },
        events: events,
        height: 460, firstDay: 1, dayMaxEvents: true,
        eventClick: function (info) {
            const ev = info.event, props = ev.extendedProps;
            <?php if ($isAdmin): ?>
                window.location.href = `<?= BASE_URL ?>/views/evenement/modifier.php?id=${ev.id}`;
            <?php elseif ($isLoggedIn): ?>
                if (props.est_inscrit) { alert('✅ Vous êtes déjà inscrit !'); return; }
                if (props.est_complet) { alert('❌ Événement complet.'); return; }
                openModal(ev.id, ev.title, props.lieu, ev.startStr, props.heure, props.places_restantes);
            <?php else: ?>
                alert('🔐 Connectez-vous pour vous inscrire.');
                window.location.href = '<?= BASE_URL ?>/index.php?route=login';
            <?php endif; ?>
        },
        dateClick: function (info) {
            <?php if ($isAdmin): ?>
                window.location.href = `<?= BASE_URL ?>/views/evenement/ajouter.php?categorie=<?= $categorie_id ?>&date=${info.dateStr}`;
            <?php endif; ?>
        },
        eventDidMount: function (info) {
            const p = info.event.extendedProps;
            let tip = `${info.event.title}\n📍 ${p.lieu}\n🕐 ${p.heure}\n👥 ${p.places_restantes}/${p.places_total}`;
            if (p.est_complet)  tip += '\n❌ COMPLET';
            if (p.est_inscrit)  tip += '\n✅ Inscrit';
            info.el.title = tip;
        }
    });
    calendar.render();
});

function partagerFacebook(eventId) {
    var url = '<?= BASE_URL ?>/index.php?action=evenements_categorie&id=' + eventId;
    window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url), 'fb-share', 'width=600,height=400');
}

// Auto-dismiss toast
setTimeout(() => {
    const t = document.getElementById('ceToast');
    if (t) { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; setTimeout(() => t.remove(), 300); }
}, 5000);
</script>
<?php require BASE_PATH . '/views/App/Views/layouts/footer.php'; ?>