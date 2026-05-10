<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $flash = get_flash();
        require BASE_PATH . '/views/App/Views/layouts/header.php';
        require BASE_PATH . '/views/App/Views/' . $view . '.php';
        require BASE_PATH . '/views/App/Views/layouts/footer.php';
    }
}

