<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../controllers/RendezVousController.php';

$catIds = array_map('intval', (array)($_GET['cats'] ?? []));
$date   = trim($_GET['date'] ?? '');

if (empty($catIds) || empty($date)) {
    echo json_encode(['error' => 'Paramètres manquants']);
    exit;
}

$db   = new Database();
$conn = $db->getConnection();
$rdv  = new RendezVous($conn);

$times = RendezVousController::getAvailableTimes($rdv, $catIds, $date);
echo json_encode($times);
?>