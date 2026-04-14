<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        if (isset($_GET['role']) && in_array($_GET['role'], ['citoyen', 'admin'], true)) {
            $_SESSION['user']['role'] = $_GET['role'];
        }

        $this->render('home', [
            'title' => 'Accueil - Carte Intelligente',
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
