<?php
session_start();

// Simulation d'authentification
if(!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['nom'] = 'Admin';
    $_SESSION['prenom'] = 'Admin';
    $_SESSION['role'] = 'admin';
}

require_once 'controllers/EventController.php';
?>