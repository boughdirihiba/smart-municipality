<?php

declare(strict_types=1);

namespace Config;

final class View
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $viewFile, array $data = []): void
    {
        $fullPath = __DIR__ . '/../views/' . $viewFile;
        if (!is_file($fullPath)) {
            http_response_code(500);
            echo 'View not found';
            return;
        }

        extract($data, EXTR_SKIP);
        require $fullPath;
    }
}
