<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Rapport
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Créer un rapport
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO rapports (titre, type, periode_debut, periode_fin, status)
             VALUES (:titre, :type, :periode_debut, :periode_fin, :status)'
        );

        $stmt->execute([
            ':titre' => $data['titre'],
            ':type' => $data['type'],
            ':periode_debut' => $data['periode_debut'],
            ':periode_fin' => $data['periode_fin'],
            ':status' => 'en_generation',
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Générer les métriques du rapport
     */
    public function generateMetrics(int $rapportId, string $periodDebut, string $periodFin): array
    {
        $metrics = [];

        // Signalements stats
        $stmt = $this->pdo->prepare(
            'SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN statut = "resolu" THEN 1 ELSE 0 END) as resolus,
                SUM(CASE WHEN statut = "en_cours" THEN 1 ELSE 0 END) as en_cours,
                SUM(CASE WHEN statut = "en_attente" THEN 1 ELSE 0 END) as en_attente,
                SUM(CASE WHEN statut = "rejete" THEN 1 ELSE 0 END) as rejetes
             FROM signalements 
             WHERE DATE(date_signalement) BETWEEN :debut AND :fin'
        );
        $stmt->execute([':debut' => $periodDebut, ':fin' => $periodFin]);
        $sigStats = $stmt->fetch();

        $metrics['signalements'] = [
            'total' => (int)($sigStats['total'] ?? 0),
            'resolus' => (int)($sigStats['resolus'] ?? 0),
            'en_cours' => (int)($sigStats['en_cours'] ?? 0),
            'en_attente' => (int)($sigStats['en_attente'] ?? 0),
            'rejetes' => (int)($sigStats['rejetes'] ?? 0),
            'taux_resolution' => ($sigStats['total'] > 0) ? round(($sigStats['resolus'] / $sigStats['total']) * 100, 2) : 0,
        ];

        // Interventions stats
        $stmt = $this->pdo->prepare(
            'SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN statut = "terminee" THEN 1 ELSE 0 END) as terminees,
                SUM(CASE WHEN statut = "en_cours" THEN 1 ELSE 0 END) as en_cours,
                SUM(CASE WHEN statut = "annulee" THEN 1 ELSE 0 END) as annulees,
                AVG(progression) as progression_moyenne
             FROM interventions 
             WHERE DATE(date_intervention) BETWEEN :debut AND :fin'
        );
        $stmt->execute([':debut' => $periodDebut, ':fin' => $periodFin]);
        $intStats = $stmt->fetch();

        $metrics['interventions'] = [
            'total' => (int)($intStats['total'] ?? 0),
            'terminees' => (int)($intStats['terminees'] ?? 0),
            'en_cours' => (int)($intStats['en_cours'] ?? 0),
            'annulees' => (int)($intStats['annulees'] ?? 0),
            'progression_moyenne' => round((float)($intStats['progression_moyenne'] ?? 0), 2),
        ];

        // Coûts stats
        $stmt = $this->pdo->prepare(
            'SELECT 
                COUNT(*) as total_interventions,
                SUM(cout_total) as cout_total,
                AVG(cout_total) as cout_moyen,
                MIN(cout_total) as cout_min,
                MAX(cout_total) as cout_max
             FROM couts_intervention c
             LEFT JOIN interventions i ON i.id = c.intervention_id
             WHERE DATE(i.date_intervention) BETWEEN :debut AND :fin'
        );
        $stmt->execute([':debut' => $periodDebut, ':fin' => $periodFin]);
        $costStats = $stmt->fetch();

        $metrics['couts'] = [
            'total_interventions' => (int)($costStats['total_interventions'] ?? 0),
            'cout_total' => round((float)($costStats['cout_total'] ?? 0), 2),
            'cout_moyen' => round((float)($costStats['cout_moyen'] ?? 0), 2),
            'cout_min' => round((float)($costStats['cout_min'] ?? 0), 2),
            'cout_max' => round((float)($costStats['cout_max'] ?? 0), 2),
        ];

        // Time tracking stats
        $stmt = $this->pdo->prepare(
            'SELECT 
                COUNT(*) as total_sessions,
                SUM(duree_minutes) as total_minutes,
                AVG(duree_minutes) as moyenne_minutes
             FROM time_tracking 
             WHERE DATE(heure_debut) BETWEEN :debut AND :fin AND statut = "terminé"'
        );
        $stmt->execute([':debut' => $periodDebut, ':fin' => $periodFin]);
        $timeStats = $stmt->fetch();

        $metrics['time_tracking'] = [
            'total_sessions' => (int)($timeStats['total_sessions'] ?? 0),
            'total_heures' => round(((float)($timeStats['total_minutes'] ?? 0) / 60), 2),
            'moyenne_minutes' => round((float)($timeStats['moyenne_minutes'] ?? 0), 2),
        ];

        // Catégories breakdown
        $stmt = $this->pdo->prepare(
            'SELECT categorie, COUNT(*) as count FROM signalements 
             WHERE DATE(date_signalement) BETWEEN :debut AND :fin
             GROUP BY categorie'
        );
        $stmt->execute([':debut' => $periodDebut, ':fin' => $periodFin]);
        $metrics['par_categorie'] = $stmt->fetchAll();

        return $metrics;
    }

    /**
     * Mettre à jour le rapport avec contenu et PDF
     */
    public function update(int $rapportId, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE rapports SET 
                contenu = :contenu,
                fichier_pdf = :fichier_pdf,
                metriques = :metriques,
                status = :status,
                updated_at = NOW()
             WHERE id = :id'
        );

        return $stmt->execute([
            ':id' => $rapportId,
            ':contenu' => $data['contenu'] ?? '',
            ':fichier_pdf' => $data['fichier_pdf'] ?? '',
            ':metriques' => json_encode($data['metriques'] ?? []),
            ':status' => $data['status'] ?? 'termine',
        ]);
    }

    /**
     * Récupérer un rapport
     */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM rapports WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return is_array($result) ? $result : null;
    }

    /**
     * Lister les rapports
     */
    public function all(?string $type = null, int $limit = 20): array
    {
        $query = 'SELECT * FROM rapports';
        $params = [];

        if ($type) {
            $query .= ' WHERE type = :type';
            $params[':type'] = $type;
        }

        $query .= ' ORDER BY created_at DESC LIMIT ' . (int)$limit;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Générer le contenu HTML du rapport
     */
    public function generateHtmlContent(array $metrics, string $titre, string $periodDebut, string $periodFin): string
    {
        $html = '<h1>' . htmlspecialchars($titre) . '</h1>';
        $html .= '<p>Période: ' . htmlspecialchars($periodDebut) . ' à ' . htmlspecialchars($periodFin) . '</p>';
        $html .= '<hr>';

        // Signalements section
        $html .= '<h2>📋 Signalements</h2>';
        $sig = $metrics['signalements'];
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr><td>Total signalements</td><td>' . $sig['total'] . '</td></tr>';
        $html .= '<tr><td>Résolus</td><td>' . $sig['resolus'] . ' (' . $sig['taux_resolution'] . '%)</td></tr>';
        $html .= '<tr><td>En cours</td><td>' . $sig['en_cours'] . '</td></tr>';
        $html .= '<tr><td>En attente</td><td>' . $sig['en_attente'] . '</td></tr>';
        $html .= '<tr><td>Rejetés</td><td>' . $sig['rejetes'] . '</td></tr>';
        $html .= '</table>';

        // Interventions section
        $html .= '<h2>🔧 Interventions</h2>';
        $int = $metrics['interventions'];
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr><td>Total interventions</td><td>' . $int['total'] . '</td></tr>';
        $html .= '<tr><td>Terminées</td><td>' . $int['terminees'] . '</td></tr>';
        $html .= '<tr><td>En cours</td><td>' . $int['en_cours'] . '</td></tr>';
        $html .= '<tr><td>Annulées</td><td>' . $int['annulees'] . '</td></tr>';
        $html .= '<tr><td>Progression moyenne</td><td>' . $int['progression_moyenne'] . '%</td></tr>';
        $html .= '</table>';

        // Coûts section
        $html .= '<h2>💰 Coûts</h2>';
        $costs = $metrics['couts'];
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr><td>Coût total</td><td>' . number_format($costs['cout_total'], 2) . ' TND</td></tr>';
        $html .= '<tr><td>Coût moyen par intervention</td><td>' . number_format($costs['cout_moyen'], 2) . ' TND</td></tr>';
        $html .= '<tr><td>Coût min/max</td><td>' . number_format($costs['cout_min'], 2) . ' / ' . number_format($costs['cout_max'], 2) . ' TND</td></tr>';
        $html .= '</table>';

        // Time tracking section
        $html .= '<h2>⏱️ Durée des interventions</h2>';
        $time = $metrics['time_tracking'];
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr><td>Total sessions</td><td>' . $time['total_sessions'] . '</td></tr>';
        $html .= '<tr><td>Total heures</td><td>' . $time['total_heures'] . ' h</td></tr>';
        $html .= '<tr><td>Durée moyenne</td><td>' . $time['moyenne_minutes'] . ' min</td></tr>';
        $html .= '</table>';

        // Catégories breakdown
        $html .= '<h2>🏷️ Par catégorie</h2>';
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr><th>Catégorie</th><th>Nombre</th></tr>';
        foreach ($metrics['par_categorie'] as $cat) {
            $html .= '<tr><td>' . htmlspecialchars($cat['categorie']) . '</td><td>' . $cat['count'] . '</td></tr>';
        }
        $html .= '</table>';

        return $html;
    }
}
