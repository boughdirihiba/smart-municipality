<?php
/**
 * ajax/rdv_submit.php
 * Dedicated AJAX endpoint for RDV creation.
 * Place this file in your ajax/ directory alongside available_dates.php.
 *
 * Called by confirmRdv() in rendez-vous.php via:
 *   fetch(BASE_URL + '/ajax/rdv_submit.php', { method:'POST', body: formData })
 */

// ── Clean every output buffer the router may have opened ─────────────────────
while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

// ── Bootstrap ────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Adjust these paths if your directory structure differs.
// Assuming: ajax/ and config/ and controllers/ and models/ are siblings.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/RendezVous.php';
require_once __DIR__ . '/../controllers/RendezVousController.php';

// ── Validate request ─────────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['ajax'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
    exit;
}

if (empty($_SESSION['user']['id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Session expirée. Veuillez vous reconnecter.']);
    exit;
}

// ── Handle the submission ────────────────────────────────────────────────────
$db   = new Database();
$conn = $db->getConnection();
$rdv  = new RendezVous($conn);

$date_rdv = trim($_POST['date_rdv'] ?? '');
$slots    = $_POST['slots'] ?? [];

if (empty($date_rdv) || empty($slots)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Données manquantes (date ou créneaux).']);
    exit;
}

$created      = 0;
$errors       = 0;
$createdSlots = [];

foreach ($slots as $slotStr) {
    [$cat_id, $heure] = explode('|', $slotStr, 2);
    $cat_id = (int)$cat_id;

    if (RendezVousController::isSlotTaken($rdv, $cat_id, $date_rdv, $heure)) {
        $errors++;
        continue;
    }

    $rdv->setUserId($_SESSION['user']['id']);
    $rdv->setCategorieId($cat_id);
    $rdv->setDateRdv($date_rdv);
    $rdv->setHeure($heure);
    $rdv->setStatut('en_attente');

    if (RendezVousController::create($rdv)) {
        $created++;
        $stmtCat = $conn->prepare("SELECT nom FROM categories WHERE id = :id");
        $stmtCat->execute([':id' => $cat_id]);
        $catRow         = $stmtCat->fetch(PDO::FETCH_ASSOC);
        $createdSlots[] = ['service' => $catRow['nom'] ?? 'Service', 'heure' => $heure];
    } else {
        $errors++;
    }
}

ob_clean();

if ($created > 0) {
    $msg = $errors === 0
        ? "$created rendez-vous enregistré(s) avec succès."
        : "$created rendez-vous enregistré(s). $errors créneau(x) ignoré(s).";
    echo json_encode(['success' => true, 'message' => $msg, 'created' => $created]);
} else {
    echo json_encode(['success' => false, 'message' => 'Tous les créneaux sont déjà pris.']);
}
exit;
