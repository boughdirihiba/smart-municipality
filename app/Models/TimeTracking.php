<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class TimeTracking
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Démarrer le time tracking d'une intervention
     */
    public function start(int $interventionId, int $equipeId, ?string $notes = null): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO time_tracking (intervention_id, equipe_id, heure_debut, statut, notes)
             VALUES (:intervention_id, :equipe_id, NOW(), :statut, :notes)'
        );

        $stmt->execute([
            ':intervention_id' => $interventionId,
            ':equipe_id' => $equipeId,
            ':statut' => 'en_cours',
            ':notes' => $notes,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Terminer le time tracking
     */
    public function end(int $trackingId, ?string $notes = null): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE time_tracking 
             SET heure_fin = NOW(), 
                 statut = :statut,
                 duree_minutes = TIMESTAMPDIFF(MINUTE, heure_debut, NOW()),
                 notes = COALESCE(:notes, notes),
                 updated_at = NOW()
             WHERE id = :id'
        );

        return $stmt->execute([
            ':id' => $trackingId,
            ':statut' => 'terminé',
            ':notes' => $notes,
        ]);
    }

    /**
     * Pause le time tracking
     */
    public function pause(int $trackingId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE time_tracking SET statut = :statut, updated_at = NOW() WHERE id = :id'
        );
        return $stmt->execute([':id' => $trackingId, ':statut' => 'interrompu']);
    }

    /**
     * Reprendre le time tracking
     */
    public function resume(int $trackingId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE time_tracking SET statut = :statut, updated_at = NOW() WHERE id = :id'
        );
        return $stmt->execute([':id' => $trackingId, ':statut' => 'en_cours']);
    }

    /**
     * Obtenir le tracking actif d'une intervention
     */
    public function getActive(int $interventionId, int $equipeId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM time_tracking 
             WHERE intervention_id = :intervention_id AND equipe_id = :equipe_id AND statut != :statut
             ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute([
            ':intervention_id' => $interventionId,
            ':equipe_id' => $equipeId,
            ':statut' => 'terminé',
        ]);
        $result = $stmt->fetch();
        return is_array($result) ? $result : null;
    }

    /**
     * Obtenir tous les trackings d'une intervention
     */
    public function getByIntervention(int $interventionId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.*, e.nom as equipe_nom 
             FROM time_tracking t
             LEFT JOIN equipes e ON e.id = t.equipe_id
             WHERE t.intervention_id = :intervention_id
             ORDER BY t.heure_debut DESC'
        );
        $stmt->execute([':intervention_id' => $interventionId]);
        return $stmt->fetchAll();
    }

    /**
     * Durée totale d'une intervention
     */
    public function getTotalDuration(int $interventionId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT SUM(duree_minutes) as total FROM time_tracking 
             WHERE intervention_id = :intervention_id AND statut = :statut'
        );
        $stmt->execute([
            ':intervention_id' => $interventionId,
            ':statut' => 'terminé',
        ]);
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Obtenir les statistiques de time tracking par équipe
     */
    public function getTeamStats(int $equipeId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = 'SELECT 
                    COUNT(*) as nombre_interventions,
                    SUM(CASE WHEN statut = :terminé THEN duree_minutes ELSE 0 END) as total_minutes,
                    AVG(CASE WHEN statut = :terminé THEN duree_minutes ELSE NULL END) as moyenne_minutes,
                    MIN(heure_debut) as premiere_intervention,
                    MAX(heure_fin) as derniere_intervention
                  FROM time_tracking 
                  WHERE equipe_id = :equipe_id';

        $params = [
            ':equipe_id' => $equipeId,
            ':terminé' => 'terminé',
        ];

        if ($dateFrom) {
            $query .= ' AND DATE(heure_debut) >= :date_from';
            $params[':date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $query .= ' AND DATE(heure_fin) <= :date_to';
            $params[':date_to'] = $dateTo;
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return is_array($result) ? $result : [];
    }
}
