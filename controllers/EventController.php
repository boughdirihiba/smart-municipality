<?php
require_once 'config/database.php';
require_once 'models/Event.php';

class EventController {
    private $event;
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->event = new Event($this->conn);
    }

    private function checkAdmin() {
        if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: index.php");
            exit();
        }
    }

    // Page de gestion des événements (CRUD)
    public function manage() {
        $this->checkAdmin();
        
        $categorie = isset($_GET['categorie']) ? $_GET['categorie'] : 'all';
        $stmt = $this->event->filterByCategory($categorie);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        include __DIR__ . '/../views/events/manage.php';
    }

    // Page Dashboard (statistiques)
    public function dashboard() {
        $this->checkAdmin();
        
        $stmt = $this->event->readAll();
        $all_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stats = $this->calculateStats($all_events);
        
        $today = date('Y-m-d');
        $upcoming_events = array_filter($all_events, function($event) use ($today) {
            return $event['date_evenement'] >= $today;
        });
        
        usort($upcoming_events, function($a, $b) {
            return strcmp($a['date_evenement'], $b['date_evenement']);
        });
        
        include __DIR__ . '/../views/events/dashboard.php';
    }

    // Calculer les statistiques
    private function calculateStats($events) {
        $stats = [
            'total' => count($events),
            'upcoming' => 0,
            'past' => 0,
            'by_category' => [
                'Culture' => 0,
                'Sport' => 0,
                'Environnement' => 0,
                'Social' => 0,
                'Education' => 0
            ]
        ];
        
        $today = date('Y-m-d');
        foreach($events as $event) {
            if($event['date_evenement'] >= $today) {
                $stats['upcoming']++;
            } else {
                $stats['past']++;
            }
            
            $categorie = $event['categorie'];
            if(isset($stats['by_category'][$categorie])) {
                $stats['by_category'][$categorie]++;
            }
        }
        
        // Taux de participation
        $stats['participation_rate'] = $stats['total'] > 0 ? round(($stats['upcoming'] / $stats['total']) * 100) : 0;
        
        // Catégories actives
        $stats['active_categories'] = 0;
        foreach($stats['by_category'] as $count) {
            if($count > 0) $stats['active_categories']++;
        }
        
        // Catégorie la plus populaire
        $max_cat = max($stats['by_category']);
        $stats['popular_category'] = $max_cat > 0 ? array_search($max_cat, $stats['by_category']) : 'Aucune';
        
        // Moyenne par mois
        $stats['avg_per_month'] = $stats['total'] > 0 ? round($stats['total'] / 3) : 0;
        
        return $stats;
    }

    // Formulaire d'ajout
    public function create() {
        $this->checkAdmin();
        
        $errors = [];
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $errors = $this->validateEventData($_POST);
            
            if(empty($errors)) {
                $this->event->titre = $_POST['titre'];
                $this->event->description = $_POST['description'];
                $this->event->lieu = $_POST['lieu'];
                $this->event->date_evenement = $_POST['date_evenement'];
                $this->event->heure = $_POST['heure'];
                $this->event->categorie = $_POST['categorie'];
                
                if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = "uploads/events/";
                    if(!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $image_name = time() . '_' . uniqid() . '.' . $extension;
                    $target_file = $target_dir . $image_name;
                    
                    if(move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $this->event->image_url = $target_file;
                    }
                }
                
                if($this->event->create()) {
                    $_SESSION['success'] = "Événement créé avec succès !";
                    header("Location: index.php?action=manage");
                    exit();
                } else {
                    $errors['general'] = "Erreur lors de la création.";
                }
            }
        }
        
        include __DIR__ . '/../views/events/create.php';
    }

    // Formulaire de modification
    public function edit() {
        $this->checkAdmin();
        
        if(!isset($_GET['id'])) {
            header("Location: index.php?action=manage");
            exit();
        }
        
        $this->event->id = $_GET['id'];
        $this->event->readOne();
        
        $errors = [];
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $errors = $this->validateEventData($_POST, true);
            
            if(empty($errors)) {
                $this->event->titre = $_POST['titre'];
                $this->event->description = $_POST['description'];
                $this->event->lieu = $_POST['lieu'];
                $this->event->date_evenement = $_POST['date_evenement'];
                $this->event->heure = $_POST['heure'];
                $this->event->categorie = $_POST['categorie'];
                
                if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = "uploads/events/";
                    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $image_name = time() . '_' . uniqid() . '.' . $extension;
                    $target_file = $target_dir . $image_name;
                    
                    if(move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $this->event->image_url = $target_file;
                    }
                }
                
                if($this->event->update()) {
                    $_SESSION['success'] = "Événement modifié avec succès !";
                    header("Location: index.php?action=manage");
                    exit();
                } else {
                    $errors['general'] = "Erreur lors de la modification.";
                }
            }
        }
        
        include __DIR__ . '/../views/events/edit.php';
    }

    // Suppression
    public function delete() {
        $this->checkAdmin();
        
        if(isset($_GET['id'])) {
            $this->event->id = $_GET['id'];
            if($this->event->delete()) {
                $_SESSION['success'] = "Événement supprimé avec succès !";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression.";
            }
        }
        
        header("Location: index.php?action=manage");
        exit();
    }

    // Validation des données
    private function validateEventData($data, $isEdit = false) {
        $errors = [];
        
        if(empty($data['titre'])) {
            $errors['titre'] = "Le titre est requis.";
        } elseif(strlen($data['titre']) < 5) {
            $errors['titre'] = "Le titre doit contenir au moins 5 caractères.";
        } elseif(strlen($data['titre']) > 100) {
            $errors['titre'] = "Le titre ne peut pas dépasser 100 caractères.";
        }
        
        if(empty($data['description'])) {
            $errors['description'] = "La description est requise.";
        } elseif(strlen($data['description']) < 10) {
            $errors['description'] = "La description doit contenir au moins 10 caractères.";
        } elseif(strlen($data['description']) > 500) {
            $errors['description'] = "La description ne peut pas dépasser 500 caractères.";
        }
        
        if(empty($data['lieu'])) {
            $errors['lieu'] = "Le lieu est requis.";
        } elseif(strlen($data['lieu']) < 3) {
            $errors['lieu'] = "Le lieu doit contenir au moins 3 caractères.";
        } elseif(strlen($data['lieu']) > 100) {
            $errors['lieu'] = "Le lieu ne peut pas dépasser 100 caractères.";
        }
        
        if(empty($data['date_evenement'])) {
            $errors['date_evenement'] = "La date est requise.";
        } else {
            $date = DateTime::createFromFormat('Y-m-d', $data['date_evenement']);
            $today = new DateTime();
            $today->setTime(0, 0, 0);
            
            if(!$date || $date->format('Y-m-d') != $data['date_evenement']) {
                $errors['date_evenement'] = "Format de date invalide.";
            } elseif ($date < $today) {
                $errors['date_evenement'] = "La date doit être supérieure ou égale à aujourd'hui.";
            }
        }
        
        if(empty($data['heure'])) {
            $errors['heure'] = "L'heure est requise.";
        } elseif(!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $data['heure'])) {
            $errors['heure'] = "Format d'heure invalide (HH:MM).";
        }
        
        $allowed_categories = ['Culture', 'Sport', 'Environnement', 'Social', 'Education'];
        if(empty($data['categorie'])) {
            $errors['categorie'] = "La catégorie est requise.";
        } elseif(!in_array($data['categorie'], $allowed_categories)) {
            $errors['categorie'] = "Catégorie invalide.";
        }
        
        return $errors;
    }
}

// Routing
$controller = new EventController();

if(isset($_GET['action'])) {
    switch($_GET['action']) {
        case 'manage':
            $controller->manage();
            break;
        case 'dashboard':
            $controller->dashboard();
            break;
        case 'create':
            $controller->create();
            break;
        case 'edit':
            $controller->edit();
            break;
        case 'delete':
            $controller->delete();
            break;
        default:
            $controller->manage();
    }
} else {
    $controller->manage();
}
?>