<?php
session_start();
require_once __DIR__ . '/../../controller/ParticipationC.php';
require_once __DIR__ . '/../../controller/EvenementC.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php?error=Vous devez être connecté pour vous inscrire');
    exit();
}

// Récupérer l'ID de l'événement
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if ($event_id <= 0) {
    header('Location: ../../index.php?error=Événement invalide');
    exit();
}

// Vérifier si l'événement existe
$evenementC = new EvenementC();
$evenement = $evenementC->afficherEvenementParId($event_id);

if (!$evenement) {
    header('Location: ../../index.php?error=Événement non trouvé');
    exit();
}

// Vérifier si déjà inscrit
$participationC = new ParticipationC();
$dejaInscrit = $participationC->estInscrit($_SESSION['user_id'], $event_id);

if ($dejaInscrit) {
    header('Location: ../../index.php?error=Vous êtes déjà inscrit à cet événement');
    exit();
}

// Ajouter la participation
require_once __DIR__ . '/../../model/Participation.php';
$participation = new Participation($_SESSION['user_id'], $event_id);
$result = $participationC->ajouterParticipation($participation);

if ($result['success']) {
    header('Location: ../../index.php?success=inscrit');
} else {
    header('Location: ../../index.php?error=' . urlencode($result['message']));
}
exit();
?>