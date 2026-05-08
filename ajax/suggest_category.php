<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../controllers/RendezVousController.php';

$db   = new Database();
$conn = $db->getConnection();
$rdv  = new RendezVous($conn);

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$suggestions = RendezVousController::getSuggestions($rdv, $query);
echo json_encode($suggestions);
?>