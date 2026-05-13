<?php
// Dedicated chatbot AJAX endpoint — bypasses legacy_router.php overhead
require __DIR__ . '/config/config.php';

ini_set('display_errors', '0');
while (ob_get_level() > 0) { ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');

if (!\Config\Auth::check()) {
    echo json_encode(['success' => false, 'response' => 'Non authentifié']);
    exit;
}

require_once __DIR__ . '/controllers/ChatbotController.php';
$chatbot = new ChatbotController();
$chatbot->sendMessage();
