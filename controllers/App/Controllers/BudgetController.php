<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Budget;
use App\Models\BudgetForecast;

class BudgetController extends Controller
{
    private Budget $budgetModel;
    private BudgetForecast $forecastModel;

    public function __construct()
    {
        $this->budgetModel = new Budget();
        $this->forecastModel = new BudgetForecast();
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

        $annee = (int)($_GET['annee'] ?? date('Y'));
        $categorie = (string)($_GET['categorie'] ?? '');
        $zone = (string)($_GET['zone'] ?? '');

        $budgets = $this->budgetModel->all((string)$annee, $categorie, $zone);
        $summaryByCategory = $this->budgetModel->getSummaryByCategory($annee);
        $summaryByZone = $this->budgetModel->getSummaryByZone($annee);

        // Calculate overall stats
        $stats = [
            'total_alloue' => 0,
            'total_depense' => 0,
            'total_reserve' => 0,
            'taux_utilisation' => 0,
            'taux_reserve' => 0,
        ];

        foreach ($budgets as $budget) {
            $stats['total_alloue'] += (float)$budget['montant_alloue'];
            $stats['total_depense'] += (float)$budget['montant_depense'];
            $stats['total_reserve'] += (float)$budget['montant_reserve'];
        }

        if ($stats['total_alloue'] > 0) {
            $stats['taux_utilisation'] = round(($stats['total_depense'] / $stats['total_alloue']) * 100, 2);
            $stats['taux_reserve'] = round(($stats['total_reserve'] / $stats['total_alloue']) * 100, 2);
        }

        $this->render('backoffice/budgets/list', [
            'title' => 'Gestion des Budgets - ' . $annee,
            'budgets' => $budgets,
            'summaryByCategory' => $summaryByCategory,
            'summaryByZone' => $summaryByZone,
            'stats' => $stats,
            'annee' => $annee,
            'categorie' => $categorie,
            'zone' => $zone,
            'categories' => $this->budgetModel->getCategories(),
            'zones' => $this->budgetModel->getZones(),
        ]);
    }

    public function create(): void
    {
        $this->ensureAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre' => (string)($_POST['titre'] ?? ''),
                'annee' => (int)($_POST['annee'] ?? date('Y')),
                'categorie' => (string)($_POST['categorie'] ?? ''),
                'zone' => (string)($_POST['zone'] ?? ''),
                'montant_alloue' => (float)($_POST['montant_alloue'] ?? 0),
                'montant_reserve' => (float)($_POST['montant_reserve'] ?? 0),
                'description' => (string)($_POST['description'] ?? ''),
                'responsable_id' => (int)($_POST['responsable_id'] ?? 0),
            ];

            if ($this->budgetModel->create($data)) {
                set_flash('success', 'Budget créé avec succès.');
                redirect('budget/index');
            } else {
                set_flash('error', 'Erreur lors de la création du budget.');
            }
        }

        $this->render('backoffice/budgets/create', [
            'title' => 'Créer un Budget',
            'categories' => $this->budgetModel->getCategories(),
            'zones' => $this->budgetModel->getZones(),
        ]);
    }

    public function edit(): void
    {
        $this->ensureAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $budget = $this->budgetModel->find($id);

        if (!$budget) {
            set_flash('error', 'Budget non trouvé.');
            redirect('budget/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [];
            if (isset($_POST['titre'])) {
                $data['titre'] = (string)$_POST['titre'];
            }
            if (isset($_POST['montant_alloue'])) {
                $data['montant_alloue'] = (float)$_POST['montant_alloue'];
            }
            if (isset($_POST['montant_reserve'])) {
                $data['montant_reserve'] = (float)$_POST['montant_reserve'];
            }
            if (isset($_POST['statut'])) {
                $data['statut'] = (string)$_POST['statut'];
            }
            if (isset($_POST['description'])) {
                $data['description'] = (string)$_POST['description'];
            }

            if ($this->budgetModel->update($id, $data)) {
                set_flash('success', 'Budget mis à jour avec succès.');
                redirect('budget/edit&id=' . $id);
            } else {
                set_flash('error', 'Erreur lors de la mise à jour.');
            }
        }

        $transactions = $this->budgetModel->getTransactions($id);

        $this->render('backoffice/budgets/edit', [
            'title' => 'Éditer le Budget: ' . $budget['titre'],
            'budget' => $budget,
            'transactions' => $transactions,
            'categories' => $this->budgetModel->getCategories(),
        ]);
    }

    public function detail(): void
    {
        $this->ensureAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $budget = $this->budgetModel->find($id);

        if (!$budget) {
            set_flash('error', 'Budget non trouvé.');
            redirect('budget/index');
        }

        $transactions = $this->budgetModel->getTransactions($id);
        $forecasts = $this->forecastModel->getForecast($id);
        $accuracy = $this->forecastModel->getForecastAccuracy($id);

        $this->render('backoffice/budgets/detail', [
            'title' => 'Détail du Budget: ' . $budget['titre'],
            'budget' => $budget,
            'transactions' => $transactions,
            'forecasts' => $forecasts,
            'accuracy' => $accuracy,
        ]);
    }

    public function generateForecast(): void
    {
        $this->ensureAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $budget = $this->budgetModel->find($id);

        if (!$budget) {
            set_flash('error', 'Budget non trouvé.');
            redirect('budget/index');
        }

        $forecasts = $this->forecastModel->generateForecast($id);
        if ($this->forecastModel->saveForecast($id, $forecasts)) {
            set_flash('success', 'Prévisions générées avec succès.');
            redirect('budget/detail&id=' . $id);
        } else {
            set_flash('error', 'Erreur lors de la génération des prévisions.');
            redirect('budget/detail&id=' . $id);
        }
    }

    public function addTransaction(): void
    {
        $this->ensureAdmin();

        $id = (int)($_POST['budget_id'] ?? 0);
        $budget = $this->budgetModel->find($id);

        if (!$budget) {
            http_response_code(404);
            echo json_encode(['error' => 'Budget non trouvé']);
            exit;
        }

        $interventionId = !empty($_POST['intervention_id']) ? (int)$_POST['intervention_id'] : null;
        $montant = (float)($_POST['montant'] ?? 0);
        $type = (string)($_POST['type'] ?? 'debit');
        $description = (string)($_POST['description'] ?? '');

        if ($montant <= 0) {
            http_response_code(400);
            if ($this->expectsJson()) {
                echo json_encode(['error' => 'Montant invalide']);
            } else {
                set_flash('error', 'Montant invalide.');
                redirect('budget/detail&id=' . $id);
            }
            exit;
        }

        if ($this->budgetModel->addTransaction($id, $interventionId, $montant, $type, $description)) {
            if ($this->expectsJson()) {
                echo json_encode(['success' => true, 'message' => 'Transaction ajoutée']);
            } else {
                set_flash('success', 'Dépense enregistrée avec succès.');
                redirect('budget/detail&id=' . $id);
            }
        } else {
            http_response_code(500);
            if ($this->expectsJson()) {
                echo json_encode(['error' => 'Erreur lors de l\'ajout']);
            } else {
                set_flash('error', 'Erreur lors de l\'ajout de la dépense.');
                redirect('budget/detail&id=' . $id);
            }
        }

        exit;
    }

    private function expectsJson(): bool
    {
        $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));

        return str_contains($accept, 'application/json')
            || strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    }
}
