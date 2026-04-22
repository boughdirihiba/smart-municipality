<?php
session_start();
require_once __DIR__ . '/../../../controller/CategorieEvenementC.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../../index.php');
    exit();
}

$id = $_GET['id'] ?? 0;

// Récupérer la catégorie pour supprimer son image
$categorieC = new CategorieEvenementC();
$categorie = $categorieC->afficherCategorieParId($id);

if ($categorie && $categorie['image_url']) {
    $imagePath = __DIR__ . '/../../../' . $categorie['image_url'];
    if (file_exists($imagePath)) {
        unlink($imagePath); // Supprimer le fichier image
    }
}

$result = $categorieC->supprimerCategorie($id);

if ($result['success']) {
    header('Location: liste.php?success=1');
} else {
    header('Location: liste.php?error=' . urlencode($result['message']));
}
?>