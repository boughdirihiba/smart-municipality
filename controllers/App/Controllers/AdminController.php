<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Signalement;

class AdminController extends Controller
{
    private Signalement $model;

    public function __construct()
    {
        if (($_SESSION['user']['role'] ?? 'citoyen') !== 'admin') {
            set_flash('error', 'Accès réservé aux administrateurs.');
            redirect('home/index');
        }
        $this->model = new Signalement();
    }

    public function index(): void
    {
        $categorie = (string)($_GET['categorie'] ?? '');
        $statut    = (string)($_GET['statut'] ?? '');

        $items = $this->model->allWithFilters(
            $categorie !== '' ? $categorie : null,
            $statut    !== '' ? $statut    : null
        );

        $stats = [
            'total'       => count($items),
            'en_attente'  => 0,
            'en_cours'    => 0,
            'resolu'      => 0,
            'ai_critique' => 0,
        ];

        foreach ($items as $item) {
            $s = $item['statut'] ?? '';
            if ($s === 'en_attente') { $stats['en_attente']++; }
            elseif ($s === 'en_cours') { $stats['en_cours']++; }
            elseif ($s === 'resolu')   { $stats['resolu']++; }

            if (($item['triage_level'] ?? '') === 'urgent') {
                $stats['ai_critique']++;
            }
        }

        $this->render('backoffice/signalements/list', [
            'title'     => 'BackOffice - Signalements',
            'items'     => $items,
            'stats'     => $stats,
            'categorie' => $categorie,
            'statut'    => $statut,
        ]);
    }

    public function edit(): void
    {
        $id   = (int)($_GET['id'] ?? 0);
        $item = $this->model->find($id);

        if (!$item) {
            set_flash('error', 'Signalement introuvable.');
            redirect('admin/list');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newStatut     = (string)($_POST['statut'] ?? $item['statut']);
            $progression   = (int)($_POST['progression'] ?? $item['progression'] ?? 0);
            $latRaw        = trim((string)($_POST['latitude'] ?? ''));
            $lonRaw        = trim((string)($_POST['longitude'] ?? ''));
            $commentaire   = trim((string)($_POST['commentaire_position'] ?? ''));

            $latitude  = $latRaw !== '' && is_numeric($latRaw)  ? (float)$latRaw  : null;
            $longitude = $lonRaw !== '' && is_numeric($lonRaw) ? (float)$lonRaw : null;

            $ok = $this->model->updateStatusAndPosition($id, $newStatut, $progression, $latitude, $longitude, $commentaire ?: null);

            if ($ok) {
                add_notification('Signalement #' . $id . ' mis à jour par admin.', 'admin');
                set_flash('success', 'Signalement mis à jour avec succès.');
            } else {
                set_flash('error', 'Erreur lors de la mise à jour.');
            }

            redirect('admin/list');
        }

        $this->render('backoffice/signalements/edit', [
            'title' => 'Modifier le signalement #' . $id,
            'item'  => $item,
        ]);
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0 && $this->model->delete($id)) {
            set_flash('success', 'Signalement supprimé.');
        } else {
            set_flash('error', 'Erreur lors de la suppression.');
        }

        redirect('admin/list');
    }
}
