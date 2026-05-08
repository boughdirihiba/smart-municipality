<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Budget
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO budgets (titre, annee, categorie, zone, montant_alloue, montant_reserve, description, responsable_id, statut)
             VALUES (:titre, :annee, :categorie, :zone, :montant_alloue, :montant_reserve, :description, :responsable_id, :statut)'
        );

        return $stmt->execute([
            ':titre' => $data['titre'],
            ':annee' => (int)$data['annee'],
            ':categorie' => $data['categorie'],
            ':zone' => $data['zone'] ?? null,
            ':montant_alloue' => (float)$data['montant_alloue'],
            ':montant_reserve' => (float)($data['montant_reserve'] ?? 0),
            ':description' => $data['description'] ?? null,
            ':responsable_id' => (int)($data['responsable_id'] ?? 0) ?: null,
            ':statut' => $data['statut'] ?? 'planifie',
        ]);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT b.*, u.nom AS responsable_nom, u.prenom AS responsable_prenom
             FROM budgets b
             LEFT JOIN utilisateurs u ON u.id = b.responsable_id
             WHERE b.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(?string $annee = null, ?string $categorie = null, ?string $zone = null): array
    {
        $sql = 'SELECT b.*, u.nom AS responsable_nom, u.prenom AS responsable_prenom
                FROM budgets b
                LEFT JOIN utilisateurs u ON u.id = b.responsable_id
                WHERE 1=1';
        $params = [];

        if ($annee !== null && $annee !== '') {
            $sql .= ' AND b.annee = :annee';
            $params[':annee'] = (int)$annee;
        }

        if ($categorie !== null && $categorie !== '') {
            $sql .= ' AND b.categorie = :categorie';
            $params[':categorie'] = $categorie;
        }

        if ($zone !== null && $zone !== '') {
            $sql .= ' AND b.zone = :zone';
            $params[':zone'] = $zone;
        }

        $sql .= ' ORDER BY b.annee DESC, b.categorie ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['titre'])) {
            $fields[] = 'titre = :titre';
            $params[':titre'] = $data['titre'];
        }
        if (isset($data['montant_alloue'])) {
            $fields[] = 'montant_alloue = :montant_alloue';
            $params[':montant_alloue'] = (float)$data['montant_alloue'];
        }
        if (isset($data['montant_reserve'])) {
            $fields[] = 'montant_reserve = :montant_reserve';
            $params[':montant_reserve'] = (float)$data['montant_reserve'];
        }
        if (isset($data['statut'])) {
            $fields[] = 'statut = :statut';
            $params[':statut'] = $data['statut'];
        }
        if (isset($data['description'])) {
            $fields[] = 'description = :description';
            $params[':description'] = $data['description'];
        }

        if (empty($fields)) {
            return true;
        }

        $sql = 'UPDATE budgets SET ' . implode(', ', $fields) . ', updated_at = CURRENT_TIMESTAMP WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function getSummaryByCategory(int $annee): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT categorie, 
                    SUM(montant_alloue) AS total_alloue,
                    SUM(montant_depense) AS total_depense,
                    SUM(montant_reserve) AS total_reserve,
                    COUNT(*) AS count
             FROM budgets
             WHERE annee = :annee
             GROUP BY categorie
             ORDER BY categorie ASC'
        );
        $stmt->execute([':annee' => $annee]);
        return $stmt->fetchAll();
    }

    public function getSummaryByZone(int $annee): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT zone, 
                    SUM(montant_alloue) AS total_alloue,
                    SUM(montant_depense) AS total_depense,
                    SUM(montant_reserve) AS total_reserve,
                    COUNT(*) AS count
             FROM budgets
             WHERE annee = :annee AND zone IS NOT NULL
             GROUP BY zone
             ORDER BY zone ASC'
        );
        $stmt->execute([':annee' => $annee]);
        return $stmt->fetchAll();
    }

    public function addTransaction(int $budgetId, ?int $interventionId, float $montant, string $type, ?string $description): bool
    {
        try {
            $this->pdo->beginTransaction();

            // Insert transaction
            $stmt = $this->pdo->prepare(
                'INSERT INTO budget_transactions (budget_id, intervention_id, montant, type, description)
                 VALUES (:budget_id, :intervention_id, :montant, :type, :description)'
            );
            $stmt->execute([
                ':budget_id' => $budgetId,
                ':intervention_id' => $interventionId,
                ':montant' => $montant,
                ':type' => $type,
                ':description' => $description,
            ]);

            // Update budget montant_depense
            if ($type === 'debit') {
                $updateStmt = $this->pdo->prepare(
                    'UPDATE budgets SET montant_depense = montant_depense + :montant WHERE id = :id'
                );
            } else {
                $updateStmt = $this->pdo->prepare(
                    'UPDATE budgets SET montant_depense = montant_depense - :montant WHERE id = :id'
                );
            }

            $updateStmt->execute([
                ':montant' => $montant,
                ':id' => $budgetId,
            ]);

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    public function syncInterventionExpense(int $interventionId, float $montant, string $description = ''): bool
    {
        $budget = $this->findBudgetForIntervention($interventionId);

        if (!$budget) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $deleteStmt = $this->pdo->prepare(
                'DELETE FROM budget_transactions
                 WHERE budget_id = :budget_id AND intervention_id = :intervention_id AND type = :type'
            );
            $deleteStmt->execute([
                ':budget_id' => (int)$budget['id'],
                ':intervention_id' => $interventionId,
                ':type' => 'debit',
            ]);

            $insertStmt = $this->pdo->prepare(
                'INSERT INTO budget_transactions (budget_id, intervention_id, montant, type, description)
                 VALUES (:budget_id, :intervention_id, :montant, :type, :description)'
            );
            $insertStmt->execute([
                ':budget_id' => (int)$budget['id'],
                ':intervention_id' => $interventionId,
                ':montant' => $montant,
                ':type' => 'debit',
                ':description' => $description,
            ]);

            $updateStmt = $this->pdo->prepare(
                'UPDATE budgets SET montant_depense = montant_depense + :montant WHERE id = :id'
            );
            $updateStmt->execute([
                ':montant' => $montant,
                ':id' => (int)$budget['id'],
            ]);

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    public function findBudgetForIntervention(int $interventionId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT b.*
             FROM interventions i
             LEFT JOIN signalements s ON s.id = i.signalement_id
             LEFT JOIN localisations l ON l.id = s.localisation_id
             JOIN budgets b ON b.categorie = i.type
             WHERE i.id = :intervention_id
               AND b.annee = YEAR(CURDATE())
             ORDER BY CASE
                 WHEN b.zone IS NOT NULL AND b.zone <> "" AND l.quartier = b.zone THEN 0
                 WHEN b.zone IS NULL OR b.zone = "" THEN 1
                 ELSE 2
             END, b.updated_at DESC
             LIMIT 1'
        );
        $stmt->execute([':intervention_id' => $interventionId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function getTransactions(int $budgetId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT bt.*, i.titre AS intervention_titre
             FROM budget_transactions bt
             LEFT JOIN interventions i ON i.id = bt.intervention_id
             WHERE bt.budget_id = :budget_id
             ORDER BY bt.created_at DESC'
        );
        $stmt->execute([':budget_id' => $budgetId]);
        return $stmt->fetchAll();
    }

    public function getCategories(): array
    {
        return ['route', 'eclairage', 'eau', 'transport', 'ordures', 'autre'];
    }

    public function getZones(): array
    {
        $stmt = $this->pdo->prepare('SELECT DISTINCT quartier FROM localisations ORDER BY quartier ASC');
        $stmt->execute();
        return array_map(function($row) { return $row['quartier']; }, $stmt->fetchAll());
    }
}
