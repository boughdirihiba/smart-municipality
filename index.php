<?php
require_once "controllers/DemandeController.php";

$controller = new DemandeController();

$action = $_GET['action'] ?? 'manage';

switch($action) {

    case 'create':
        $controller->create();
        break;

    case 'store':
        $controller->store();
        break;

    case 'dashboard':
        $controller->dashboard();
        break;

    case 'edit':
        $controller->edit();
        break;

    case 'update':
        $controller->update();
        break;

    case 'delete':
        $controller->delete();
        break;

    default:
        $controller->manage();
        break;
}
?>