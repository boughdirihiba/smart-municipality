<?php

session_start();

require_once '../config/database.php';
require_once '../models/RendezVous.php';

$db = new Database();
$conn = $db->getConnection();

$rdv = new RendezVous($conn);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'create':
        if (!isset($_SESSION['user'])) {
            header('Location: ../views/login.php');
            exit;
        }

        $service = $_POST['service'] ?? '';
        $date_rdv = $_POST['date_rdv'] ?? '';
        $heure = $_POST['heure'] ?? '';

        if (empty($service) || empty($date_rdv) || empty($heure)) {
            $_SESSION['error'] = "Veuillez remplir tous les champs.";
            header('Location: ../views/rendez-vous.php');
            exit;
        }

        if ($rdv->isSlotTaken($service, $date_rdv, $heure)) {
            $_SESSION['error'] = "Ce créneau est déjà réservé. Veuillez en choisir un autre.";
            header('Location: ../views/rendez-vous.php?service=' . urlencode($service) . '&date=' . $date_rdv);
            exit;
        }

        $rdv->setUserId($_SESSION['user']['id']);
        $rdv->setService($service);
        $rdv->setDateRdv($date_rdv);
        $rdv->setHeure($heure);
        $rdv->setStatut('confirme');

        if ($rdv->create()) {
            $_SESSION['success'] = "Votre rendez-vous a été confirmé avec succès.";
            header('Location: ../views/rendez-vous.php');
        } else {
            $_SESSION['error'] = "Une erreur est survenue. Veuillez réessayer.";
            header('Location: ../views/rendez-vous.php?service=' . urlencode($service) . '&date=' . $date_rdv . '&heure=' . $heure);
        }
        exit;
        break;

    case 'update':
        if (!isset($_SESSION['user'])) {
            header('Location: ../views/login.php');
            exit;
        }

        $id = $_POST['id'] ?? '';
        $service = $_POST['service'] ?? '';
        $date_rdv = $_POST['date_rdv'] ?? '';
        $heure = $_POST['heure'] ?? '';

        if (empty($id) || empty($service) || empty($date_rdv) || empty($heure)) {
            $_SESSION['error'] = "Veuillez remplir tous les champs.";
            header('Location: ../views/rendez-vous.php');
            exit;
        }

        if ($rdv->isSlotTaken($service, $date_rdv, $heure)) {
            $_SESSION['error'] = "Ce créneau est déjà réservé.";
            header('Location: ../views/rendez-vous.php');
            exit;
        }

        $rdv->setId($id);
        $rdv->setService($service);
        $rdv->setDateRdv($date_rdv);
        $rdv->setHeure($heure);
        $rdv->setStatut('confirme');

        if ($rdv->update()) {
            $_SESSION['success'] = "Votre rendez-vous a été modifié avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la modification.";
        }

        header('Location: ../views/rendez-vous.php');
        exit;
        break;

    case 'delete':
        if (!isset($_SESSION['user'])) {
            header('Location: ../views/login.php');
            exit;
        }

        $id = $_GET['id'] ?? $_POST['id'] ?? '';

        if (empty($id)) {
            $_SESSION['error'] = "Rendez-vous introuvable.";
            header('Location: ../views/rendez-vous.php');
            exit;
        }

        if ($rdv->delete($id)) {
            $_SESSION['success'] = "Votre rendez-vous a été annulé.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de l'annulation.";
        }

        header('Location: ../views/rendez-vous.php');
        exit;
        break;

    default:
        header('Location: ../views/rendez-vous.php');
        exit;
        break;

}

?>