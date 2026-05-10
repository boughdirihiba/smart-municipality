<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Budget;
use App\Models\CoutIntervention;
use App\Models\Intervention;

class InterventionController extends Controller
{
    private Intervention $model;
    private CoutIntervention $coutModel;
    private Budget $budgetModel;

    public function __construct()
    {
        $this->model = new Intervention();
        $this->coutModel = new CoutIntervention();
        $this->budgetModel = new Budget();
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

        $type = trim((string)($_GET['type'] ?? 'autre'));
        $fromSignalementId = (int)($_GET['from_signalement'] ?? 0);
        $latitude = (float)($_GET['latitude'] ?? 0.0);
        $longitude = (float)($_GET['longitude'] ?? 0.0);

        $autoTasks = $this->model->generateTasksForType($type);

        // Initialize item data
        $item = [
            'id' => 0,
            'titre' => '',
            'description' => '',
            'type' => $type,
            'latitude' => $latitude ?: '',
            'longitude' => $longitude ?: '',
            'statut' => 'planifiee',
            'progression' => '0',
            'date_intervention' => '',
            'tasks' => $autoTasks,
            'tasks_json' => json_encode($autoTasks, JSON_UNESCAPED_UNICODE),
            'from_signalement' => $fromSignalementId,
        ];

        // Load signalement data if from_signalement is provided
        if ($fromSignalementId > 0) {
            $signalementModel = new \App\Models\Signalement();
            $signalement = $signalementModel->find($fromSignalementId);

            if ($signalement) {
                // Pre-fill from signalement data
                $item['titre'] = 'Intervention: ' . ($signalement['titre'] ?? 'Signalement');
                $item['description'] = $signalement['titre'] ?? '';
                $item['type'] = $signalement['categorie'] ?? $type;
                $item['latitude'] = $signalement['latitude'] ?? $latitude;
                $item['longitude'] = $signalement['longitude'] ?? $longitude;

                // Regenerate tasks for the selected category
                $autoTasks = $this->model->generateTasksForType($item['type']);
                $item['tasks'] = $autoTasks;
                $item['tasks_json'] = json_encode($autoTasks, JSON_UNESCAPED_UNICODE);
            }
        }

        $this->render('backoffice/interventions/edit', [
            'title' => 'Nouvelle intervention',
            'item' => $item,
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
                'item' => array_merge(['id' => 0], $data, [
                    'tasks' => $data['tasks'],
                ]),
                'errors' => $errors,
                'isEdit' => false,
            ]);
            return;
        }

        // Always generate task plan from type + description for new interventions.
        $generatedTasks = $this->model->generateTasksWithAI($data['type'], $data['titre'], $data['description']);
        if (!empty($generatedTasks)) {
            $data['tasks'] = $generatedTasks;
            $data['tasks_json'] = $this->model->encodeTasks($generatedTasks);
        }

        $tasksJson = $this->model->encodeTasks($data['tasks']);
        $progression = $this->model->calculateProgressionFromTasks($data['tasks']);

        $interventionId = $this->model->create([
            'titre' => $data['titre'],
            'description' => $data['description'],
            'type' => $data['type'],
            'tasks_json' => $tasksJson,
            'latitude' => (float)$data['latitude'],
            'longitude' => (float)$data['longitude'],
            'statut' => $data['statut'],
            'progression' => $progression,
            'date_intervention' => $data['date_intervention'],
            'signalement_id' => !empty($data['from_signalement']) ? (int)$data['from_signalement'] : null,
        ]);

        if ($interventionId > 0) {
            $estimation = $this->coutModel->estimate($interventionId);
            if (!isset($estimation['error'])) {
                $estimation['type'] = $data['type'];
                $this->coutModel->save($interventionId, $estimation);

                $linkedBudget = $this->budgetModel->findBudgetForIntervention($interventionId);
                if ($linkedBudget) {
                    $this->budgetModel->syncInterventionExpense(
                        $interventionId,
                        (float)($estimation['cout_total'] ?? 0),
                        'Coût IA intervention #' . $interventionId
                    );
                }

                $coutTotal = isset($estimation['cout_total']) ? number_format((float)$estimation['cout_total'], 2, ',', ' ') . ' TND' : 'N/A';
                $budgetNom = $linkedBudget ? $linkedBudget['titre'] : 'Pas de budget correspondant';
                set_flash('success', 'Intervention creée. Estimation: ' . $coutTotal . ' (Budget lié: ' . $budgetNom . ')');
            } else {
                set_flash('success', 'Intervention creee, mais estimation non disponible pour le moment.');
            }
        } else {
            set_flash('error', 'Erreur lors de la creation.');
        }
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
                    'item' => array_merge(['id' => $id], $data, [
                        'tasks' => $data['tasks'],
                    ]),
                    'errors' => $errors,
                    'isEdit' => true,
                ]);
                return;
            }

            $tasksJson = $this->model->encodeTasks($data['tasks']);
            $progression = $this->model->calculateProgressionFromTasks($data['tasks']);

            $ok = $this->model->update($id, [
                'titre' => $data['titre'],
                'description' => $data['description'],
                'type' => $data['type'],
                'tasks_json' => $tasksJson,
                'latitude' => (float)$data['latitude'],
                'longitude' => (float)$data['longitude'],
                'statut' => $data['statut'],
                'progression' => $progression,
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

        $item['tasks'] = $this->model->normalizeTasksJson((string)($item['tasks_json'] ?? ''));

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

    public function generateTasks(): void
    {
        $this->ensureAdmin();

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
            return;
        }

        $type = trim((string)($_POST['type'] ?? 'autre'));
        $titre = trim((string)($_POST['titre'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));

        if ($description === '' || mb_strlen($description) < 10) {
            echo json_encode(['ok' => false, 'message' => 'Description too short']);
            return;
        }

        $tasks = $this->model->generateTasksWithAI($type, $titre, $description);
        echo json_encode(['ok' => true, 'tasks' => $tasks], JSON_UNESCAPED_UNICODE);
    }

    private function collectInput(): array
    {
        $tasksRaw = trim((string)($_POST['tasks_json'] ?? '[]'));
        $tasks = $this->model->normalizeTasksJson($tasksRaw);

        $type = trim((string)($_POST['type'] ?? ''));
        $titre = trim((string)($_POST['titre'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));

        return [
            'titre' => $titre,
            'description' => $description,
            'type' => $type,
            'latitude' => trim((string)($_POST['latitude'] ?? '')),
            'longitude' => trim((string)($_POST['longitude'] ?? '')),
            'statut' => trim((string)($_POST['statut'] ?? '')),
            'date_intervention' => trim((string)($_POST['date_intervention'] ?? '')),
            'tasks_json' => $tasksRaw,
            'tasks' => $tasks,
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

        if (empty($data['tasks'])) {
            $errors[] = 'Ajoutez au moins une étape pour calculer la progression.';
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
