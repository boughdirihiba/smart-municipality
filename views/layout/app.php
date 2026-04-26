<?php

declare(strict_types=1);

use Config\Auth;

$title = isset($title) ? (string) $title : 'Smart Municipality';
$active = isset($active) ? (string) $active : '';
$contentView = isset($contentView) ? (string) $contentView : '';

Auth::startSession();
$settings = isset($_SESSION['settings']) && is_array($_SESSION['settings']) ? $_SESSION['settings'] : ['notifications' => true, 'dark_mode' => false];
$isDark = (bool)($settings['dark_mode'] ?? false);

$u = Auth::user();
$name = trim((string)($u['prenom'] ?? '') . ' ' . (string)($u['nom'] ?? ''));
$initialSource = $name !== '' ? $name : 'U';
$initial = function_exists('mb_substr')
  ? strtoupper((string) mb_substr($initialSource, 0, 1))
  : strtoupper(substr($initialSource, 0, 1));

$scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
$base = str_replace('\\', '/', dirname($scriptName));
$base = rtrim($base, '/');
if ($base === '' || $base === '.') {
    $base = '';
}

$h = static function (string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
};
$asset = static function (string $path) use ($base): string {
    return $base . '/' . ltrim($path, '/');
};
$url = static function (string $path) use ($base): string {
    if ($path === '') {
        return $base . '/';
    }
    if ($path[0] === '/') {
        return $base . $path;
    }
    return $base . '/' . $path;
};

$navItems = [
  'profile' => ['label' => 'Profil', 'href' => 'index.php?route=profile'],
  'events' => ['label' => 'Événements', 'href' => 'index.php?route=events'],
  'map' => ['label' => 'Carte', 'href' => 'index.php?route=map'],
  'blog' => ['label' => 'Blog', 'href' => 'index.php?route=blog'],
  'services' => ['label' => 'Services', 'href' => 'index.php?route=services'],
  'rdv' => ['label' => 'Rendez-vous', 'href' => 'index.php?route=rdv'],
];

?><!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= $h($title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $h($asset('assets/css/theme.css')) ?>">
  <link rel="stylesheet" href="<?= $h($asset('assets/css/app.css')) ?>">
  <script defer src="<?= $h($asset('assets/js/form-validation.js')) ?>"></script>
</head>
<body class="<?= $isDark ? 'theme-dark' : '' ?>">
  <div class="app-shell">
    <header class="navbar">
      <div class="container nav-inner">
        <a class="brand" href="<?= $h($url('index.php?route=events')) ?>">
          <span class="brand-mark" aria-hidden="true"></span>
          <span>Smart Municipality</span>
        </a>

        <nav class="nav-links" aria-label="Navigation">
          <?php foreach ($navItems as $key => $it): ?>
            <a
              class="<?= $active === $key ? 'active' : '' ?>"
              href="<?= $h($url($it['href'])) ?>"
            ><?= $h($it['label']) ?></a>
          <?php endforeach; ?>
        </nav>

        <div class="nav-actions">
          <input class="input search" type="search" placeholder="Rechercher…" aria-label="Rechercher">

          <div class="user-menu">
            <button class="user-button" type="button" aria-haspopup="menu">
              <span class="avatar" aria-hidden="true"><?= $h($initial) ?></span>
              <span style="font-weight:900; max-width:170px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                <?= $h($name !== '' ? $name : 'Utilisateur') ?>
              </span>
            </button>
            <div class="dropdown" role="menu">
              <a href="<?= $h($url('index.php?route=profile')) ?>">Mon profil</a>
              <a href="<?= $h($url('index.php?route=logout')) ?>">Déconnexion</a>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="main">
      <div class="container">
        <?php
          $full = __DIR__ . '/../' . $contentView;
          if (!is_file($full)) {
              echo '<div class="card" style="padding:16px;">Vue introuvable.</div>';
          } else {
              require $full;
          }
        ?>
      </div>
    </main>
  </div>
</body>
</html>
