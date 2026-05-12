<?php
// ─── LEGACY ACTION ROUTER ────────────────────────────────────────────────────
// Handles ?action=xxx for all legacy controllers.
// This is separate from the new ?route=controller/action MVC system.

$action = $_POST['action'] ?? $_GET['action'] ?? '';
if (empty($action)) { return false; }  // tell caller: not handled here

// ── Demandes ────────────────────────────────────────────────────────────────
require_once __DIR__ . '/controllers/DemandeController.php';
$demandeC = new DemandeController();

// ── Services ────────────────────────────────────────────────────────────────
require_once __DIR__ . '/controllers/ServiceController.php';
$serviceC = new ServiceController();

// ── Documents ───────────────────────────────────────────────────────────────
require_once __DIR__ . '/controllers/DocumentController.php';
$documentC = new DocumentController();

// ── Notifications ────────────────────────────────────────────────────────────
require_once __DIR__ . '/controllers/NotificationController.php';
$notifC = new NotificationController();

// ── Blog ─────────────────────────────────────────────────────────────────────
require_once __DIR__ . '/controllers/BlogController.php';
$blogC = new BlogController();

// ── Rating ───────────────────────────────────────────────────────────────────
require_once __DIR__ . '/controllers/RatingController.php';
$ratingC = new RatingController();

// ── Chatbot ──────────────────────────────────────────────────────────────────
require_once __DIR__ . '/controllers/ChatbotController.php';
$chatbotC = new ChatbotController();

switch ($action) {
    // ─ Demandes ─────────────────────────────────────────────────────────────
    case 'manage':            $demandeC->manage();             break;
    case 'create':            $demandeC->create();             break;
    case 'store':             $demandeC->store();              break;
    case 'edit':              $demandeC->edit();               break;
    case 'update':            $demandeC->update();             break;
    case 'delete':            $demandeC->delete();             break;
    case 'dashboard':         $demandeC->dashboard();          break;

    // ─ Services ─────────────────────────────────────────────────────────────
    case 'list_services':
    case 'services_list':     $serviceC->list();               break;
    case 'create_service':    $serviceC->createForm();         break;
    case 'store_service':     $serviceC->store();              break;
    case 'edit_service':      $serviceC->editForm();           break;
    case 'update_service':    $serviceC->update();             break;
    case 'delete_service':    $serviceC->delete();             break;
    case 'ajax_search_services': $serviceC->getServicesFront(); break;

    // ─ Documents ─────────────────────────────────────────────────────────────
    case 'get_documents':     $documentC->getDocuments();      break;
    case 'upload_document':   $documentC->uploadForm();        break;
    case 'upload':            $documentC->upload();            break;
    case 'edit_document':     $documentC->editForm();          break;
    case 'update_document':   $documentC->update();            break;
    case 'replace_document':  $documentC->replace();           break;
    case 'delete_document':   $documentC->delete();            break;
    case 'download_document': $documentC->download();          break;

    // ─ Notifications ─────────────────────────────────────────────────────────
    case 'get_notifications_count':     $notifC->getUnreadCount();       break;
    case 'get_user_notifications':      $notifC->getUserNotifications(); break;
    case 'mark_notification_read':      $notifC->markAsRead();           break;
    case 'mark_all_notifications_read': $notifC->markAllAsRead();        break;
    case 'notification_form':           $notifC->sendForm();             break;
    case 'notification_send':           $notifC->send();                 break;
    case 'delete_notification':         $notifC->delete();               break;

    // ─ Blog ──────────────────────────────────────────────────────────────────
    case 'blog':
        include __DIR__ . '/views/frontoffice.php';
        break;
    case 'getpost':
        $title = 'Blog';
        $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        include __DIR__ . '/views/blog_single.php';
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;
    case 'login':              $blogC->login($_POST);            break;
    case 'logout':             $blogC->logout();                 break;
    case 'createPost':         $blogC->createPost($_POST, $_FILES); break;
    case 'updatePost':         $blogC->updatePost($_POST, $_FILES); break;
    case 'deletePost':         $blogC->deletePost($_POST);        break;
    case 'createComment':      $blogC->createComment($_POST);     break;
    case 'updateComment':      $blogC->updateComment($_POST);     break;
    case 'deleteComment':      $blogC->deleteComment($_POST);     break;
    case 'setLanguage':        $blogC->setLanguage();             break;
    case 'setFontSize':        $blogC->setFontSize();        break;
    case 'ajaxCreateComment':   $blogC->ajaxCreateComment();   break;
    case 'ajaxDeleteComment':   $blogC->ajaxDeleteComment();   break;
    case 'ajaxUpdateComment':   $blogC->ajaxUpdateComment();   break;
    case 'ajaxDeletePost':      $blogC->ajaxDeletePost();      break;
    case 'ajaxReactToPost':     $blogC->ajaxReactToPost();     break;
    case 'getReactionsList':    $blogC->getReactionsList();    break;
    case 'searchAjax':          $blogC->searchAjax();          break;
    case 'getSpeakText':        $blogC->getSpeakText();        break;
    case 'setTheme':            $blogC->setTheme();            break;

    // ─ Ratings ───────────────────────────────────────────────────────────────
    case 'add_rating':          $ratingC->addOrUpdate();       break;
    case 'get_ratings':         $ratingC->getServiceRatings(); break;
    case 'delete_rating':       $ratingC->deleteRating();      break;

    // ─ Chatbot ───────────────────────────────────────────────────────────────
    case 'chatbot_message':     $chatbotC->sendMessage();      break;
    case 'chatbot_suggestions': $chatbotC->getSuggestions();   break;

    // ─ Navigation (serve views directly) ─────────────────────────────────────
    case 'evenements':
        require_once __DIR__ . '/controllers/CategorieEvenementC.php';
        require_once __DIR__ . '/controllers/EvenementC.php';
        $catC_ev = new CategorieEvenementC();
        $rawCats_ev = $catC_ev->afficherCategories();
        $categories = [];
        foreach ($rawCats_ev as $cat_ev) {
            $cat_ev['nb_evenements'] = $catC_ev->compterEvenementsParCategorie($cat_ev['id']);
            $categories[] = $cat_ev;
        }
        $title = 'Événements'; $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        include __DIR__ . '/views/evenement/categories.php';
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;

    case 'evenements_categorie':
        include __DIR__ . '/categorie_evenements.php';
        break;

    case 'carte_intelligente':
        redirect('home/index');
        break;

    case 'rendez_vous':
        include __DIR__ . '/views/frontoffice/rendez-vous.php';
        break;

    case 'rdv_create_multi':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/controllers/RendezVousController.php';
        $userId_rdv = (int)($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0);
        if (!$userId_rdv) {
            set_flash('error', 'Vous devez être connecté pour prendre un rendez-vous.');
            header('Location: ' . BASE_URL . '/index.php?action=rendez_vous'); exit();
        }
        $db_rdv = new Database(); $conn_rdv = $db_rdv->getConnection(); $rdv_o = new RendezVous($conn_rdv);
        $date_rdv_c = trim($_POST['date_rdv'] ?? '');
        $slots_c    = $_POST['slots'] ?? [];
        if (empty($date_rdv_c) || empty($slots_c)) {
            set_flash('error', 'Données manquantes.');
            header('Location: ' . BASE_URL . '/index.php?action=rendez_vous'); exit();
        }
        $created_c = 0; $errors_c = 0;
        foreach ($slots_c as $slotStr) {
            [$cat_s, $heure_s] = explode('|', $slotStr, 2);
            $cat_s = (int)$cat_s;
            if (RendezVousController::isSlotTaken($rdv_o, $cat_s, $date_rdv_c, $heure_s)) { $errors_c++; continue; }
            $rdv_o->setUserId($userId_rdv); $rdv_o->setCategorieId($cat_s);
            $rdv_o->setDateRdv($date_rdv_c); $rdv_o->setHeure($heure_s); $rdv_o->setStatut('en_attente');
            if (RendezVousController::create($rdv_o)) { $created_c++; } else { $errors_c++; }
        }
        if ($created_c > 0) {
            $msg_c = $errors_c === 0
                ? "$created_c rendez-vous enregistré(s) avec succès. En attente de confirmation."
                : "$created_c enregistré(s), $errors_c créneau(x) déjà pris — ignoré(s).";
            set_flash('success', $msg_c);
        } else {
            set_flash('error', 'Tous les créneaux sont déjà pris. Veuillez choisir un autre horaire.');
        }
        header('Location: ' . BASE_URL . '/index.php?action=rendez_vous'); exit();

    case 'rdv_delete':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/controllers/RendezVousController.php';
        $db_rdv = new Database(); $conn_rdv = $db_rdv->getConnection(); $rdv_o = new RendezVous($conn_rdv);
        $id_rdv = (int)($_GET['id'] ?? 0);
        if (!$id_rdv) { set_flash('error', 'Rendez-vous introuvable.'); header('Location: ' . BASE_URL . '/index.php?action=rendez_vous'); exit(); }
        if (RendezVousController::delete($rdv_o, $id_rdv)) {
            set_flash('success', 'Rendez-vous supprimé.');
        } else {
            set_flash('error', 'Erreur lors de la suppression.');
        }
        header('Location: ' . BASE_URL . '/index.php?action=rendez_vous'); exit();

    case 'rdv_edit':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/controllers/RendezVousController.php';
        $db_rdv = new Database(); $conn_rdv = $db_rdv->getConnection(); $rdv_o = new RendezVous($conn_rdv);
        $id_rdv = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$id_rdv) { header('Location: ' . BASE_URL . '/index.php?action=rendez_vous'); exit(); }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rdv_o->setId($id_rdv);
            $rdv_o->setCategorieId((int)($_POST['categorie_id'] ?? 0));
            $rdv_o->setDateRdv(trim($_POST['date_rdv'] ?? ''));
            $rdv_o->setHeure(trim($_POST['heure'] ?? '') . ':00');
            $rdv_o->setStatut('en_attente');
            if (RendezVousController::update($rdv_o)) {
                set_flash('success', 'Rendez-vous modifié avec succès.');
            } else {
                set_flash('error', 'Erreur lors de la modification.');
            }
            header('Location: ' . BASE_URL . '/index.php?action=rendez_vous'); exit();
        }
        $rdv_data = RendezVousController::readOne($rdv_o, $id_rdv);
        if (!$rdv_data) { header('Location: ' . BASE_URL . '/index.php?action=rendez_vous'); exit(); }
        $categories_edit = RendezVousController::getAllCategories($rdv_o);
        $title = 'Modifier le rendez-vous'; $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        include __DIR__ . '/views/frontoffice/rdv-edit.php';
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;

    case 'profil':
        redirect('home/index');
        break;

    default:
        return false;   // tell caller: unknown action
}
return true;    // handled
