<?php

declare(strict_types=1);

use Config\Auth;

$title = isset($title) ? (string) $title : 'Dashboard';
$active = isset($active) ? (string) $active : '';
$contentView = isset($contentView) ? (string) $contentView : '';

Auth::startSession();
$settings = isset($_SESSION['settings']) && is_array($_SESSION['settings']) ? $_SESSION['settings'] : ['notifications' => true, 'dark_mode' => false];
$isDark = (bool)($settings['dark_mode'] ?? false);

$u = Auth::user();
$name = trim((string)($u['prenom'] ?? '') . ' ' . (string)($u['nom'] ?? ''));
$initial = strtoupper(mb_substr($name !== '' ? $name : 'U', 0, 1));

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

$items = [
  'admin-blog' => 'Blog',
  'profile' => 'Profil',
  'admin-signalement' => 'Signalement',
  'admin-events' => 'Événements',
  'admin-map' => 'Carte intelligente',
  'admin-services' => 'Services en ligne',
  'admin-rdv' => 'Rendez-vous',
];

$icon = static function (string $key): string {
  $common = 'width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';

  switch ($key) {
    case 'admin-blog':
      return '<svg ' . $common . '><path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20"/><path d="M20 2H6.5A2.5 2.5 0 0 0 4 4.5v15"/><path d="M8 6h8"/><path d="M8 10h10"/><path d="M8 14h6"/></svg>';
    case 'profile':
      return '<svg ' . $common . '><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>';
    case 'admin-signalement':
      return '<svg ' . $common . '><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M2 14h4"/><path d="M10 8h4"/><path d="M18 16h4"/></svg>';
    case 'admin-events':
      return '<svg ' . $common . '><path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 10h18"/><path d="M5 6h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/></svg>';
    case 'admin-map':
      return '<svg ' . $common . '><path d="M9 18l-6 3V6l6-3 6 3 6-3v15l-6 3-6-3z"/><path d="M9 3v15"/><path d="M15 6v15"/></svg>';
    case 'admin-services':
      return '<svg ' . $common . '><path d="M12 2v6"/><path d="M12 16v6"/><path d="M4.93 4.93l4.24 4.24"/><path d="M14.83 14.83l4.24 4.24"/><path d="M2 12h6"/><path d="M16 12h6"/><path d="M4.93 19.07l4.24-4.24"/><path d="M14.83 9.17l4.24-4.24"/></svg>';
    case 'admin-rdv':
      return '<svg ' . $common . '><path d="M3 4h18"/><path d="M8 2v4"/><path d="M16 2v4"/><path d="M7 8h10"/><path d="M5 8v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8"/><path d="M10 14h4"/></svg>';
    default:
      return '<svg ' . $common . '><path d="M12 2l9 21H3z"/></svg>';
  }
};

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
  <link rel="stylesheet" href="<?= $h($asset('assets/css/admin.css')) ?>">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
</head>
<body class="<?= $isDark ? 'theme-dark' : '' ?>">
  <div class="admin-shell">
    <aside class="sidebar">
      <a class="brand" href="<?= $h($url('index.php?route=admin-blog')) ?>">
        <span class="brand-mark" aria-hidden="true"></span>
        <span>SMART MRC</span>
      </a>

      <nav class="nav-admin" aria-label="Admin">
        <a class="<?= $active === 'admin-blog' ? 'active' : '' ?>" href="<?= $h($url('index.php?route=admin-blog')) ?>">
          <span class="nav-item">
            <span class="nav-ico"><?= $icon('admin-blog') ?></span>
            <span>Blog</span>
          </span>
        </a>
        <a class="<?= $active === 'profile' ? 'active' : '' ?>" href="<?= $h($url('index.php?route=profile')) ?>">
          <span class="nav-item">
            <span class="nav-ico"><?= $icon('profile') ?></span>
            <span>Profil</span>
          </span>
        </a>
        <a class="<?= $active === 'admin-signalement' ? 'active' : '' ?>" href="<?= $h($url('index.php?route=admin-signalement')) ?>">
          <span class="nav-item">
            <span class="nav-ico"><?= $icon('admin-signalement') ?></span>
            <span>Signalement</span>
          </span>
        </a>
        <a class="<?= $active === 'admin-events' ? 'active' : '' ?>" href="<?= $h($url('index.php?route=admin-events')) ?>">
          <span class="nav-item">
            <span class="nav-ico"><?= $icon('admin-events') ?></span>
            <span>Événements</span>
          </span>
        </a>
        <a class="<?= $active === 'admin-map' ? 'active' : '' ?>" href="<?= $h($url('index.php?route=admin-map')) ?>">
          <span class="nav-item">
            <span class="nav-ico"><?= $icon('admin-map') ?></span>
            <span>Carte intelligente</span>
          </span>
        </a>
        <a class="<?= $active === 'admin-services' ? 'active' : '' ?>" href="<?= $h($url('index.php?route=admin-services')) ?>">
          <span class="nav-item">
            <span class="nav-ico"><?= $icon('admin-services') ?></span>
            <span>Services en ligne</span>
          </span>
        </a>
        <a class="<?= $active === 'admin-rdv' ? 'active' : '' ?>" href="<?= $h($url('index.php?route=admin-rdv')) ?>">
          <span class="nav-item">
            <span class="nav-ico"><?= $icon('admin-rdv') ?></span>
            <span>Rendez-vous</span>
          </span>
        </a>
      </nav>
    </aside>

    <div class="admin-content">
      <header class="topbar">
        <div class="title"><?= $h($title) ?></div>
        <div style="display:flex; align-items:center; gap:12px;">
          <input class="input" style="width:280px; max-width:35vw;" type="search" placeholder="Rechercher…" aria-label="Rechercher">
          <div class="notif" title="Notifications" aria-label="Notifications">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
          </div>
          <div class="user-menu">
            <button class="user-button" type="button" aria-haspopup="menu">
              <span class="avatar" aria-hidden="true"><?= $h($initial) ?></span>
              <span style="font-weight:900;"><?= $h($name !== '' ? $name : 'Utilisateur') ?></span>
            </button>
            <div class="dropdown" role="menu">
              <a href="<?= $h($url('index.php?route=profile')) ?>">Mon profil</a>
              <a href="<?= $h($url('index.php?route=events')) ?>">Site public</a>
              <a href="<?= $h($url('index.php?route=logout')) ?>">Déconnexion</a>
            </div>
          </div>
        </div>
      </header>

      <main class="admin-main">
        <?php
          $full = __DIR__ . '/../' . $contentView;
          if (!is_file($full)) {
              echo '<div class="card" style="padding:16px;">Vue introuvable.</div>';
          } else {
              require $full;
          }
        ?>
      </main>
    </div>
  </div>
</body>
</html>
