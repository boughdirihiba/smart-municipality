<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/RendezVousController.php';

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$db   = new Database();
$conn = $db->getConnection();
$rdv  = new RendezVous($conn);

$suggestions = RendezVousController::getSuggestions($rdv, $query);
echo json_encode($suggestions);
