<?php

declare(strict_types=1);

namespace Controles;

use Config\Auth;
use Config\Database;
use Config\Flash;
use Config\View;
use Models\PdoUserRepository;

final class DashboardController
{
    public function dashboard(): void
    {
        Auth::requireAdmin();

        $flash = Flash::consume();

        $repo = new PdoUserRepository(Database::pdo());
        $activeUsers = $repo->countUsers();

        $users = $repo->listUsers(200, 0);
        $editId = (int)($_GET['edit'] ?? 0);
        $editUser = null;
        if ($editId > 0) {
            foreach ($users as $u) {
                if ((int)($u['id'] ?? 0) === $editId) {
                    $editUser = $u;
                    break;
                }
            }
        }

        View::render('layout/admin.php', [
            'title' => 'Dashboard',
            'active' => 'dashboard',
            'contentView' => 'dashboard.php',
            'flash' => $flash,
            'stats' => [
                'total_posts' => 24,
                'active_users' => $activeUsers,
                'comments' => 71,
                'reactions' => 312,
            ],
            'users' => $users,
            'editUser' => $editUser,
        ]);
    }

    public function section(string $key): void
    {
        Auth::requireAdmin();

        $titles = [
            'blog' => 'Blog',
            'signalement' => 'Signalement',
            'events' => 'Événements',
            'map' => 'Carte intelligente',
            'services' => 'Services en ligne',
            'rdv' => 'Rendez-vous',
        ];

        $title = $titles[$key] ?? 'Section';

        View::render('layout/admin.php', [
            'title' => $title,
            'active' => 'admin-' . $key,
            'contentView' => 'admin_section.php',
            'sectionTitle' => $title,
        ]);
    }
}
