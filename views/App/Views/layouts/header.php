<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title ?? APP_NAME); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <style>
    /* ═══════════════════════════════════════════════════════════════
       NAVBAR — Professional icon-based with tooltip labels
    ═══════════════════════════════════════════════════════════════ */
    .navbar {
        position: sticky;
        top: 0;
        z-index: 1100;
        display: flex;
        align-items: center;
        gap: 0;
        padding: 0 24px;
        height: 62px;
        background: linear-gradient(135deg, #0B4F30 0%, #1A7A4E 100%);
        box-shadow: 0 2px 16px rgba(11,79,48,0.22);
    }

    /* ── Brand ── */
    .nav-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        flex-shrink: 0;
        margin-right: 18px;
    }
    .nav-brand img {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        object-fit: cover;
        background: rgba(255,255,255,0.15);
    }
    .nav-brand-text {
        font-family: 'Poppins', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
        line-height: 1.2;
        letter-spacing: -0.2px;
    }
    .nav-brand-text span {
        color: #3DDC84;
    }

    /* ── Nav links ── */
    .nav-links {
        display: flex;
        align-items: center;
        list-style: none;
        margin: 0;
        padding: 0;
        gap: 2px;
        flex: 1;
    }

    .nav-links li {
        position: relative;
    }

    .nav-links li a {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        border-radius: 14px;
        color: rgba(255,255,255,0.75);
        text-decoration: none;
        font-size: 1.2rem;
        transition: background 0.18s, color 0.18s, transform 0.18s;
        position: relative;
    }

    .nav-links li a:hover {
        background: rgba(255,255,255,0.14);
        color: #fff;
        transform: translateY(-2px);
    }

    .nav-links li a.active {
        background: rgba(255,255,255,0.18);
        color: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    .nav-links li a.active::after {
        content: '';
        position: absolute;
        bottom: 4px;
        left: 50%;
        transform: translateX(-50%);
        width: 18px;
        height: 3px;
        background: #3DDC84;
        border-radius: 99px;
    }

    /* ── Tooltip on hover ── */
    .nav-links li a::before {
        content: attr(data-label);
        position: absolute;
        bottom: -36px;
        left: 50%;
        transform: translateX(-50%) translateY(4px);
        background: #0B4F30;
        color: #fff;
        font-size: 0.72rem;
        font-weight: 600;
        font-family: 'Poppins', sans-serif;
        padding: 4px 10px;
        border-radius: 6px;
        white-space: nowrap;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.18s, transform 0.18s;
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        z-index: 9999;
        letter-spacing: 0.3px;
    }

    .nav-links li a::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-bottom-color: #0B4F30;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.18s;
        z-index: 9999;
    }

    /* Override the active indicator — only apply after for active, not both */
    .nav-links li a.active::after {
        content: '';
        position: absolute;
        bottom: 4px;
        left: 50%;
        transform: translateX(-50%);
        width: 18px;
        height: 3px;
        background: #3DDC84;
        border-radius: 99px;
        border: none;
        opacity: 1;
    }

    .nav-links li a:hover::before {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    .nav-links li a:hover::after {
        opacity: 1;
    }

    /* Don't show tooltip arrow on active items */
    .nav-links li a.active:hover::after {
        bottom: 4px;
        width: 18px;
        height: 3px;
        background: #3DDC84;
        border-radius: 99px;
        border: none;
    }

    /* ── Right side ── */
    .nav-right {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
    }

    /* ── Accessibility bar (lang / theme / font) ── */
    .accessibility-panel {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .nav-divider {
        width: 1px;
        height: 24px;
        background: rgba(255,255,255,0.2);
        margin: 0 4px;
    }

    .accessibility-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 30px;
        padding: 0 10px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.25);
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        font-size: 0.78rem;
        font-weight: 600;
        font-family: 'Poppins', sans-serif;
        background: rgba(255,255,255,0.07);
        transition: background 0.18s, color 0.18s, border-color 0.18s;
        letter-spacing: 0.3px;
    }

    .accessibility-btn:hover,
    .accessibility-btn.active {
        background: rgba(255,255,255,0.2);
        color: #fff;
        border-color: rgba(255,255,255,0.45);
    }

    .accessibility-btn.active {
        background: rgba(61,220,132,0.25);
        border-color: #3DDC84;
        color: #3DDC84;
    }

    /* ── Search ── */
    .nav-search {
        display: flex;
        align-items: center;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 999px;
        padding: 0 14px;
        height: 34px;
        gap: 8px;
        transition: background 0.2s, border-color 0.2s;
        cursor: text;
    }

    .nav-search:hover,
    .nav-search:focus-within {
        background: rgba(255,255,255,0.18);
        border-color: rgba(255,255,255,0.4);
    }

    .nav-search-icon {
        color: rgba(255,255,255,0.6);
        font-size: 0.9rem;
    }

    .nav-search input {
        background: transparent;
        border: none;
        outline: none;
        color: #fff;
        font-size: 0.82rem;
        font-family: 'Poppins', sans-serif;
        width: 130px;
    }

    .nav-search input::placeholder {
        color: rgba(255,255,255,0.45);
    }

    /* ── Mobile toggle ── */
    .mobile-toggle {
        display: none;
        flex-direction: column;
        gap: 5px;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 4px;
        margin-left: auto;
    }

    .mobile-toggle span {
        display: block;
        width: 22px;
        height: 2px;
        background: #fff;
        border-radius: 2px;
        transition: 0.3s;
    }

    /* ── Responsive ── */
    @media (max-width: 900px) {
        .mobile-toggle { display: flex; }

        .nav-links {
            display: none;
            position: absolute;
            top: 62px;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #0B4F30, #1A7A4E);
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: center;
            padding: 10px;
            gap: 4px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        .nav-links.open { display: flex; }

        .nav-links li a::before,
        .nav-links li a::after { display: none; }

        .nav-links li a {
            width: 52px;
            height: 52px;
        }

        .accessibility-panel { display: none; }
        .nav-search { display: none; }
    }
    </style>
</head>
<?php
$currentRoute  = $_GET['route']  ?? 'home/index';
$currentAction = $_GET['action'] ?? '';
$userRole      = $_SESSION['user']['role'] ?? 'citoyen';
$currentTheme  = $_SESSION['user_theme'] ?? 'light';
$currentFontSize = $_SESSION['font_size'] ?? 100;
$currentLang   = $_SESSION['app_lang'] ?? 'fr';
$redirectUrl   = rawurlencode($_SERVER['REQUEST_URI'] ?? '/index.php');
?>
<body class="role-<?php echo $userRole; ?> theme-<?php echo $currentTheme; ?><?php echo $currentTheme === 'dark' ? ' dark-mode' : ''; ?>" style="font-size: <?php echo $currentFontSize; ?>%;">

<?php if (empty($hideNavbar)): ?>
<nav class="navbar" id="navbar">

    <!-- Brand -->
    <a class="nav-brand" href="<?php echo BASE_URL; ?>/index.php?route=home/index">
        <img src="<?php echo BASE_URL; ?>/public/uploads/sidebar-photo.svg" alt="Logo">
        <span class="nav-brand-text">Smart <span>Municipality</span></span>
    </a>

    <!-- Mobile toggle -->
    <button class="mobile-toggle" type="button" aria-label="Menu"
            onclick="document.querySelector('.nav-links').classList.toggle('open')">
        <span></span><span></span><span></span>
    </button>

    <!-- Nav links — icons only, tooltip on hover -->
    <ul class="nav-links">
        <li>
            <a href="<?php echo BASE_URL; ?>/index.php?route=profile"
               data-label="Profil"
               class="<?php echo $currentRoute === 'profile' ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i>
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>/index.php?action=evenements"
               data-label="Événements"
               class="<?php echo in_array($currentAction, ['evenements']) ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i>
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>/index.php?route=home/index"
               data-label="Carte"
               class="<?php echo $currentRoute === 'home/index' && empty($currentAction) ? 'active' : ''; ?>">
                <i class="fas fa-map-marked-alt"></i>
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>/index.php?action=blog"
               data-label="Blog"
               class="<?php echo $currentAction === 'blog' ? 'active' : ''; ?>">
                <i class="fas fa-newspaper"></i>
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>/index.php?action=manage"
               data-label="Demandes"
               class="<?php echo $currentAction === 'manage' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i>
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>/index.php?action=rendez_vous"
               data-label="Rendez-vous"
               class="<?php echo $currentAction === 'rendez_vous' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i>
            </a>
        </li>
        <?php if ($userRole === 'admin'): ?>
        <li>
            <a href="<?php echo BASE_URL; ?>/index.php?route=admin/list"
               data-label="Dashboard"
               class="<?php echo str_starts_with($currentRoute, 'admin/') ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <!-- Right side -->
    <div class="nav-right">

        <!-- Accessibility -->
        <div class="accessibility-panel">

            <!-- Language -->
            <a class="accessibility-btn <?php echo $currentLang === 'fr' ? 'active' : ''; ?>"
               href="<?php echo BASE_URL; ?>/index.php?action=setLanguage&lang=fr&redirect=<?php echo $redirectUrl; ?>">FR</a>
            <a class="accessibility-btn <?php echo $currentLang === 'en' ? 'active' : ''; ?>"
               href="<?php echo BASE_URL; ?>/index.php?action=setLanguage&lang=en&redirect=<?php echo $redirectUrl; ?>">EN</a>
            <a class="accessibility-btn <?php echo $currentLang === 'ar' ? 'active' : ''; ?>"
               href="<?php echo BASE_URL; ?>/index.php?action=setLanguage&lang=ar&redirect=<?php echo $redirectUrl; ?>">AR</a>

            <div class="nav-divider"></div>

            <!-- Font size -->
            <a class="accessibility-btn" title="Augmenter la taille"
               href="<?php echo BASE_URL; ?>/index.php?action=setFontSize&size=<?php echo min(130, $currentFontSize + 10); ?>&redirect=<?php echo $redirectUrl; ?>">A+</a>
            <a class="accessibility-btn" title="Réduire la taille"
               href="<?php echo BASE_URL; ?>/index.php?action=setFontSize&size=<?php echo max(80, $currentFontSize - 10); ?>&redirect=<?php echo $redirectUrl; ?>">A−</a>

            <div class="nav-divider"></div>

            <!-- Theme -->
            <a class="accessibility-btn <?php echo $currentTheme === 'light' ? 'active' : ''; ?>"
               title="Mode clair"
               href="<?php echo BASE_URL; ?>/index.php?action=setTheme&theme=light&redirect=<?php echo $redirectUrl; ?>">☀️</a>
            <a class="accessibility-btn <?php echo $currentTheme === 'dark' ? 'active' : ''; ?>"
               title="Mode sombre"
               href="<?php echo BASE_URL; ?>/index.php?action=setTheme&theme=dark&redirect=<?php echo $redirectUrl; ?>">🌙</a>

        </div>

        <!-- Search -->
        <div class="nav-search">
            <span class="nav-search-icon"><i class="fas fa-search"></i></span>
            <input type="text" placeholder="Rechercher...">
        </div>

    </div>
</nav>
<?php endif; ?>

<div class="app-shell">
    <?php if (($userRole !== 'citoyen' || !empty($forceSidebar)) && empty($hideSidebar)): ?>
        <?php require BASE_PATH . '/views/App/Views/layouts/sidebar.php'; ?>
    <?php endif; ?>
    <main class="app-content">

    <?php if (!empty($flash)): ?>
        <div class="alert <?php echo $flash['type'] === 'success' ? 'alert-success' : 'alert-error'; ?>">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>