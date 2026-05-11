<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use Config\Auth;
use App\Models\Signalement;

class HomeController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin('index.php?route=auth/login');

        if (Auth::isAdmin()) {
            redirect('admin/list');
        }

        $signalementModel = new Signalement();
        $userId = Auth::id();
        $userSignalements = $userId > 0 ? $signalementModel->allByUser($userId) : [];
        $latestSignalements = array_slice($userSignalements, 0, 3);
        $resolvedCount = 0;
        $pendingCount = 0;

        foreach ($userSignalements as $item) {
            if (($item['statut'] ?? '') === 'resolu') {
                $resolvedCount++;
            } elseif (($item['statut'] ?? '') === 'en_attente') {
                $pendingCount++;
            }
        }

        $this->render('frontoffice/home', [
            'title' => 'Accueil - Carte Intelligente',
            'userSignalementCount' => count($userSignalements),
            'userResolvedCount' => $resolvedCount,
            'userPendingCount' => $pendingCount,
            'latestSignalements' => $latestSignalements,
        ]);
    }

    public function notificationsSeen(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
            return;
        }

        mark_notifications_seen();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true]);
    }
}
