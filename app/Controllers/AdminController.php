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

    private function ensureAdmin(): void
    {
        if (($_SESSION['user']['role'] ?? 'citoyen') !== 'admin') {
            set_flash('error', 'Acces reserve aux administrateurs.');
            redirect('home/index');
        }
    }

    public function index(): void
    {
        $this->ensureAdmin();

        $categorie = (string)($_GET['categorie'] ?? '');
        $statut = (string)($_GET['statut'] ?? '');

        $items = $this->model->allWithFilters($categorie, $statut);
        $items = $this->applyAiTriage($items);

        $stats = [
            'total' => count($items),
            'en_attente' => 0,
            'en_cours' => 0,
            'resolu' => 0,
            'rejete' => 0,
            'ai_critique' => 0,
        ];

        foreach ($items as $item) {
            $key = (string)($item['statut'] ?? '');
            if (array_key_exists($key, $stats)) {
                $stats[$key] += 1;
            }

            if (($item['triage_level'] ?? '') === 'critique') {
                $stats['ai_critique'] += 1;
            }
        }

        $this->render('backoffice/signalements/list', [
            'title' => 'BackOffice - Signalements',
            'items' => $items,
            'categorie' => $categorie,
            'statut' => $statut,
            'stats' => $stats,
        ]);
    }

    private function applyAiTriage(array $items): array
    {
        $zoneCategoryCounts = [];

        foreach ($items as $item) {
            $statut = (string)($item['statut'] ?? '');
            if (!in_array($statut, ['en_attente', 'en_cours'], true)) {
                continue;
            }

            $zone = strtolower(trim((string)($item['quartier'] ?? '')));
            $cat = strtolower(trim((string)($item['categorie'] ?? '')));
            $key = $zone . '|' . $cat;
            if (!isset($zoneCategoryCounts[$key])) {
                $zoneCategoryCounts[$key] = 0;
            }
            $zoneCategoryCounts[$key] += 1;
        }

        foreach ($items as &$item) {
            $score = 0;
            $reasons = [];

            $statut = (string)($item['statut'] ?? 'en_attente');
            if ($statut === 'en_attente') {
                $score += 2;
                $reasons[] = 'en attente';
            } elseif ($statut === 'en_cours') {
                $score += 1;
                $reasons[] = 'deja en cours';
            }

            $categorie = strtolower(trim((string)($item['categorie'] ?? '')));
            if (in_array($categorie, ['eau', 'eclairage', 'route'], true)) {
                $score += 2;
                $reasons[] = 'categorie sensible';
            }

            $text = strtolower((string)($item['titre'] ?? '') . ' ' . (string)($item['description'] ?? ''));
            if (preg_match('/(danger|accident|bloque|fuite|incendie|panne|urgent|securite)/u', $text) === 1) {
                $score += 3;
                $reasons[] = 'mots critiques detectes';
            }

            $dateRaw = (string)($item['date_signalement'] ?? '');
            $createdAt = strtotime($dateRaw);
            if ($createdAt !== false) {
                $days = (int)floor((time() - $createdAt) / 86400);
                if ($days >= 7 && $statut !== 'resolu' && $statut !== 'rejete') {
                    $score += 2;
                    $reasons[] = 'anciennete superieure a 7 jours';
                } elseif ($days >= 3 && $statut !== 'resolu' && $statut !== 'rejete') {
                    $score += 1;
                    $reasons[] = 'anciennete superieure a 3 jours';
                }
            }

            $zone = strtolower(trim((string)($item['quartier'] ?? '')));
            $key = $zone . '|' . $categorie;
            $similarCount = (int)($zoneCategoryCounts[$key] ?? 0);
            if ($similarCount >= 3) {
                $score += 2;
                $reasons[] = 'repetition locale (' . $similarCount . ')';
            }

            $level = 'faible';
            if ($score >= 8) {
                $level = 'critique';
            } elseif ($score >= 5) {
                $level = 'eleve';
            } elseif ($score >= 3) {
                $level = 'moyen';
            }

            $item['triage_score'] = $score;
            $item['triage_level'] = $level;
            $item['triage_reason'] = !empty($reasons) ? implode(' | ', $reasons) : 'signalement standard';
        }
        unset($item);

        usort($items, static function (array $a, array $b): int {
            $scoreDiff = ((int)($b['triage_score'] ?? 0)) <=> ((int)($a['triage_score'] ?? 0));
            if ($scoreDiff !== 0) {
                return $scoreDiff;
            }

            return strcmp((string)($b['date_signalement'] ?? ''), (string)($a['date_signalement'] ?? ''));
        });

        return $items;
    }

    public function edit(): void
    {
        $this->ensureAdmin();

        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newStatus = trim((string)($_POST['statut'] ?? ''));
            $progressionRaw = trim((string)($_POST['progression'] ?? '0'));
            $latRaw = trim((string)($_POST['latitude'] ?? ''));
            $lngRaw = trim((string)($_POST['longitude'] ?? ''));
            $commentaire = trim((string)($_POST['commentaire_position'] ?? ''));
            $allowed = ['en_attente', 'en_cours', 'resolu', 'rejete'];

            if (!in_array($newStatus, $allowed, true)) {
                set_flash('error', 'Statut invalide.');
                redirect('admin/edit&id=' . $id);
            }

            if (!is_numeric($progressionRaw)) {
                set_flash('error', 'Progression invalide.');
                redirect('admin/edit&id=' . $id);
            }

            $progression = (int)$progressionRaw;
            if ($progression < 0 || $progression > 100) {
                set_flash('error', 'Progression hors plage autorisee.');
                redirect('admin/edit&id=' . $id);
            }

            if ($newStatus === 'resolu' && $progression < 100) {
                $progression = 100;
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

            $ok = $this->model->updateStatusAndPosition($id, $newStatus, $progression, $latitude, $longitude, $commentaire);
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

        $this->render('backoffice/signalements/edit', [
            'title' => 'Modifier statut',
            'item' => $item,
        ]);
    }

    public function delete(): void
    {
        $this->ensureAdmin();

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
