<?php
session_start();
require_once __DIR__ . '/../../controller/ParticipationC.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php?error=Vous devez être connecté');
    exit();
}

// Récupérer l'ID de l'événement
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if ($event_id <= 0) {
    header('Location: mes_participations.php?error=Événement invalide');
    exit();
}

// Annuler la participation
$participationC = new ParticipationC();
$result = $participationC->annulerParticipation($_SESSION['user_id'], $event_id);

if ($result['success']) {
    header('Location: mes_participations.php?success=annule');
} else {
    header('Location: mes_participations.php?error=' . urlencode($result['message']));
}
exit();
?>