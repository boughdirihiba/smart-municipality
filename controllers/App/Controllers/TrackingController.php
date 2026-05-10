<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Budget;
use App\Models\Equipe;
use App\Models\GpsTracking;
use App\Models\TimeTracking;
use App\Models\CoutIntervention;

class TrackingController extends Controller
{
    private Equipe $equipeModel;
    private GpsTracking $gpsModel;
    private TimeTracking $timeModel;
    private CoutIntervention $coutModel;
    private Budget $budgetModel;

    public function __construct()
    {
        $this->equipeModel = new Equipe();
        $this->gpsModel = new GpsTracking();
        $this->timeModel = new TimeTracking();
        $this->coutModel = new CoutIntervention();
        $this->budgetModel = new Budget();
    }

    private function ensureAdmin(): void
    {
        if (($_SESSION['user']['role'] ?? 'citoyen') !== 'admin') {
            set_flash('error', 'Accès réservé aux administrateurs.');
            redirect('home/index');
        }
    }

    /**
     * Page de gestion des équipes
     */
    public function teams(): void
    {
        $this->ensureAdmin();

        $teams = $this->equipeModel->all();
        foreach ($teams as &$team) {
            $team['agents'] = $this->equipeModel->getAgents($team['id']);
            $lastPos = $this->gpsModel->getLastPosition($team['id']);
            $team['derniere_position'] = $lastPos;
        }

        $this->render('backoffice/teams/list', [
            'title' => 'Gestion des équipes',
            'teams' => $teams,
        ]);
    }

    /**
     * Créer une équipe
     */
    public function createTeam(): void
    {
        $this->ensureAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => trim((string)($_POST['nom'] ?? '')),
                'description' => trim((string)($_POST['description'] ?? '')),
                'type_intervention' => trim((string)($_POST['type_intervention'] ?? 'autre')),
                'nombre_agents' => (int)($_POST['nombre_agents'] ?? 1),
            ];

            if (empty($data['nom'])) {
                set_flash('error', 'Le nom de l\'équipe est requis.');
                redirect('tracking/teams');
            }

            $teamId = $this->equipeModel->create($data);
            set_flash('success', 'Équipe créée avec succès.');
            redirect('tracking/editTeam&id=' . $teamId);
        }

        $this->render('backoffice/teams/create', [
            'title' => 'Créer une équipe',
        ]);
    }

    /**
     * Enregistrer la position GPS d'une équipe
     */
    public function logGps(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'POST only']);
            return;
        }

        $input = json_decode((string)file_get_contents('php://input'), true);

        if (!is_array($input)) {
            echo json_encode(['error' => 'Invalid input']);
            return;
        }

        $equipeId = (int)($input['equipe_id'] ?? 0);
        $interventionId = isset($input['intervention_id']) ? (int)$input['intervention_id'] : null;
        $latitude = (float)($input['latitude'] ?? 0);
        $longitude = (float)($input['longitude'] ?? 0);
        $precision = isset($input['precision']) ? (float)$input['precision'] : null;
        $vitesse = isset($input['vitesse']) ? (float)$input['vitesse'] : null;

        if ($equipeId <= 0 || !is_numeric($latitude) || !is_numeric($longitude)) {
            echo json_encode(['error' => 'Invalid coordinates']);
            return;
        }

        $ok = $this->gpsModel->logPosition($equipeId, $interventionId, $latitude, $longitude, $precision, $vitesse);

        echo json_encode([
            'ok' => $ok,
            'message' => $ok ? 'Position enregistrée' : 'Erreur lors de l\'enregistrement',
        ]);
    }

    /**
     * Démarrer le time tracking
     */
    public function startTracking(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'POST only']);
            return;
        }

        $interventionId = (int)($_POST['intervention_id'] ?? 0);
        $equipeId = (int)($_POST['equipe_id'] ?? 0);
        $notes = trim((string)($_POST['notes'] ?? ''));

        if ($interventionId <= 0 || $equipeId <= 0) {
            echo json_encode(['error' => 'Invalid IDs']);
            return;
        }

        $trackingId = $this->timeModel->start($interventionId, $equipeId, $notes ?: null);

        echo json_encode([
            'ok' => $trackingId > 0,
            'tracking_id' => $trackingId,
        ]);
    }

    /**
     * Arrêter le time tracking
     */
    public function stopTracking(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'POST only']);
            return;
        }

        $trackingId = (int)($_POST['tracking_id'] ?? 0);
        $notes = trim((string)($_POST['notes'] ?? ''));

        if ($trackingId <= 0) {
            echo json_encode(['error' => 'Invalid tracking ID']);
            return;
        }

        $ok = $this->timeModel->end($trackingId, $notes ?: null);

        echo json_encode([
            'ok' => $ok,
            'message' => $ok ? 'Time tracking terminé' : 'Erreur',
        ]);
    }

    /**
     * Estimer le coût d'une intervention
     */
    public function estimateCost(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $interventionId = (int)($_GET['intervention_id'] ?? 0);

        if ($interventionId <= 0) {
            echo json_encode(['error' => 'Invalid intervention ID']);
            return;
        }

        $estimation = $this->coutModel->estimate($interventionId);

        if (isset($estimation['error'])) {
            echo json_encode($estimation);
            return;
        }

        // Sauvegarder l'estimation
        $estimation['type'] = $_GET['type'] ?? 'autre';
        $this->coutModel->save($interventionId, $estimation);

        $linkedBudget = $this->budgetModel->findBudgetForIntervention($interventionId);
        $budgetLinked = false;
        if ($linkedBudget) {
            $budgetLinked = $this->budgetModel->syncInterventionExpense(
                $interventionId,
                (float)($estimation['cout_total'] ?? 0),
                'Coût IA intervention #' . $interventionId
            );
        }

        echo json_encode([
            'ok' => true,
            'estimation' => $estimation,
            'budget_linked' => $budgetLinked,
            'budget_id' => $linkedBudget['id'] ?? null,
        ]);
    }

    /**
     * Tableau de bord tracking (carte temps réel)
     */
    public function dashboard(): void
    {
        $this->ensureAdmin();

        $positions = $this->gpsModel->getCurrentPositions();

        $this->render('backoffice/tracking/dashboard', [
            'title' => 'Tableau de bord tracking',
            'positions' => $positions,
        ]);
    }
}
