<?php
session_start();
require_once __DIR__ . '/ChatbotC.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message vide']);
    exit();
}

$chatbotC = new ChatbotC();
$userId = $_SESSION['user_id'] ?? null;

$result = $chatbotC->traiterMessage($message, $userId);

echo json_encode([
    'success' => $result['success'],
    'message' => $result['message'],
    'events' => $result['events'] ?? []
]);
?>