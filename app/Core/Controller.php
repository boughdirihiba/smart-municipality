<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    // Provide a no-op constructor so child controllers can safely call parent::__construct()
    public function __construct()
    {
        // Intentionally empty
    }
    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $flash = get_flash();

        $appView = BASE_PATH . '/app/Views/' . $view . '.php';
        $legacyView = BASE_PATH . '/views/' . $view . '.php';

        require BASE_PATH . '/app/Views/layouts/header.php';
        if (is_file($appView)) {
            require $appView;
        } elseif (is_file($legacyView)) {
            require $legacyView;
        } else {
            throw new \RuntimeException('View not found: ' . $view);
        }
        require BASE_PATH . '/app/Views/layouts/footer.php';
    }
}
