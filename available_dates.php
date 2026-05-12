<?php
header('Content-Type: application/json; charset=utf-8');

// FIX: use absolute path so this works from any context
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/RendezVousController.php';

$catIds = array_map('intval', (array)($_GET['cats'] ?? []));
$heure  = trim($_GET['heure'] ?? '');

if (empty($catIds) || empty($heure)) {
    echo json_encode(['error' => 'Paramètres manquants']);
    exit;
}

if (strlen($heure) == 5) $heure .= ':00';

$db   = new Database();
$conn = $db->getConnection();
$rdv  = new RendezVous($conn);

$dates = RendezVousController::getAvailableDates($rdv, $catIds, $heure, 60);
echo json_encode($dates);
