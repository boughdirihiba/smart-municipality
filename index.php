<?php
session_start();

require_once "controllers/DemandeController.php";
require_once "controllers/DocumentController.php";
require_once "controllers/ServiceController.php";
require_once "controllers/NotificationController.php";

$demandeController = new DemandeController();
$documentController = new DocumentController();
$serviceController = new ServiceController();
$notificationController = new NotificationController();

$action = $_GET['action'] ?? 'manage';

switch($action) {

    // ========== ROUTES DEMANDES ==========
    case 'create':
        $demandeController->create();
        break;
    case 'store':
        $demandeController->store();
        break;
    case 'dashboard':
        $demandeController->dashboard();
        break;
    case 'edit':
        $demandeController->edit();
        break;
    case 'update':
        $demandeController->update();
        break;
    case 'delete':
        $demandeController->delete();
        break;
    case 'manage':
        $demandeController->manage();
        break;

    // ========== ROUTES DOCUMENTS ==========
    case 'get_documents':
        $documentController->getDocuments();
        break;
    case 'upload_document_form':
        $documentController->uploadForm();
        break;
    case 'upload_document':
        $documentController->upload();
        break;
    case 'edit_document':
        $documentController->editForm();
        break;
    case 'update_document':
        $documentController->update();
        break;
    case 'replace_document':
        $documentController->replace();
        break;
    case 'delete_document':
        $documentController->delete();
        break;
    case 'download_document':
        $documentController->download();
        break;

    // ========== ROUTES SERVICES ==========
    case 'create_service':
        $serviceController->createForm();
        break;
    case 'store_service':
        $serviceController->store();
        break;
    case 'list_services':
        $serviceController->list();
        break;
    case 'edit_service':
        $serviceController->editForm();
        break;
    case 'update_service':
        $serviceController->update();
        break;
    case 'delete_service':
        $serviceController->delete();
        break;

    // ========== ROUTES NOTIFICATIONS ==========
    case 'get_notifications_count':
        $notificationController->getUnreadCount();
        break;
    case 'get_user_notifications':
        $notificationController->getUserNotifications();
        break;
    case 'mark_notification_read':
        $notificationController->markAsRead();
        break;
    case 'mark_all_notifications_read':
        $notificationController->markAllAsRead();
        break;
    case 'send_notification_form':
        $notificationController->sendForm();
        break;
    case 'send_notification':
        $notificationController->send();
        break;
    case 'delete_notification':
        $notificationController->delete();
        break;

    // ========== ROUTE PAR DEFAUT ==========
    default:
        $demandeController->manage();
        break;
}
?>