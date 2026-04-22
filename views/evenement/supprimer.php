<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementC.php';

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$id = $_GET['id'] ?? 0;

$evenementC = new EvenementC();
$result = $evenementC->supprimerEvenement($id);

if ($result['success']) {
    header('Location: ../../index.php?success=suppr');
} else {
    header('Location: ../../index.php?error=' . urlencode($result['message']));
}
exit();
?>