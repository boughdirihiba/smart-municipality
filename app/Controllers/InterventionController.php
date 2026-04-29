<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Intervention;

class InterventionController extends Controller
{
    private Intervention $model;

    public function __construct()
    {
        $this->model = new Intervention();
    }

    private function ensureAdmin(): void
    {
        if (($_SESSION['user']['role'] ?? 'citoyen') !== 'admin') {
            set_flash('error', 'Acces reserve aux administrateurs.');
            redirect('home/index');
        }
    }

    public function index(): void
    {
        $this->ensureAdmin();

        $type = (string)($_GET['type'] ?? '');
        $statut = (string)($_GET['statut'] ?? '');
        $items = $this->model->all($type, $statut);

        $this->render('backoffice/interventions/list', [
            'title' => 'BackOffice - Interventions',
            'items' => $items,
            'type' => $type,
            'statut' => $statut,
        ]);
    }

    public function create(): void
    {
        $this->ensureAdmin();

        $this->render('backoffice/interventions/edit', [
            'title' => 'Nouvelle intervention',
            'item' => [
                'id' => 0,
                'titre' => '',
                'description' => '',
                'type' => 'autre',
                'latitude' => '',
                'longitude' => '',
                'statut' => 'planifiee',
                'progression' => '0',
                'date_intervention' => '',
            ],
            'errors' => [],
            'isEdit' => false,
        ]);
    }

    public function store(): void
    {
        $this->ensureAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed';
            return;
        }

        $data = $this->collectInput();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('backoffice/interventions/edit', [
                'title' => 'Nouvelle intervention',
                'item' => array_merge(['id' => 0], $data),
                'errors' => $errors,
                'isEdit' => false,
            ]);
            return;
        }

        $ok = $this->model->create([
            'titre' => $data['titre'],
            'description' => $data['description'],
            'type' => $data['type'],
            'latitude' => (float)$data['latitude'],
            'longitude' => (float)$data['longitude'],
            'statut' => $data['statut'],
            'progression' => (int)$data['progression'],
            'date_intervention' => $data['date_intervention'],
        ]);

        set_flash($ok ? 'success' : 'error', $ok ? 'Intervention creee.' : 'Erreur lors de la creation.');
        redirect('interventions/list');
    }

    public function edit(): void
    {
        $this->ensureAdmin();

        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            set_flash('error', 'Intervention invalide.');
            redirect('interventions/list');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->collectInput();
            $errors = $this->validate($data);

            if (!empty($errors)) {
                $this->render('backoffice/interventions/edit', [
                    'title' => 'Modifier intervention',
                    'item' => array_merge(['id' => $id], $data),
                    'errors' => $errors,
                    'isEdit' => true,
                ]);
                return;
            }

            $ok = $this->model->update($id, [
                'titre' => $data['titre'],
                'description' => $data['description'],
                'type' => $data['type'],
                'latitude' => (float)$data['latitude'],
                'longitude' => (float)$data['longitude'],
                'statut' => $data['statut'],
                'progression' => (int)$data['progression'],
                'date_intervention' => $data['date_intervention'],
            ]);

            set_flash($ok ? 'success' : 'error', $ok ? 'Intervention mise a jour.' : 'Erreur de mise a jour.');
            redirect('interventions/list');
        }

        $item = $this->model->find($id);
        if (!$item) {
            set_flash('error', 'Intervention introuvable.');
            redirect('interventions/list');
        }

        $this->render('backoffice/interventions/edit', [
            'title' => 'Modifier intervention',
            'item' => $item,
            'errors' => [],
            'isEdit' => true,
        ]);
    }

    public function delete(): void
    {
        $this->ensureAdmin();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            set_flash('error', 'Intervention invalide.');
            redirect('interventions/list');
        }

        $ok = $this->model->delete($id);
        set_flash($ok ? 'success' : 'error', $ok ? 'Intervention supprimee.' : 'Suppression impossible.');
        redirect('interventions/list');
    }

    private function collectInput(): array
    {
        return [
            'titre' => trim((string)($_POST['titre'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'type' => trim((string)($_POST['type'] ?? '')),
            'latitude' => trim((string)($_POST['latitude'] ?? '')),
            'longitude' => trim((string)($_POST['longitude'] ?? '')),
            'statut' => trim((string)($_POST['statut'] ?? '')),
            'progression' => trim((string)($_POST['progression'] ?? '0')),
            'date_intervention' => trim((string)($_POST['date_intervention'] ?? '')),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['titre'] === '' || mb_strlen($data['titre']) < 5) {
            $errors[] = 'Le titre est obligatoire (minimum 5 caracteres).';
        }

        if ($data['description'] === '' || mb_strlen($data['description']) < 10) {
            $errors[] = 'La description est obligatoire (minimum 10 caracteres).';
        }

        $allowedTypes = ['route', 'eclairage', 'eau', 'transport', 'ordures', 'autre'];
        if (!in_array($data['type'], $allowedTypes, true)) {
            $errors[] = 'Le type est invalide.';
        }

        $allowedStatus = ['planifiee', 'en_cours', 'terminee', 'annulee'];
        if (!in_array($data['statut'], $allowedStatus, true)) {
            $errors[] = 'Le statut est invalide.';
        }

        if (!is_numeric($data['progression'])) {
            $errors[] = 'La progression doit etre un nombre entre 0 et 100.';
        } else {
            $progression = (int)$data['progression'];
            if ($progression < 0 || $progression > 100) {
                $errors[] = 'La progression doit etre comprise entre 0 et 100.';
            }

            if ($data['statut'] === 'terminee' && $progression < 100) {
                $errors[] = 'Une intervention terminee doit etre a 100%.';
            }
        }

        if (!is_numeric($data['latitude']) || (float)$data['latitude'] < -90 || (float)$data['latitude'] > 90) {
            $errors[] = 'Latitude invalide.';
        }

        if (!is_numeric($data['longitude']) || (float)$data['longitude'] < -180 || (float)$data['longitude'] > 180) {
            $errors[] = 'Longitude invalide.';
        }

        if ($data['date_intervention'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_intervention'])) {
            $errors[] = 'Date intervention invalide (YYYY-MM-DD).';
        }

        return $errors;
    }
}
