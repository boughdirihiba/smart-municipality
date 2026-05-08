<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class BudgetForecast
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Generate monthly forecasts for a budget based on historical data
     */
    public function generateForecast(int $budgetId): array
    {
        $budget = $this->getBudget($budgetId);
        if (!$budget) {
            return [];
        }

        $historicalData = $this->getHistoricalCosts($budget['categorie'], $budget['zone']);
        $forecasts = [];

        for ($month = 1; $month <= 12; $month += 1) {
            $estimatedCost = $this->estimateMonthlyCost($budget, $historicalData, $month);
            $forecasts[] = [
                'budget_id' => $budgetId,
                'mois' => $month,
                'depenses_estimees' => $estimatedCost,
                'facteurs' => json_encode($this->getSeasonalFactors($month, $budget['categorie'])),
            ];
        }

        return $forecasts;
    }

    /**
     * Estimate monthly cost using linear regression + seasonal factors
     */
    private function estimateMonthlyCost(array $budget, array $historicalData, int $month): float
    {
        if (empty($historicalData)) {
            // Simple linear distribution if no historical data
            return round($budget['montant_alloue'] / 12, 2);
        }

        // Calculate average cost per month from historical data
        $avgCost = array_sum(array_column($historicalData, 'cost')) / count($historicalData);

        // Apply seasonal factor
        $seasonalFactor = $this->getSeasonalFactor($month, $budget['categorie']);

        // Apply zone/category adjustments
        $adjustment = $this->getCategoryAdjustment($budget['categorie']);

        return round($avgCost * $seasonalFactor * $adjustment, 2);
    }

    /**
     * Get seasonal factors based on category and month
     */
    private function getSeasonalFactor(int $month, string $categorie): float
    {
        $seasonalFactors = [
            'route' => [1.2, 1.1, 1.0, 0.9, 0.8, 0.7, 0.7, 0.8, 0.9, 1.0, 1.1, 1.2],  // More in summer/winter
            'eclairage' => [1.3, 1.2, 1.0, 0.8, 0.7, 0.7, 0.7, 0.7, 0.8, 1.0, 1.2, 1.3], // More in winter
            'eau' => [0.9, 0.9, 0.9, 0.8, 0.7, 0.7, 0.8, 0.8, 0.9, 1.0, 1.1, 1.1],      // Less in summer
            'transport' => [1.0, 1.0, 1.1, 1.1, 1.0, 0.9, 0.9, 1.0, 1.1, 1.1, 1.0, 1.0], // Back to school
            'ordures' => [0.9, 0.9, 1.0, 1.0, 1.1, 1.2, 1.2, 1.1, 1.0, 1.0, 0.9, 0.9],  // More in summer
            'autre' => [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0],
        ];

        $factors = $seasonalFactors[$categorie] ?? $seasonalFactors['autre'];
        return $factors[$month - 1] ?? 1.0;
    }

    /**
     * Get all seasonal factors for a category
     */
    private function getSeasonalFactors(int $month, string $categorie): array
    {
        $months = ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'];
        return [
            'month' => $months[$month - 1],
            'factor' => $this->getSeasonalFactor($month, $categorie),
        ];
    }

    /**
     * Get category-specific cost adjustment
     */
    private function getCategoryAdjustment(string $categorie): float
    {
        $adjustments = [
            'route' => 1.15,
            'eclairage' => 1.05,
            'eau' => 1.1,
            'transport' => 0.95,
            'ordures' => 1.0,
            'autre' => 1.0,
        ];

        return $adjustments[$categorie] ?? 1.0;
    }

    /**
     * Get historical cost data for a category and zone
     */
    private function getHistoricalCosts(string $categorie, ?string $zone): array
    {
        $sql = 'SELECT SUM(ci.cout_total) AS cost
                FROM couts_intervention ci
                JOIN interventions i ON i.id = ci.intervention_id
                WHERE i.type = :categorie';
        $params = [':categorie' => $categorie];

        if ($zone !== null && $zone !== '') {
            $sql .= ' AND i.latitude IS NOT NULL AND i.longitude IS NOT NULL';
        }

        $sql .= ' GROUP BY MONTH(ci.created_at)
                 ORDER BY MONTH(ci.created_at) ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get budget by ID
     */
    private function getBudget(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM budgets WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Save forecasts to database
     */
    public function saveForecast(int $budgetId, array $forecasts): bool
    {
        try {
            $this->pdo->beginTransaction();

            // Delete existing forecasts
            $deleteStmt = $this->pdo->prepare('DELETE FROM budget_forecasts WHERE budget_id = :budget_id');
            $deleteStmt->execute([':budget_id' => $budgetId]);

            // Insert new forecasts
            $insertStmt = $this->pdo->prepare(
                'INSERT INTO budget_forecasts (budget_id, mois, depenses_estimees, facteurs)
                 VALUES (:budget_id, :mois, :depenses_estimees, :facteurs)'
            );

            foreach ($forecasts as $forecast) {
                $insertStmt->execute([
                    ':budget_id' => $budgetId,
                    ':mois' => $forecast['mois'],
                    ':depenses_estimees' => $forecast['depenses_estimees'],
                    ':facteurs' => $forecast['facteurs'],
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    /**
     * Get forecasts for a budget
     */
    public function getForecast(int $budgetId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM budget_forecasts WHERE budget_id = :budget_id ORDER BY mois ASC'
        );
        $stmt->execute([':budget_id' => $budgetId]);
        return $stmt->fetchAll();
    }

    /**
     * Update actual expenses for a month
     */
    public function updateActualExpenses(int $budgetId, int $month, float $actualAmount): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE budget_forecasts 
             SET depenses_reelles = :amount,
                 precision_score = CASE WHEN depenses_estimees > 0 
                    THEN ROUND(100 - ABS((depenses_estimees - :amount) / depenses_estimees * 100), 2)
                    ELSE 0 END
             WHERE budget_id = :budget_id AND mois = :mois'
        );

        return $stmt->execute([
            ':amount' => $actualAmount,
            ':budget_id' => $budgetId,
            ':mois' => $month,
        ]);
    }

    /**
     * Get forecast accuracy summary
     */
    public function getForecastAccuracy(int $budgetId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT 
                AVG(precision_score) AS avg_accuracy,
                COUNT(CASE WHEN precision_score > 0 THEN 1 END) AS months_with_actuals,
                MAX(precision_score) AS best_month,
                MIN(precision_score) AS worst_month,
                SUM(depenses_estimees) AS total_estimated,
                SUM(depenses_reelles) AS total_actual
             FROM budget_forecasts
             WHERE budget_id = :budget_id'
        );
        $stmt->execute([':budget_id' => $budgetId]);
        return $stmt->fetch() ?: [];
    }
}
