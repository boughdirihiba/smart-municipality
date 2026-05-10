<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class CoutIntervention
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Estimer le coût d'une intervention via modèle ML
     */
    public function estimate(int $interventionId): array
    {
        $intervention = $this->getIntervention($interventionId);
        if (!$intervention) {
            return ['error' => 'Intervention not found'];
        }

        // Récupérer l'historique similaire
        $historique = $this->getSimilarInterventions($intervention['type'], $intervention['quartier'] ?? 'unknown', 5);

        // Calculer les coûts basiques
        $coutBase = $this->calculateBaseCost($intervention['type']);
        $coutMateriel = $this->estimateMaterialCost($intervention['type'], $intervention['description']);
        $coutMainOeuvre = $this->estimateLaborCost($intervention['type'], $historique);
        $coutDeplacement = $this->estimateTransportCost(
            (float)($intervention['latitude'] ?? 0),
            (float)($intervention['longitude'] ?? 0)
        );

        // Appliquer les facteurs d'ajustement
        $facteurs = $this->getAdjustmentFactors($intervention);
        $multiplicateur = 1.0;
        foreach ($facteurs as $key => $value) {
            $multiplicateur *= $value;
        }

        $coutTotal = ($coutBase + $coutMateriel + $coutMainOeuvre + $coutDeplacement) * $multiplicateur;

        return [
            'cout_base' => $coutBase,
            'cout_materiel' => $coutMateriel,
            'cout_main_oeuvre' => $coutMainOeuvre,
            'cout_deplacement' => $coutDeplacement,
            'cout_total' => $coutTotal,
            'facteurs_ajustement' => $facteurs,
            'historique_similar' => $historique,
        ];
    }

    /**
     * Enregistrer l'estimation
     */
    public function save(int $interventionId, array $estimation): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO couts_intervention 
                 (intervention_id, type_intervention, cout_base, cout_materiel, cout_main_oeuvre, cout_deplacement, cout_total, facteurs_ajustement, historique_similar)
                 VALUES (:intervention_id, :type_intervention, :cout_base, :cout_materiel, :cout_main_oeuvre, :cout_deplacement, :cout_total, :facteurs_ajustement, :historique_similar)
                 ON DUPLICATE KEY UPDATE 
                 cout_base = VALUES(cout_base),
                 cout_materiel = VALUES(cout_materiel),
                 cout_main_oeuvre = VALUES(cout_main_oeuvre),
                 cout_deplacement = VALUES(cout_deplacement),
                 cout_total = VALUES(cout_total),
                 facteurs_ajustement = VALUES(facteurs_ajustement),
                 historique_similar = VALUES(historique_similar),
                 updated_at = NOW()'
            );

            return $stmt->execute([
                ':intervention_id' => $interventionId,
                ':type_intervention' => $estimation['type'] ?? 'autre',
                ':cout_base' => $estimation['cout_base'] ?? 0,
                ':cout_materiel' => $estimation['cout_materiel'] ?? 0,
                ':cout_main_oeuvre' => $estimation['cout_main_oeuvre'] ?? 0,
                ':cout_deplacement' => $estimation['cout_deplacement'] ?? 0,
                ':cout_total' => $estimation['cout_total'] ?? 0,
                ':facteurs_ajustement' => json_encode($estimation['facteurs_ajustement'] ?? []),
                ':historique_similar' => json_encode($estimation['historique_similar'] ?? []),
            ]);
        } catch (\Throwable $e) {
            error_log('Cost estimation save error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Coût de base selon le type d'intervention
     */
    private function calculateBaseCost(string $type): float
    {
        $costs = [
            'route' => 500.0,
            'eclairage' => 300.0,
            'eau' => 400.0,
            'transport' => 250.0,
            'ordures' => 200.0,
            'autre' => 150.0,
        ];
        return $costs[$type] ?? 150.0;
    }

    /**
     * Estimation du coût matériel basée sur la description
     */
    private function estimateMaterialCost(string $type, string $description): float
    {
        $cost = 0.0;

        if (preg_match('/(remplacer|nouveau|neuf)/ui', $description)) {
            $cost += 200.0;
        }

        if (preg_match('/(urgent|critique|danger)/ui', $description)) {
            $cost += 150.0;
        }

        $keywords = [
            'route' => ['bitume', 'asphalte', 'beton'],
            'eclairage' => ['lampadaire', 'ampoule', 'transformateur'],
            'eau' => ['tuyau', 'canalisation', 'valve'],
            'transport' => ['bus', 'route', 'signal'],
            'ordures' => ['bac', 'camion', 'conteneur'],
        ];

        foreach ($keywords[$type] ?? [] as $keyword) {
            if (preg_match('/' . preg_quote($keyword, '/') . '/ui', $description)) {
                $cost += 100.0;
            }
        }

        return $cost;
    }

    /**
     * Estimation main d'oeuvre basée sur l'historique
     */
    private function estimateLaborCost(string $type, array $historique): float
    {
        if (empty($historique)) {
            $costPerHour = 50.0;
            $estimatedHours = match ($type) {
                'route' => 4,
                'eclairage' => 2,
                'eau' => 3,
                'transport' => 2,
                'ordures' => 1,
                default => 1,
            };
            return $costPerHour * $estimatedHours;
        }

        $totalCost = 0.0;
        foreach ($historique as $item) {
            $totalCost += floatval($item['cout_main_oeuvre'] ?? 0);
        }

        return $totalCost / count($historique);
    }

    /**
     * Coût de déplacement en fonction de la zone
     */
    private function estimateTransportCost(float $latitude, float $longitude): float
    {
        // Tunis centre : lat 36.8065, lon 10.1615
        $tunisCentrale = ['lat' => 36.8065, 'lon' => 10.1615];

        $distance = $this->haversineDistance(
            $tunisCentrale['lat'],
            $tunisCentrale['lon'],
            $latitude,
            $longitude
        );

        // 5 TND par km
        return $distance * 5.0;
    }

    /**
     * Facteurs d'ajustement
     */
    private function getAdjustmentFactors(array $intervention): array
    {
        $factors = [
            'urgence' => 1.0,
            'complexite' => 1.0,
            'saison' => 1.0,
        ];

        if (preg_match('/(urgent|critique|danger)/ui', $intervention['description'] ?? '')) {
            $factors['urgence'] = 1.5;
        }

        $descLength = strlen($intervention['description'] ?? '');
        if ($descLength > 200) {
            $factors['complexite'] = 1.3;
        } elseif ($descLength > 100) {
            $factors['complexite'] = 1.1;
        }

        $month = (int)date('m');
        if ($month >= 6 && $month <= 9) {
            $factors['saison'] = 0.9; // Réduction estivale possible
        } else {
            $factors['saison'] = 1.1; // Surcharge hivernal/printemps
        }

        return $factors;
    }

    /**
     * Interventions similaires de l'historique
     */
    private function getSimilarInterventions(string $type, string $zone, int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.* FROM couts_intervention c
             LEFT JOIN interventions i ON i.id = c.intervention_id
             WHERE c.type_intervention = :type
             AND c.cout_total > 0
             ORDER BY c.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':type', $type, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Distance haversine
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

    private function getIntervention(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM interventions WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return is_array($result) ? $result : null;
    }

    public function get(int $interventionId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM couts_intervention WHERE intervention_id = :id');
        $stmt->execute([':id' => $interventionId]);
        $result = $stmt->fetch();
        return is_array($result) ? $result : null;
    }
}
