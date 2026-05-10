<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$configFile = $root . '/config/config.php';
$viewsRoot = $root . '/views';

if (!file_exists($configFile)) {
    fwrite(STDERR, "Erreur: fichier de configuration introuvable : $configFile\n");
    exit(1);
}

$configContents = file_get_contents($configFile);
if ($configContents === false) {
    fwrite(STDERR, "Erreur: impossible de lire $configFile\n");
    exit(1);
}

$baseUrl = null;
if (preg_match("/define\('BASE_URL',\s*['\"]([^'\"]+)['\"]\);/", $configContents, $matches)) {
    $baseUrl = $matches[1];
}

if ($baseUrl === null) {
    fwrite(STDOUT, "Constante BASE_URL introuvable dans config/config.php. Ajout de la constante...\n");

    $insertAfter = "define('BASE_PATH', dirname(__DIR__));";
    $position = strpos($configContents, $insertAfter);
    if ($position === false) {
        fwrite(STDERR, "Impossible de localiser l'emplacement d'insertion dans config/config.php.\n");
        exit(1);
    }

    $baseUrl = '/smart/smart-municipality';
    $replacement = $insertAfter . "\n" . "define('BASE_URL', '$baseUrl');\n";
    $configContents = str_replace($insertAfter, $replacement, $configContents);
    if (file_put_contents($configFile, $configContents) === false) {
        fwrite(STDERR, "Erreur: impossible de mettre à jour config/config.php\n");
        exit(1);
    }

    fwrite(STDOUT, "BASE_URL défini sur '$baseUrl' dans config/config.php.\n");
} else {
    fwrite(STDOUT, "Constante BASE_URL trouvée : $baseUrl\n");
}

$viewExtensions = ['php', 'html', 'htm'];
$changedFiles = [];
$regex = '/\b(href|src|srcset)\s*=\s*("|\')([^"\']+)\2/i';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsRoot, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $extension = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
    if (!in_array($extension, $viewExtensions, true)) {
        continue;
    }

    $content = file_get_contents($file->getPathname());
    if ($content === false) {
        continue;
    }

    $newContent = preg_replace_callback($regex, function (array $matches) use ($baseUrl) {
        $attr = $matches[1];
        $quote = $matches[2];
        $url = $matches[3];

        if (preg_match('~^(?:https?:|//|#|mailto:|javascript:|<\?php|<\?=)~i', $url)) {
            return $matches[0];
        }

        if (strpos($url, 'BASE_URL') !== false) {
            return $matches[0];
        }

        $normalized = $url;
        if (strpos($normalized, '/smart/smart-municipality') === 0) {
            $normalized = substr($normalized, strlen('/smart/smart-municipality')) ?: '/';
        }

        if (strpos($normalized, 'css/') === 0) {
            $normalized = '/assets/' . ltrim($normalized, '/');
        } elseif (strpos($normalized, 'js/') === 0) {
            $normalized = '/assets/' . ltrim($normalized, '/');
        } elseif (preg_match('~^(images|image|img)/~', $normalized)) {
            $normalized = '/assets/' . ltrim($normalized, '/');
        }

        if ($normalized === '' || $normalized[0] !== '/') {
            $normalized = '/' . ltrim($normalized, '/');
        }

        return sprintf('%s=%s<?php echo BASE_URL; ?>%s%s', $attr, $quote, $normalized, $quote);
    }, $content);

    if ($newContent !== $content) {
        file_put_contents($file->getPathname(), $newContent);
        $changedFiles[] = $file->getPathname();
    }
}

fwrite(STDOUT, "\nTraitement terminé. Fichiers modifiés : " . count($changedFiles) . "\n");
foreach ($changedFiles as $path) {
    fwrite(STDOUT, " - $path\n");
}

if (empty($changedFiles)) {
    fwrite(STDOUT, "Aucun fichier de vue n'a nécessité de mise à jour.\n");
}
