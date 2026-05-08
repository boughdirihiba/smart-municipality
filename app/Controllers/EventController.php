<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Evenement;
use App\Models\CategorieEvenement;

class EventController extends Controller
{
    private Evenement $evenement;
    private CategorieEvenement $categorie;

    public function __construct()
    {
        parent::__construct();
        $this->evenement = new Evenement();
        $this->categorie = new CategorieEvenement();
    }

    /**
     * List all events
     */
    public function index()
    {
        $events = $this->evenement->all();
        $this->render('evenement/liste', [
            'events' => $events,
            'pageTitle' => 'Tous les événements'
        ]);
    }

    /**
     * Display upcoming events
     */
    public function upcoming()
    {
        $events = $this->evenement->upcoming();
        $this->render('evenement/upcoming', [
            'events' => $events,
            'pageTitle' => 'Événements à venir'
        ]);
    }

    /**
     * Display event details
     */
    public function detail()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(404);
            echo 'Event not found.';
            return;
        }

        $event = $this->evenement->find($id);
        if (!$event) {
            http_response_code(404);
            echo 'Event not found.';
            return;
        }

        $this->render('evenement/detail', [
            'event' => $event,
            'pageTitle' => $event['titre'] ?? 'Event'
        ]);
    }

    /**
     * Create event form
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre' => $_POST['titre'] ?? null,
                'description' => $_POST['description'] ?? null,
                'lieu' => $_POST['lieu'] ?? null,
                'date_evenement' => $_POST['date_evenement'] ?? null,
                'heure' => $_POST['heure'] ?? null,
                'max_participants' => $_POST['max_participants'] ?? null,
                'categorie_id' => $_POST['categorie_id'] ?? null
            ];

            if ($this->evenement->create($data)) {
                $_SESSION['success'] = 'Événement créé avec succès';
                header('Location: ' . $GLOBALS['baseUrl'] . '?route=event/index');
                exit;
            } else {
                $_SESSION['error'] = 'Erreur lors de la création';
            }
        }

        $categories = $this->categorie->all();
        $this->render('evenement/create', [
            'categories' => $categories,
            'pageTitle' => 'Créer un événement'
        ]);
    }

    /**
     * Edit event
     */
    public function edit()
    {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$id) {
            http_response_code(404);
            echo 'Event not found.';
            return;
        }

        $event = $this->evenement->find($id);
        if (!$event) {
            http_response_code(404);
            echo 'Event not found.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre' => $_POST['titre'] ?? $event['titre'],
                'description' => $_POST['description'] ?? $event['description'],
                'lieu' => $_POST['lieu'] ?? $event['lieu'],
                'date_evenement' => $_POST['date_evenement'] ?? $event['date_evenement'],
                'heure' => $_POST['heure'] ?? $event['heure'],
                'categorie_id' => $_POST['categorie_id'] ?? $event['categorie_id']
            ];

            if ($this->evenement->update($id, $data)) {
                $_SESSION['success'] = 'Événement mis à jour avec succès';
                header('Location: ' . $GLOBALS['baseUrl'] . '?route=event/detail&id=' . $id);
                exit;
            } else {
                $_SESSION['error'] = 'Erreur lors de la mise à jour';
            }
        }

        $categories = $this->categorie->all();
        $this->render('evenement/edit', [
            'event' => $event,
            'categories' => $categories,
            'pageTitle' => 'Éditer: ' . $event['titre']
        ]);
    }

    /**
     * Delete event
     */
    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo 'Invalid ID.';
            return;
        }

        if ($this->evenement->delete($id)) {
            $_SESSION['success'] = 'Événement supprimé avec succès';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression';
        }

        header('Location: ' . $GLOBALS['baseUrl'] . '?route=event/index');
        exit;
    }

    /**
     * List categories
     */
    public function categories()
    {
        $categories = $this->categorie->all();
        $this->render('evenement/categories', [
            'categories' => $categories,
            'pageTitle' => 'Catégories d\'événements'
        ]);
    }

    /**
     * Create category
     */
    public function createCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => $_POST['nom'] ?? null,
                'description' => $_POST['description'] ?? null,
                'image_url' => $_POST['image_url'] ?? null
            ];

            if ($this->categorie->create($data)) {
                $_SESSION['success'] = 'Catégorie créée avec succès';
                header('Location: ' . $GLOBALS['baseUrl'] . '?route=event/categories');
                exit;
            } else {
                $_SESSION['error'] = 'Erreur lors de la création';
            }
        }

        $this->render('evenement/createCategory', [
            'pageTitle' => 'Créer une catégorie'
        ]);
    }

    /**
     * Edit category
     */
    public function editCategory()
    {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$id) {
            http_response_code(404);
            echo 'Category not found.';
            return;
        }

        $category = $this->categorie->find($id);
        if (!$category) {
            http_response_code(404);
            echo 'Category not found.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => $_POST['nom'] ?? $category['nom'],
                'description' => $_POST['description'] ?? $category['description'],
                'image_url' => $_POST['image_url'] ?? $category['image_url']
            ];

            if ($this->categorie->update($id, $data)) {
                $_SESSION['success'] = 'Catégorie mise à jour avec succès';
                header('Location: ' . $GLOBALS['baseUrl'] . '?route=event/categories');
                exit;
            } else {
                $_SESSION['error'] = 'Erreur lors de la mise à jour';
            }
        }

        $this->render('evenement/editCategory', [
            'category' => $category,
            'pageTitle' => 'Éditer: ' . $category['nom']
        ]);
    }

    /**
     * Delete category
     */
    public function deleteCategory()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo 'Invalid ID.';
            return;
        }

        $result = $this->categorie->delete($id);
        
        if ($result['success'] ?? false) {
            $_SESSION['success'] = 'Catégorie supprimée avec succès';
        } else {
            $_SESSION['error'] = $result['message'] ?? 'Erreur lors de la suppression';
        }

        header('Location: ' . $GLOBALS['baseUrl'] . '?route=event/categories');
        exit;
    }
}
?>
