// views/evenement/verifier_qr.php
<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementC.php';
require_once __DIR__ . '/../../controller/ParticipationC.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$qrData = $_GET['qr'] ?? '';
$qrDecoded = json_decode($qrData, true);

if ($qrDecoded) {
    $evenementC = new EvenementC();
    $participationC = new ParticipationC();
    
    $event = $evenementC->afficherEvenementParId($qrDecoded['event_id']);
    $isRegistered = $participationC->estInscrit($qrDecoded['user_id'], $qrDecoded['event_id']);
    
    if ($event && $isRegistered) {
        // Mettre à jour le statut
        echo "<h1 style='color: green'>✅ Entrée validée</h1>";
        echo "<p>Bienvenue " . htmlspecialchars($qrDecoded['user_name']) . "</p>";
        echo "<p>Événement: " . htmlspecialchars($event['titre']) . "</p>";
    } else {
        echo "<h1 style='color: red'>❌ Entrée non valide</h1>";
    }
} else {
    echo "<h1 style='color: red'>❌ QR Code invalide</h1>";
}
?>