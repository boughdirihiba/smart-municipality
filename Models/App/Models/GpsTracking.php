<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class GpsTracking
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Enregistrer la position GPS d'une équipe
     */
    public function logPosition(int $equipeId, ?int $interventionId, float $latitude, float $longitude, ?float $precision = null, ?float $vitesse = null): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO gps_tracking (equipe_id, intervention_id, latitude, longitude, precision, vitesse)
                 VALUES (:equipe_id, :intervention_id, :latitude, :longitude, :precision, :vitesse)'
            );

            return $stmt->execute([
                ':equipe_id' => $equipeId,
                ':intervention_id' => $interventionId,
                ':latitude' => $latitude,
                ':longitude' => $longitude,
                ':precision' => $precision,
                ':vitesse' => $vitesse,
            ]);
        } catch (\Throwable $e) {
            error_log('GPS tracking error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir la dernière position d'une équipe
     */
    public function getLastPosition(int $equipeId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM gps_tracking WHERE equipe_id = :equipe_id ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute([':equipe_id' => $equipeId]);
        $result = $stmt->fetch();
        return is_array($result) ? $result : null;
    }

    /**
     * Obtenir l'historique de position d'une équipe sur une intervention
     */
    public function getTrajectory(int $interventionId, int $equipeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT latitude, longitude, created_at, precision, vitesse 
             FROM gps_tracking 
             WHERE intervention_id = :intervention_id AND equipe_id = :equipe_id
             ORDER BY created_at ASC'
        );
        $stmt->execute([
            ':intervention_id' => $interventionId,
            ':equipe_id' => $equipeId,
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Obtenir les positions actuelles de toutes les équipes
     */
    public function getCurrentPositions(): array
    {
        $stmt = $this->pdo->query(
            'SELECT DISTINCT e.id, e.nom, e.statut, g.latitude, g.longitude, g.created_at, g.vitesse, g.intervention_id
             FROM equipes e
             LEFT JOIN gps_tracking g ON e.id = g.equipe_id
             WHERE g.created_at = (SELECT MAX(created_at) FROM gps_tracking WHERE equipe_id = e.id)
             OR g.created_at IS NULL'
        );
        return $stmt->fetchAll();
    }

    /**
     * Distance parcourue par une équipe pour une intervention (approximation)
     */
    public function getDistanceTraveled(int $interventionId, int $equipeId): float
    {
        $positions = $this->getTrajectory($interventionId, $equipeId);
        
        if (count($positions) < 2) {
            return 0.0;
        }

        $totalDistance = 0.0;

        for ($i = 0; $i < count($positions) - 1; $i++) {
            $lat1 = floatval($positions[$i]['latitude']);
            $lon1 = floatval($positions[$i]['longitude']);
            $lat2 = floatval($positions[$i + 1]['latitude']);
            $lon2 = floatval($positions[$i + 1]['longitude']);

            $distance = $this->haversineDistance($lat1, $lon1, $lat2, $lon2);
            $totalDistance += $distance;
        }

        return $totalDistance;
    }

    /**
     * Formule haversine pour calculer distance entre 2 points GPS
     */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * asin(sqrt($a));

        return $earthRadius * $c;
    }
}
