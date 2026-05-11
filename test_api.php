<?php
// test_api.php
require_once 'controllers/ChatbotController.php';
$controller = new ChatbotController();

if ($_GET['action'] === 'sendMessage') {
    // Simule un message
    $_POST['message'] = 'Bonjour';
    $controller->sendMessage();
} elseif ($_GET['action'] === 'ping') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'API accessible']);
    exit;
}