<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Intervention;
use App\Models\Signalement;

class MapController
{
    private Signalement $model;
    private Intervention $interventionModel;

    public function __construct()
    {
        $this->model = new Signalement();
        $this->interventionModel = new Intervention();
    }

    public function data(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $categorie = (string)($_GET['categorie'] ?? '');
        $date = (string)($_GET['date'] ?? '');
        $zone = (string)($_GET['zone'] ?? '');

        $signalements = $this->model->mapData($categorie, $date, $zone);
        foreach ($signalements as &$item) {
            $item['image_url'] = !empty($item['image']) ? UPLOAD_URL . $item['image'] : null;
            $item['entity_type'] = 'signalement';
        }

        $interventionsRaw = $this->interventionModel->mapData($categorie, $date, $zone);
        $interventions = [];
        foreach ($interventionsRaw as $row) {
            $interventions[] = [
                'id' => (int)$row['id'],
                'titre' => (string)$row['titre'],
                'description' => (string)$row['description'],
                'image' => null,
                'image_url' => null,
                'categorie' => (string)$row['type'],
                'latitude' => (float)$row['latitude'],
                'longitude' => (float)$row['longitude'],
                'statut' => (string)$row['statut'],
                'progression' => (int)($row['progression'] ?? 0),
                'date_signalement' => (string)($row['date_intervention'] ?: $row['created_at']),
                'adresse' => null,
                'quartier' => null,
                'entity_type' => 'intervention',
            ];
        }

        $items = array_merge($signalements, $interventions);

        echo json_encode($items, JSON_UNESCAPED_UNICODE);
    }
}
