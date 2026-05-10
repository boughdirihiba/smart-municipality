<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Signalement;
use App\Models\Intervention;

class ExportController extends Controller
{
    private Signalement $signalementModel;
    private Intervention $interventionModel;

    public function __construct()
    {
        $this->signalementModel = new Signalement();
        $this->interventionModel = new Intervention();
    }

    private function ensureAdmin(): void
    {
        if (($_SESSION['user']['role'] ?? 'citoyen') !== 'admin') {
            set_flash('error', 'Acces reserve aux administrateurs.');
            redirect('home/index');
        }
    }

    public function exportCsv(): void
    {
        $this->ensureAdmin();

        $signalements = $this->signalementModel->allWithFilters(null, null);
        $interventions = $this->interventionModel->all();

        // Calculate statistics
        $stats = $this->calculateStats($signalements, $interventions);

        // Generate CSV content
        $csv = $this->generateCsvContent($signalements, $interventions, $stats);

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="export_signalements_interventions_' . date('Y-m-d_His') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $csv;
        exit;
    }

    private function calculateStats(array $signalements, array $interventions): array
    {
        $stats = [
            'signalements' => [
                'total' => count($signalements),
                'en_attente' => 0,
                'en_cours' => 0,
                'resolu' => 0,
                'rejete' => 0,
                'par_categorie' => [],
                'par_quartier' => [],
                'par_priorite' => ['urgent' => 0, 'moyen' => 0, 'faible' => 0],
            ],
            'interventions' => [
                'total' => count($interventions),
                'planifiee' => 0,
                'en_cours' => 0,
                'terminee' => 0,
                'annulee' => 0,
                'par_categorie' => [],
                'par_statut_global' => [],
            ],
        ];

        foreach ($signalements as $s) {
            $statut = (string)($s['statut'] ?? 'en_attente');
            if (array_key_exists($statut, $stats['signalements'])) {
                $stats['signalements'][$statut] += 1;
            }

            $categorie = (string)($s['categorie'] ?? 'Autre');
            $stats['signalements']['par_categorie'][$categorie] = ($stats['signalements']['par_categorie'][$categorie] ?? 0) + 1;

            $quartier = (string)($s['quartier'] ?? 'Non specifie');
            $stats['signalements']['par_quartier'][$quartier] = ($stats['signalements']['par_quartier'][$quartier] ?? 0) + 1;

            $priorite = (string)($s['priority'] ?? 'moyen');
            if (array_key_exists($priorite, $stats['signalements']['par_priorite'])) {
                $stats['signalements']['par_priorite'][$priorite] += 1;
            }
        }

        foreach ($interventions as $i) {
            $statut = (string)($i['statut'] ?? 'planifiee');
            if (array_key_exists($statut, $stats['interventions'])) {
                $stats['interventions'][$statut] += 1;
            }

            $categorie = (string)($i['categorie'] ?? 'Autre');
            $stats['interventions']['par_categorie'][$categorie] = ($stats['interventions']['par_categorie'][$categorie] ?? 0) + 1;
        }

        return $stats;
    }

    private function generateCsvContent(array $signalements, array $interventions, array $stats): string
    {
        $lines = [];

        // Add header with export date
        $lines[] = "Export Signalements et Interventions - " . date('Y-m-d H:i:s');
        $lines[] = '';

        // Statistics section
        $lines[] = '=== STATISTIQUES ===';
        $lines[] = '';
        $lines[] = 'SIGNALEMENTS';
        $lines[] = 'Total,' . $stats['signalements']['total'];
        $lines[] = 'En attente,' . $stats['signalements']['en_attente'];
        $lines[] = 'En cours,' . $stats['signalements']['en_cours'];
        $lines[] = 'Resolus,' . $stats['signalements']['resolu'];
        $lines[] = 'Rejetes,' . $stats['signalements']['rejete'];
        $lines[] = '';
        $lines[] = 'Par Priorite';
        $lines[] = 'Urgent,' . $stats['signalements']['par_priorite']['urgent'];
        $lines[] = 'Moyen,' . $stats['signalements']['par_priorite']['moyen'];
        $lines[] = 'Faible,' . $stats['signalements']['par_priorite']['faible'];
        $lines[] = '';
        $lines[] = 'Par Categorie';
        foreach ($stats['signalements']['par_categorie'] as $cat => $count) {
            $lines[] = "$cat,$count";
        }
        $lines[] = '';
        $lines[] = 'Par Quartier';
        foreach ($stats['signalements']['par_quartier'] as $quartier => $count) {
            $lines[] = "$quartier,$count";
        }
        $lines[] = '';
        $lines[] = 'INTERVENTIONS';
        $lines[] = 'Total,' . $stats['interventions']['total'];
        $lines[] = 'Planifiee,' . $stats['interventions']['planifiee'];
        $lines[] = 'En cours,' . $stats['interventions']['en_cours'];
        $lines[] = 'Terminee,' . $stats['interventions']['terminee'];
        $lines[] = 'Annulee,' . $stats['interventions']['annulee'];
        $lines[] = '';
        $lines[] = 'Par Categorie';
        foreach ($stats['interventions']['par_categorie'] as $cat => $count) {
            $lines[] = "$cat,$count";
        }
        $lines[] = '';

        // Signalements detail section
        $lines[] = '=== DETAILS SIGNALEMENTS ===';
        $lines[] = 'ID,Titre,Description,Categorie,Quartier,Adresse,Priorite,Statut,Progression,Latitude,Longitude,Date,Utilisateur';
        foreach ($signalements as $s) {
            $lines[] = $this->escapeCsv([
                $s['id'] ?? '',
                $s['titre'] ?? '',
                $s['description'] ?? '',
                $s['categorie'] ?? '',
                $s['quartier'] ?? '',
                $s['adresse'] ?? '',
                $s['priority'] ?? 'moyen',
                $s['statut'] ?? 'en_attente',
                $s['progression'] ?? 0,
                $s['latitude'] ?? '',
                $s['longitude'] ?? '',
                $s['date_signalement'] ?? '',
                ($s['user_prenom'] ?? '') . ' ' . ($s['user_nom'] ?? ''),
            ]);
        }
        $lines[] = '';

        // Interventions detail section
        $lines[] = '=== DETAILS INTERVENTIONS ===';
        $lines[] = 'ID,Titre,Description,Categorie,Statut,Progression,Latitude,Longitude,Date,Duree_Estimee';
        foreach ($interventions as $i) {
            $lines[] = $this->escapeCsv([
                $i['id'] ?? '',
                $i['titre'] ?? '',
                $i['description'] ?? '',
                $i['categorie'] ?? '',
                $i['statut'] ?? 'planifiee',
                $i['progression'] ?? 0,
                $i['latitude'] ?? '',
                $i['longitude'] ?? '',
                $i['date_intervention'] ?? '',
                $i['duree_estimee'] ?? '',
            ]);
        }

        return implode("\n", $lines);
    }

    private function escapeCsv(array $fields): string
    {
        $escaped = [];
        foreach ($fields as $field) {
            $field = (string)$field;
            if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false) {
                $escaped[] = '"' . str_replace('"', '""', $field) . '"';
            } else {
                $escaped[] = $field;
            }
        }
        return implode(',', $escaped);
    }
}
