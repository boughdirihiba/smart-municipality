<?php

declare(strict_types=1);

namespace Controles;

use Config\Auth;
use Config\View;

final class PublicController
{
    public function events(): void
    {
        Auth::requireLogin();
        View::render('layout/app.php', [
            'title' => 'Événements',
            'active' => 'events',
            'contentView' => 'site.php',
            'page' => 'events',
        ]);
    }

    public function map(): void
    {
        Auth::requireLogin();
        View::render('layout/app.php', [
            'title' => 'Carte',
            'active' => 'map',
            'contentView' => 'site.php',
            'page' => 'map',
        ]);
    }

    public function blog(): void
    {
        Auth::requireLogin();
        View::render('layout/app.php', [
            'title' => 'Blog',
            'active' => 'blog',
            'contentView' => 'site.php',
            'page' => 'blog',
        ]);
    }

    public function services(): void
    {
        Auth::requireLogin();
        View::render('layout/app.php', [
            'title' => 'Services',
            'active' => 'services',
            'contentView' => 'site.php',
            'page' => 'services',
        ]);
    }

    public function rdv(): void
    {
        Auth::requireLogin();
        View::render('layout/app.php', [
            'title' => 'Rendez-vous',
            'active' => 'rdv',
            'contentView' => 'site.php',
            'page' => 'rdv',
        ]);
    }
}
