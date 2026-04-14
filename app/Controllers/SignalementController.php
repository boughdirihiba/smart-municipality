<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Signalement;

class SignalementController extends Controller
{
    private Signalement $model;

    public function __construct()
    {
        $this->model = new Signalement();
    }

    public function create(): void
    {
        $this->render('signalements/create', [
            'title' => 'Créer un signalement',
            'errors' => [],
            'old' => [],
        ]);
    }

    public function store(): void
    {
        $data = [
            'titre' => trim((string)($_POST['titre'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'categorie' => trim((string)($_POST['categorie'] ?? '')),
            'adresse' => trim((string)($_POST['adresse'] ?? '')),
            'quartier' => trim((string)($_POST['quartier'] ?? '')),
            'latitude' => trim((string)($_POST['latitude'] ?? '')),
            'longitude' => trim((string)($_POST['longitude'] ?? '')),
        ];

        $errors = $this->validate($data, $_FILES['image'] ?? null);

        $imageName = null;
        if (empty($errors) && isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
            $imageName = $this->uploadImage($_FILES['image'], $errors);
        }

        if (!empty($errors)) {
            $this->render('signalements/create', [
                'title' => 'Créer un signalement',
                'errors' => $errors,
                'old' => $data,
            ]);
            return;
        }

        $saved = $this->model->create([
            'titre' => $data['titre'],
            'description' => $data['description'],
            'categorie' => $data['categorie'],
            'adresse' => $data['adresse'],
            'quartier' => $data['quartier'],
            'latitude' => (float)$data['latitude'],
            'longitude' => (float)$data['longitude'],
            'image' => $imageName,
            'user_id' => (int)$_SESSION['user']['id'],
        ]);

        if ($saved) {
            add_notification('Nouveau signalement ajouté: ' . $data['titre'], 'created');
            set_flash('success', 'Signalement créé avec succès.');
            redirect('signalements/list');
        }

        set_flash('error', 'Erreur lors de la création du signalement.');
        redirect('signalements/create');
    }

    public function list(): void
    {
        $items = $this->model->allByUser((int)$_SESSION['user']['id']);
        $this->render('signalements/list', [
            'title' => 'Mes signalements',
            'items' => $items,
        ]);
    }

    public function detail(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = $this->model->find($id);

        if (!$item) {
            set_flash('error', 'Signalement introuvable.');
            redirect('signalements/list');
        }

        $history = $this->model->positionHistory($id);

        $this->render('signalements/detail', [
            'title' => 'Détail signalement',
            'item' => $item,
            'history' => $history,
        ]);
    }

    private function validate(array $data, ?array $image): array
    {
        $errors = [];
        $safePattern = '/^[\p{L}\p{N}\s\'.,\-()\/]+$/u';

        if ($data['titre'] === '' || mb_strlen($data['titre']) < 5) {
            $errors[] = 'Le titre est obligatoire (minimum 5 caractères).';
        }
        if (mb_strlen($data['titre']) > 255) {
            $errors[] = 'Le titre ne doit pas dépasser 255 caractères.';
        }
        if ($data['titre'] !== '' && !preg_match($safePattern, $data['titre'])) {
            $errors[] = 'Le titre contient des caractères non autorisés.';
        }

        if ($data['description'] === '' || mb_strlen($data['description']) < 10) {
            $errors[] = 'La description est obligatoire (minimum 10 caractères).';
        }
        if (mb_strlen($data['description']) > 2000) {
            $errors[] = 'La description ne doit pas dépasser 2000 caractères.';
        }

        $allowedCategories = ['route', 'eclairage', 'eau', 'transport', 'ordures', 'autre'];
        if (!in_array($data['categorie'], $allowedCategories, true)) {
            $errors[] = 'La catégorie est invalide.';
        }

        if ($data['adresse'] === '' || mb_strlen($data['adresse']) < 5) {
            $errors[] = 'L\'adresse est obligatoire (minimum 5 caractères).';
        }
        if (mb_strlen($data['adresse']) > 255) {
            $errors[] = 'L\'adresse ne doit pas dépasser 255 caractères.';
        }
        if ($data['adresse'] !== '' && !preg_match($safePattern, $data['adresse'])) {
            $errors[] = 'L\'adresse contient des caractères non autorisés.';
        }

        if ($data['quartier'] !== '') {
            if (mb_strlen($data['quartier']) < 2) {
                $errors[] = 'Le quartier est trop court.';
            }
            if (mb_strlen($data['quartier']) > 120) {
                $errors[] = 'Le quartier ne doit pas dépasser 120 caractères.';
            }
            if (!preg_match($safePattern, $data['quartier'])) {
                $errors[] = 'Le quartier contient des caractères non autorisés.';
            }
        }

        if (!is_numeric($data['latitude']) || (float)$data['latitude'] < -90 || (float)$data['latitude'] > 90) {
            $errors[] = 'Latitude invalide.';
        }

        if (!is_numeric($data['longitude']) || (float)$data['longitude'] < -180 || (float)$data['longitude'] > 180) {
            $errors[] = 'Longitude invalide.';
        }

        if ($image && !empty($image['name'])) {
            $allowedMime = ['image/jpeg', 'image/png'];
            $mime = mime_content_type($image['tmp_name']);
            if (!in_array($mime, $allowedMime, true)) {
                $errors[] = 'Image invalide (formats autorisés: JPG, PNG).';
            }
            if ((int)$image['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Image trop volumineuse (max 5 Mo).';
            }
        }

        return $errors;
    }

    private function uploadImage(array $image, array &$errors): ?string
    {
        $extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        $fileName = uniqid('sig_', true) . '.' . $extension;
        $target = UPLOAD_PATH . $fileName;

        if (!move_uploaded_file($image['tmp_name'], $target)) {
            $errors[] = 'Impossible de téléverser l\'image.';
            return null;
        }

        return $fileName;
    }
}
