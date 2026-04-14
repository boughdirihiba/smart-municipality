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
        $this->model = new Signalement();
    }

    public function index(): void
    {
        $categorie = (string)($_GET['categorie'] ?? '');
        $statut = (string)($_GET['statut'] ?? '');

        $items = $this->model->allWithFilters($categorie, $statut);

        $this->render('admin/list', [
            'title' => 'BackOffice - Signalements',
            'items' => $items,
            'categorie' => $categorie,
            'statut' => $statut,
        ]);
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newStatus = trim((string)($_POST['statut'] ?? ''));
            $latRaw = trim((string)($_POST['latitude'] ?? ''));
            $lngRaw = trim((string)($_POST['longitude'] ?? ''));
            $commentaire = trim((string)($_POST['commentaire_position'] ?? ''));
            $allowed = ['en_attente', 'en_cours', 'resolu', 'rejete'];

            if (!in_array($newStatus, $allowed, true)) {
                set_flash('error', 'Statut invalide.');
                redirect('admin/edit&id=' . $id);
            }

            $latitude = null;
            $longitude = null;

            if ($latRaw !== '' || $lngRaw !== '') {
                if (!is_numeric($latRaw) || !is_numeric($lngRaw)) {
                    set_flash('error', 'Latitude/longitude invalides.');
                    redirect('admin/edit&id=' . $id);
                }

                $latitude = (float)$latRaw;
                $longitude = (float)$lngRaw;

                if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                    set_flash('error', 'Coordonnees hors plage autorisee.');
                    redirect('admin/edit&id=' . $id);
                }
            }

            $ok = $this->model->updateStatusAndPosition($id, $newStatus, $latitude, $longitude, $commentaire);
            if ($ok && $newStatus === 'resolu') {
                add_notification('Signalement #' . $id . ' marqué comme résolu.', 'resolved');
            }
            set_flash($ok ? 'success' : 'error', $ok ? 'Mise a jour enregistree.' : 'Erreur de mise a jour.');
            redirect('admin/edit&id=' . $id);
        }

        $item = $this->model->find($id);
        if (!$item) {
            set_flash('error', 'Signalement introuvable.');
            redirect('admin/list');
        }

        $this->render('admin/edit', [
            'title' => 'Modifier statut',
            'item' => $item,
        ]);
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = $this->model->find($id);
        if ($item && !empty($item['image'])) {
            $path = UPLOAD_PATH . $item['image'];
            if (is_file($path)) {
                unlink($path);
            }
        }

        $ok = $this->model->delete($id);
        set_flash($ok ? 'success' : 'error', $ok ? 'Signalement supprimé.' : 'Suppression impossible.');
        redirect('admin/list');
    }
}
