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
    case 'manage':
        $title = 'Mes demandes'; $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        $demandeC->manage();
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;
    case 'create':
        $title = 'Nouvelle demande'; $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        $demandeC->create();
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;
    case 'store':             $demandeC->store();              break;
    case 'edit':
        $title = 'Modifier la demande'; $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        $demandeC->edit();
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;
    case 'update':            $demandeC->update();             break;
    case 'delete':            $demandeC->delete();             break;
    case 'dashboard':
        $title = 'Tableau de bord'; $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        $demandeC->dashboard();
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;

    // ─ Services ─────────────────────────────────────────────────────────────
    case 'list_services':
    case 'services_list':
        $title = 'Services'; $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        $serviceC->list();
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;
    case 'create_service':
        $title = 'Nouveau service'; $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        $serviceC->createForm();
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;
    case 'store_service':     $serviceC->store();              break;
    case 'edit_service':
        $title = 'Modifier le service'; $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        $serviceC->editForm();
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;
    case 'update_service':    $serviceC->update();             break;
    case 'delete_service':    $serviceC->delete();             break;
    case 'ajax_search_services': $serviceC->getServicesFront(); break;

    // ─ Documents ─────────────────────────────────────────────────────────────
    case 'get_documents':     $documentC->getDocuments();      break;
    case 'upload_document':
        $title = 'Ajouter un document'; $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        $documentC->uploadForm();
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;
    case 'upload':            $documentC->upload();            break;
    case 'edit_document':
        $title = 'Modifier le document'; $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        $documentC->editForm();
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;
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
        $title = 'Blog';
        $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        include __DIR__ . '/views/frontoffice.php';
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;
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

    // ─ Événements ────────────────────────────────────────────────────────────
    case 'evenements':
        require_once __DIR__ . '/controllers/CategorieEvenementC.php';
        $categorieC_list = new CategorieEvenementC();
        $rawCats = $categorieC_list->afficherCategories();
        $categories = [];
        foreach ($rawCats as $cat) {
            $cat['nb_evenements'] = $categorieC_list->compterEvenementsParCategorie($cat['id']);
            $categories[] = $cat;
        }
        $title = 'Événements'; $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        include __DIR__ . '/views/evenement/categories.php';
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;

    case 'evenements_categorie':
        require_once __DIR__ . '/controllers/EvenementC.php';
        require_once __DIR__ . '/controllers/CategorieEvenementC.php';
        require_once __DIR__ . '/controllers/ParticipationC.php';
        $evenementC_ec   = new EvenementC();
        $categorieC_ec   = new CategorieEvenementC();
        $participationC_ec = new ParticipationC();
        $categorie_id    = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($categorie_id <= 0) { header('Location: ' . BASE_URL . '/index.php?action=evenements'); exit(); }
        $categorie = $categorieC_ec->afficherCategorieParId($categorie_id);
        if (!$categorie) { header('Location: ' . BASE_URL . '/index.php?action=evenements'); exit(); }

        $tousEvenements = $evenementC_ec->afficherEvenementsAVenir();
        $evenements = array_values(array_filter($tousEvenements, function($e) use ($categorie_id) {
            return $e['categorie_id'] == $categorie_id;
        }));

        $recherche = isset($_GET['search']) ? trim($_GET['search']) : '';
        if (!empty($recherche)) {
            $evenements = array_values(array_filter($evenements, function($e) use ($recherche) {
                return stripos($e['titre'], $recherche) !== false
                    || stripos($e['description'], $recherche) !== false
                    || stripos($e['lieu'], $recherche) !== false;
            }));
        }

        $sort_by    = $_GET['sort_by'] ?? 'date';
        $sort_order = (($_GET['sort_order'] ?? '') === 'asc') ? 'asc' : 'desc';
        usort($evenements, function($a, $b) use ($sort_by, $sort_order) {
            if ($sort_by === 'titre') { $v1 = strtolower($a['titre']); $v2 = strtolower($b['titre']); }
            elseif ($sort_by === 'lieu') { $v1 = strtolower($a['lieu']); $v2 = strtolower($b['lieu']); }
            else { $v1 = strtotime($a['date_evenement']); $v2 = strtotime($b['date_evenement']); }
            return $sort_order === 'asc' ? ($v1 <=> $v2) : ($v2 <=> $v1);
        });

        $isLoggedIn  = !empty($_SESSION['user']['id']);
        $userId_ec   = $_SESSION['user']['id'] ?? null;
        $isAdmin_ec  = ($_SESSION['user']['role'] ?? '') === 'admin';

        $message_ec = ''; $messageType_ec = '';
        if (isset($_GET['success']) && $_GET['success'] === 'inscrit') {
            $message_ec = '✅ Votre inscription a été envoyée ! En attente de validation.';
            $messageType_ec = 'success';
        }
        if (isset($_GET['error'])) {
            $message_ec = '❌ ' . htmlspecialchars($_GET['error']);
            $messageType_ec = 'danger';
        }

        $nbEvenements = count($evenements);
        $totalPlaces  = array_sum(array_column($evenements, 'max_participants'));

        $calendarEvents = [];
        foreach ($evenements as $e) {
            $pr = $e['max_participants'] - $participationC_ec->compterParticipationsValidees($e['id']);
            $estComplet  = $pr <= 0;
            $estInscrit  = $isLoggedIn ? $participationC_ec->estInscrit($userId_ec, $e['id']) : false;
            $calendarEvents[] = [
                'id' => $e['id'], 'title' => $e['titre'], 'start' => $e['date_evenement'],
                'lieu' => $e['lieu'], 'heure' => $e['heure'],
                'places_restantes' => $pr, 'places_total' => $e['max_participants'],
                'est_complet' => $estComplet, 'est_inscrit' => $estInscrit,
                'color' => $estComplet ? '#9e9e9e' : ($estInscrit ? '#2e7d32' : '#1a5e2a'),
                'textColor' => 'white'
            ];
        }

        $title = htmlspecialchars($categorie['nom']); $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        include __DIR__ . '/views/evenement/categorie.php';
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;

    case 'inscrire_evenement':
        require_once __DIR__ . '/controllers/ParticipationC.php';
        require_once __DIR__ . '/models/Participation.php';
        $userId_ins  = $_SESSION['user']['id'] ?? null;
        $catId_ins   = isset($_GET['categorie_id']) ? intval($_GET['categorie_id']) : 0;
        $retUrl      = $catId_ins
            ? BASE_URL . '/index.php?action=evenements_categorie&id=' . $catId_ins
            : BASE_URL . '/index.php?action=evenements';
        if (!$userId_ins) { header('Location: ' . BASE_URL . '/index.php?route=login'); exit(); }
        $eventId_ins = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
        $nbP         = max(1, intval($_GET['nb_participants'] ?? 1));
        if (!$eventId_ins) { header('Location: ' . $retUrl . '&error=' . urlencode('ID événement invalide')); exit(); }
        $participationC_ins  = new ParticipationC();
        $participation_ins   = new Participation($userId_ins, $eventId_ins, 'inscrit', $nbP);
        $result_ins = $participationC_ins->ajouterParticipation($participation_ins);
        if ($result_ins['success']) {
            header('Location: ' . $retUrl . '&success=inscrit');
        } else {
            header('Location: ' . $retUrl . '&error=' . urlencode($result_ins['message']));
        }
        exit();

    case 'ajouter_evenement':
        require_once __DIR__ . '/controllers/EvenementC.php';
        require_once __DIR__ . '/controllers/CategorieEvenementC.php';
        $evenementC_a     = new EvenementC();
        $categorieC_a     = new CategorieEvenementC();
        $categories       = $categorieC_a->afficherCategories();
        $ev_errors        = [];
        $ev_old           = [];
        $categorie_preselect = isset($_GET['categorie']) ? intval($_GET['categorie']) : 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ev_old = [
                'titre'            => trim($_POST['titre'] ?? ''),
                'description'      => trim($_POST['description'] ?? ''),
                'max_participants' => intval($_POST['max_participants'] ?? 50),
                'lieu'             => trim($_POST['lieu'] ?? ''),
                'date_evenement'   => $_POST['date_evenement'] ?? '',
                'heure'            => $_POST['heure'] ?? '',
                'categorie_id'     => intval($_POST['categorie_id'] ?? 0),
            ];
            if (empty($ev_old['titre']))                      $ev_errors['titre']           = 'Le titre est obligatoire';
            elseif (strlen($ev_old['titre']) < 3)             $ev_errors['titre']           = 'Le titre doit contenir au moins 3 caractères';
            if (empty($ev_old['description']))                $ev_errors['description']     = 'La description est obligatoire';
            elseif (strlen($ev_old['description']) < 10)     $ev_errors['description']     = 'La description doit contenir au moins 10 caractères';
            if ($ev_old['max_participants'] < 1)              $ev_errors['max_participants']= 'Le nombre de participants doit être au moins 1';
            if (empty($ev_old['lieu']))                       $ev_errors['lieu']            = 'Le lieu est obligatoire';
            if (empty($ev_old['date_evenement'])) {
                $ev_errors['date_evenement'] = 'La date est obligatoire';
            } else {
                $_d = DateTime::createFromFormat('Y-m-d', $ev_old['date_evenement']);
                $_n = (new DateTime())->setTime(0, 0, 0);
                if (!$_d || $_d->format('Y-m-d') !== $ev_old['date_evenement']) $ev_errors['date_evenement'] = 'Format de date invalide';
                elseif ($_d < $_n)                                               $ev_errors['date_evenement'] = 'La date doit être aujourd\'hui ou dans le futur';
            }
            if (empty($ev_old['heure']))                      $ev_errors['heure'] = 'L\'heure est obligatoire';
            elseif (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $ev_old['heure'])) $ev_errors['heure'] = 'Format d\'heure invalide (HH:MM)';
            if ($ev_old['categorie_id'] <= 0)                 $ev_errors['categorie_id'] = 'Veuillez sélectionner une catégorie';

            if (empty($ev_errors)) {
                require_once BASE_PATH . '/Models/Evenement.php';
                $evObj = new Evenement($ev_old['titre'], $ev_old['description'], $ev_old['lieu'], $ev_old['date_evenement'], $ev_old['heure'], $ev_old['categorie_id'], $ev_old['max_participants']);
                if ($evenementC_a->ajouterEvenement($evObj)) {
                    set_flash('success', 'Événement ajouté avec succès.');
                    header('Location: ' . BASE_URL . '/index.php?action=evenements');
                    exit();
                }
                $ev_errors['global'] = 'Erreur lors de l\'ajout.';
            }
        }
        $title = 'Ajouter un événement'; $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        include __DIR__ . '/views/evenement/ajouter.php';
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;

    case 'modifier_evenement':
        require_once __DIR__ . '/controllers/EvenementC.php';
        require_once __DIR__ . '/controllers/CategorieEvenementC.php';
        $evenementC_m = new EvenementC();
        $categorieC_m = new CategorieEvenementC();
        $ev_id        = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($ev_id <= 0) { header('Location: ' . BASE_URL . '/index.php?action=evenements'); exit(); }
        $ev_evenement = $evenementC_m->afficherEvenementParId($ev_id);
        if (!$ev_evenement) { header('Location: ' . BASE_URL . '/index.php?action=evenements'); exit(); }
        $categories = $categorieC_m->afficherCategories();
        $ev_errors  = [];
        $ev_old     = [
            'titre'            => $ev_evenement['titre'],
            'description'      => $ev_evenement['description'],
            'max_participants' => $ev_evenement['max_participants'],
            'lieu'             => $ev_evenement['lieu'],
            'date_evenement'   => $ev_evenement['date_evenement'],
            'heure'            => $ev_evenement['heure'],
            'categorie_id'     => $ev_evenement['categorie_id'],
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ev_old = [
                'titre'            => trim($_POST['titre'] ?? ''),
                'description'      => trim($_POST['description'] ?? ''),
                'max_participants' => intval($_POST['max_participants'] ?? 50),
                'lieu'             => trim($_POST['lieu'] ?? ''),
                'date_evenement'   => $_POST['date_evenement'] ?? '',
                'heure'            => $_POST['heure'] ?? '',
                'categorie_id'     => intval($_POST['categorie_id'] ?? 0),
            ];
            if (empty($ev_old['titre']))                      $ev_errors['titre']           = 'Le titre est obligatoire';
            elseif (strlen($ev_old['titre']) < 3)             $ev_errors['titre']           = 'Le titre doit contenir au moins 3 caractères';
            if (empty($ev_old['description']))                $ev_errors['description']     = 'La description est obligatoire';
            elseif (strlen($ev_old['description']) < 10)     $ev_errors['description']     = 'La description doit contenir au moins 10 caractères';
            if ($ev_old['max_participants'] < 1)              $ev_errors['max_participants']= 'Le nombre de participants doit être au moins 1';
            if (empty($ev_old['lieu']))                       $ev_errors['lieu']            = 'Le lieu est obligatoire';
            if (empty($ev_old['date_evenement'])) {
                $ev_errors['date_evenement'] = 'La date est obligatoire';
            } else {
                $_d = DateTime::createFromFormat('Y-m-d', $ev_old['date_evenement']);
                if (!$_d || $_d->format('Y-m-d') !== $ev_old['date_evenement']) $ev_errors['date_evenement'] = 'Format de date invalide';
            }
            if (empty($ev_old['heure']))                      $ev_errors['heure'] = 'L\'heure est obligatoire';
            elseif (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $ev_old['heure'])) $ev_errors['heure'] = 'Format d\'heure invalide (HH:MM)';
            if ($ev_old['categorie_id'] <= 0)                 $ev_errors['categorie_id'] = 'Veuillez sélectionner une catégorie';

            if (empty($ev_errors)) {
                require_once BASE_PATH . '/Models/Evenement.php';
                $evObj = new Evenement($ev_old['titre'], $ev_old['description'], $ev_old['lieu'], $ev_old['date_evenement'], $ev_old['heure'], $ev_old['categorie_id'], $ev_old['max_participants']);
                if ($evenementC_m->modifierEvenement($evObj, $ev_id)) {
                    set_flash('success', 'Événement modifié avec succès.');
                    header('Location: ' . BASE_URL . '/index.php?action=evenements');
                    exit();
                }
                $ev_errors['global'] = 'Erreur lors de la modification.';
            }
        }
        $title = 'Modifier l\'événement'; $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        include __DIR__ . '/views/evenement/modifier.php';
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;

    case 'supprimer_evenement':
        include __DIR__ . '/views/evenement/supprimer.php';
        break;

    case 'participants_evenement':
        require_once __DIR__ . '/controllers/EvenementC.php';
        require_once __DIR__ . '/controllers/ParticipationC.php';
        $evenementC_p   = new EvenementC();
        $participationC = new ParticipationC();
        $event_id       = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($event_id <= 0) { header('Location: ' . BASE_URL . '/index.php?action=evenements'); exit(); }
        $evenement = $evenementC_p->afficherEvenementParId($event_id);
        if (!$evenement) { header('Location: ' . BASE_URL . '/index.php?action=evenements'); exit(); }

        $message = ''; $messageType = ''; $generatedTicket = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['participation_id'])) {
            $participation_id = intval($_POST['participation_id']);
            if ($_POST['action'] === 'valider') {
                $result = $participationC->validerParticipation($participation_id);
                $message     = $result['success'] ? '✅ Participation validée avec succès !' : '❌ Erreur: ' . $result['message'];
                $messageType = $result['success'] ? 'success' : 'danger';
                if ($result['success']) $generatedTicket = $result;
            } elseif ($_POST['action'] === 'refuser') {
                $commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : null;
                $result      = $participationC->refuserParticipation($participation_id, $commentaire);
                $message     = $result['success'] ? '⚠️ Participation refusée' : '❌ Erreur: ' . $result['message'];
                $messageType = $result['success'] ? 'warning' : 'danger';
            }
            header('Location: ' . BASE_URL . '/index.php?action=participants_evenement&id=' . $event_id . '&msg=' . urlencode($message) . '&type=' . $messageType);
            exit();
        }

        if (isset($_GET['msg'])) { $message = htmlspecialchars($_GET['msg']); $messageType = $_GET['type'] ?? 'info'; }

        $participations = $participationC->getParticipationsByEvent($event_id);
        $totalInscrits  = array_sum(array_column($participations, 'nombre_participants'));
        $totalValides   = $participationC->compterParticipationsValidees($event_id);
        $totalAttente   = $participationC->compterParticipationsEnAttente($event_id);
        $totalRefuses   = 0;
        foreach ($participations as $p) { if ($p['statut_validation'] == 'refuse') $totalRefuses += $p['nombre_participants']; }

        $title = 'Participants'; $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        include __DIR__ . '/views/evenement/participants.php';
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;
    case 'carte_intelligente':
        redirect('home/index');
        break;
    case 'rendez_vous':
        $title = 'Rendez-vous';
        $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        include __DIR__ . '/views/frontoffice/rendez-vous.php';
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
        break;

    case 'rdv_create_multi':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/controllers/RendezVousController.php';
        $userId_rdv = $_SESSION['user']['id'] ?? 0;
        if (!$userId_rdv) {
            set_flash('error', 'Vous devez être connecté pour prendre un rendez-vous.');
            header('Location: ' . BASE_URL . '/index.php?action=rendez_vous'); exit();
        }
        $db_rdv  = new Database(); $conn_rdv = $db_rdv->getConnection(); $rdv_o = new RendezVous($conn_rdv);
        $date_rdv_c = trim($_POST['date_rdv'] ?? '');
        $slots_c    = $_POST['slots'] ?? [];
        if (empty($date_rdv_c) || empty($slots_c)) {
            set_flash('error', 'Données manquantes.'); header('Location: ' . BASE_URL . '/index.php?action=rendez_vous'); exit();
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
        $userId_rdv = $_SESSION['user']['id'] ?? 0;
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
        $rdv_data      = RendezVousController::readOne($rdv_o, $id_rdv);
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
