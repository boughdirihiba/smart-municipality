<?php
session_start();
require_once __DIR__ . '/../../controller/ParticipationC.php';
require_once __DIR__ . '/../../model/Participation.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$eventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$nbParticipants = isset($_GET['nb_participants']) ? intval($_GET['nb_participants']) : 1;
$returnUrl = isset($_GET['return']) ? $_GET['return'] : '../../categorie_evenements.php';

if (!$eventId) {
    header('Location: ' . $returnUrl . '?error=ID événement invalide');
    exit();
}

$participationC = new ParticipationC();
$participation = new Participation($userId, $eventId, 'inscrit', $nbParticipants);

$result = $participationC->ajouterParticipation($participation);

if ($result['success']) {
    header('Location: ' . $returnUrl . '?success=inscrit&msg=' . urlencode($result['message']));
} else {
    header('Location: ' . $returnUrl . '?error=' . urlencode($result['message']));
}
exit();
?>