<?php
require_once __DIR__ . '/../../controllers/EvenementC.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$evenementC = new EvenementC();
$result = $evenementC->supprimerEvenement($id);
if ($result['success']) {
    set_flash('success', 'Événement supprimé avec succès.');
} else {
    set_flash('error', $result['message'] ?? 'Erreur lors de la suppression.');
}
header('Location: ' . BASE_URL . '/index.php?action=evenements');
exit();
