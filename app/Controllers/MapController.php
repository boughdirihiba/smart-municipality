<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Signalement;

class MapController
{
    private Signalement $model;

    public function __construct()
    {
        $this->model = new Signalement();
    }

    public function data(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $categorie = (string)($_GET['categorie'] ?? '');
        $date = (string)($_GET['date'] ?? '');
        $zone = (string)($_GET['zone'] ?? '');

        $items = $this->model->mapData($categorie, $date, $zone);
        foreach ($items as &$item) {
            $item['image_url'] = !empty($item['image']) ? UPLOAD_URL . $item['image'] : null;
        }

        echo json_encode($items, JSON_UNESCAPED_UNICODE);
    }
}
