<?php
session_start();

require_once "config/database.php";
require_once "controllers/DemandeController.php";
require_once "controllers/DocumentController.php";
require_once "controllers/ServiceController.php";
require_once "controllers/NotificationController.php";
require_once "controllers/ChatbotController.php";
require_once "controllers/RatingController.php";  // AJOUTÉ

// Récupérer l'action depuis l'URL
$action = isset($_GET['action']) ? $_GET['action'] : 'manage';

// Router
switch($action) {
    // ========== DEMANDES ==========
    case 'manage':
        $controller = new DemandeController();
        $controller->manage();
        break;
        
    case 'create':
        $controller = new DemandeController();
        $controller->create();
        break;
        
    case 'store':
        $controller = new DemandeController();
        $controller->store();
        break;
        
    case 'edit':
        $controller = new DemandeController();
        $controller->edit();
        break;
        
    case 'update':
        $controller = new DemandeController();
        $controller->update();
        break;
        
    case 'delete':
        $controller = new DemandeController();
        $controller->delete();
        break;
        
    case 'dashboard':
        $controller = new DemandeController();
        $controller->dashboard();
        break;
        
    // ========== DOCUMENTS ==========
    case 'upload_document':
        $controller = new DocumentController();
        $controller->upload();
        break;
        
    case 'upload_form':
        $controller = new DocumentController();
        $controller->uploadForm();
        break;
        
    case 'edit_document':
        $controller = new DocumentController();
        $controller->editForm();
        break;
        
    case 'update_document':
        $controller = new DocumentController();
        $controller->update();
        break;
        
    case 'delete_document':
        $controller = new DocumentController();
        $controller->delete();
        break;
        
    case 'download_document':
        $controller = new DocumentController();
        $controller->download();
        break;
        
    case 'get_documents':
        $controller = new DocumentController();
        $controller->getDocuments();
        break;
        
    // ========== SERVICES ==========
    case 'list_services':
        $controller = new ServiceController();
        $controller->list();
        break;
        
    case 'create_service':
        $controller = new ServiceController();
        $controller->createForm();
        break;
        
    case 'store_service':
        $controller = new ServiceController();
        $controller->store();
        break;
        
    case 'edit_service':
        $controller = new ServiceController();
        $controller->editForm();
        break;
        
    case 'update_service':
        $controller = new ServiceController();
        $controller->update();
        break;
        
    case 'delete_service':
        $controller = new ServiceController();
        $controller->delete();
        break;
        
    case 'ajax_search_services':
        $controller = new ServiceController();
        $controller->ajaxSearch();
        break;
        
    // ========== NOTIFICATIONS ==========
    case 'send_notification':
        $controller = new NotificationController();
        $controller->send();
        break;
        
    case 'get_notifications_count':
        $controller = new NotificationController();
        $controller->getUnreadCount();
        break;
        
    case 'get_user_notifications':
        $controller = new NotificationController();
        $controller->getUserNotifications();
        break;
        
    case 'mark_notification_read':
        $controller = new NotificationController();
        $controller->markAsRead();
        break;
        
    case 'mark_all_notifications_read':
        $controller = new NotificationController();
        $controller->markAllAsRead();
        break;
        
    case 'delete_notification':
        $controller = new NotificationController();
        $controller->delete();
        break;
        
    // ========== CHATBOT ==========
    case 'chatbot_widget':
        $controller = new ChatbotController();
        $controller->widget();
        break;
        
    case 'chatbot_send':
        $controller = new ChatbotController();
        $controller->sendMessage();
        break;
        
    case 'chatbot_suggestions':
        $controller = new ChatbotController();
        $controller->getSuggestions();
        break;
        
    case 'chatbot_history':
        $controller = new ChatbotController();
        $controller->getHistory();
        break;
        
    case 'chatbot_delete_history':
        $controller = new ChatbotController();
        $controller->deleteHistory();
        break;
        
    // ========== RATING (AJOUTÉ) ==========
    case 'add_rating':
        $controller = new RatingController();
        $controller->addOrUpdate();
        break;
        
    case 'get_ratings':
        $controller = new RatingController();
        $controller->getServiceRatings();
        break;
        
    case 'delete_rating':
        $controller = new RatingController();
        $controller->deleteRating();
        break;
        
    // ========== PAGES STATIQUES ==========
    case 'profil':
        include "views/profil.php";
        break;
        
    case 'evenements':
        include "views/evenements.php";
        break;
        
    case 'carte_intelligente':
        include "views/carte.php";
        break;
        
    case 'blog':
        include "views/blog.php";
        break;
        
    case 'rendez_vous':
        include "views/rendezvous.php";
        break;
        
    case 'switch_language':
        if(isset($_POST['lang'])) {
            $_SESSION['lang'] = $_POST['lang'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
        
    // ========== PAGE D'ACCUEIL PAR DÉFAUT ==========
    default:
        $controller = new DemandeController();
        $controller->manage();
        break;
}
?>