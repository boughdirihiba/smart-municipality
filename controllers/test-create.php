<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/RendezVous.php';

$db = new Database();
$conn = $db->getConnection();
$rdv = new RendezVous($conn);

$rdv->setUserId(1);
$rdv->setCategorieId(1);
$rdv->setDateRdv('2026-04-20');
$rdv->setHeure('15:00');
$rdv->setStatut('en_attente');

if ($rdv->create()) {
    echo "SAVED! ID = " . $rdv->getId();
} else {
    echo "FAILED TO SAVE";
}
?>