<?php
session_start();
require_once __DIR__ . '/../../controller/ParticipationC.php';
require_once __DIR__ . '/../../controller/EvenementC.php';
require_once __DIR__ . '/../../model/Participation.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php?error=Connexion requise');
    exit();
}

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$nb_participants = isset($_GET['nb_participants']) ? intval($_GET['nb_participants']) : 1;

if ($event_id <= 0) {
    header('Location: ../../index.php?error=Événement invalide');
    exit();
}

if ($nb_participants < 1 || $nb_participants > 10) {
    header('Location: ../../index.php?error=Nombre de participants invalide (1 à 10)');
    exit();
}

$evenementC = new EvenementC();
$evenement = $evenementC->afficherEvenementParId($event_id);

if (!$evenement) {
    header('Location: ../../index.php?error=Événement non trouvé');
    exit();
}

$participationC = new ParticipationC();

if ($participationC->estInscrit($_SESSION['user_id'], $event_id)) {
    header('Location: ../../index.php?error=Vous êtes déjà inscrit');
    exit();
}

$placesValidees = $participationC->compterParticipationsValidees($event_id);
$placesRestantes = $evenement['max_participants'] - $placesValidees;

if ($nb_participants > $placesRestantes) {
    header("Location: ../../index.php?error=Plus que $placesRestantes places disponibles");
    exit();
}

// Créer la participation (sans ticket)
$participation = new Participation($_SESSION['user_id'], $event_id, 'inscrit', $nb_participants);
$result = $participationC->ajouterParticipation($participation);

if ($result['success']) {
    // Redirection simple - pas de ticket ici
    header('Location: ../../index.php?success=inscrit');
} else {
    header('Location: ../../index.php?error=' . urlencode($result['message']));
}
exit();
?>