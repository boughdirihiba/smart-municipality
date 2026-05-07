<?php

declare(strict_types=1);

$page = isset($page) ? (string) $page : (string)($_GET['page'] ?? 'home');

$allowedPages = [
    'home',
    'login',
    'signup',
    'propos',
    'services',
    'support',
    'plan',
    'contact',
];

if (!in_array($page, $allowedPages, true)) {
    http_response_code(404);
    echo 'Not Found';
    return;
}

$titles = [
    'home' => 'Accueil',
    'login' => 'Login',
    'signup' => 'Créer un compte',
    'propos' => 'À propos',
    'services' => 'Services',
    'support' => 'Support',
    'plan' => 'Plan du site',
    'contact' => 'Contact',
];

$title = $titles[$page] ?? 'Page';

$flash = isset($flash) && is_array($flash) ? $flash : null;
$errors = is_array($flash) && isset($flash['errors']) && is_array($flash['errors']) ? $flash['errors'] : [];
$old = is_array($flash) && isset($flash['old']) && is_array($flash['old']) ? $flash['old'] : [];
$success = is_array($flash) && isset($flash['success']) ? (string)$flash['success'] : '';

$captchaSiteKey = isset($captchaSiteKey) ? (string) $captchaSiteKey : '';

$isAuth = in_array($page, ['login', 'signup'], true);

$scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
$base = str_replace('\\', '/', dirname($scriptName));
$base = rtrim($base, '/');
if ($base === '' || $base === '.') {
    $base = '';
}

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

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <?php if ($page === 'home'): ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= htmlspecialchars($asset('views/landing.css'), ENT_QUOTES, 'UTF-8') ?>">
        <script defer src="<?= htmlspecialchars($asset('views/landing.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php else: ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= htmlspecialchars($asset('assets/css/theme.css'), ENT_QUOTES, 'UTF-8') ?>">
        <link rel="stylesheet" href="<?= htmlspecialchars($asset('assets/css/face-id.css'), ENT_QUOTES, 'UTF-8') ?>">
        <link rel="stylesheet" href="<?= htmlspecialchars($asset('views/Login.css'), ENT_QUOTES, 'UTF-8') ?>">

        <?php if ($page === 'login'): ?>
            <script>
                window.__TURNSTILE_SITEKEY = <?= json_encode($captchaSiteKey, JSON_UNESCAPED_SLASHES) ?>;
            </script>
            <script defer src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit"></script>
        <?php endif; ?>

        <script defer src="<?= htmlspecialchars($asset('assets/js/form-validation.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
        <script>
            window.__FACEID_CONFIG = {
                faceApiLocal: <?= json_encode($asset('assets/vendor/face-api/face-api.min.js'), JSON_UNESCAPED_SLASHES) ?>,
                faceApiCdn: 'https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js',
                modelUrl: <?= json_encode($asset('assets/vendor/face-api/models'), JSON_UNESCAPED_SLASHES) ?>,
                enrollUrl: <?= json_encode($url('index.php?route=faceid-enroll'), JSON_UNESCAPED_SLASHES) ?>,
                loginUrl: <?= json_encode($url('index.php?route=faceid-login'), JSON_UNESCAPED_SLASHES) ?>
            };
        </script>
        <script defer src="<?= htmlspecialchars($asset('assets/js/face-id.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endif; ?>
</head>

<body>

<?php if ($page === 'home'): ?>
    <div class="landing">
        <header class="landing-nav">
            <a class="landing-logo" href="<?= htmlspecialchars($url('index.php?route=page&page=home'), ENT_QUOTES, 'UTF-8') ?>" aria-label="SMART MRC">
                <span class="landing-logoMark"></span>
                <span class="landing-logoText">SMART MRC</span>
            </a>

            <nav class="landing-links" aria-label="Navigation">
                <a href="<?= htmlspecialchars($url('index.php?route=page&page=home'), ENT_QUOTES, 'UTF-8') ?>">Home</a>
                <a href="<?= htmlspecialchars($url('index.php?route=page&page=services'), ENT_QUOTES, 'UTF-8') ?>">Services</a>
                <a href="<?= htmlspecialchars($url('index.php?route=page&page=propos'), ENT_QUOTES, 'UTF-8') ?>">About Us</a>
                <a href="<?= htmlspecialchars($url('index.php?route=page&page=contact'), ENT_QUOTES, 'UTF-8') ?>">Contact</a>
            </nav>

            <div class="landing-actions">
                <a class="btn btn-outline" href="<?= htmlspecialchars($url('index.php?route=login'), ENT_QUOTES, 'UTF-8') ?>">Login</a>
                <button class="menu-btn" type="button" aria-label="Menu">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path fill="currentColor" d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z" />
                    </svg>
                </button>
            </div>
        </header>

        <main class="landing-hero">
            <section class="hero-left" aria-hidden="true">
                <div class="blob blob-1"></div>
                <div class="blob blob-2"></div>
                <div class="blob blob-3"></div>
                <div class="hero-card">
                    <div class="hero-cardTitle">Smart MRC</div>
                    <div class="hero-cardText">Accès rapide aux services</div>
                </div>
            </section>

            <section class="hero-right">
                <div class="kicker">SMART MUNICIPALITY PLATFORM</div>
                <h1>Your Smart City Services in One Place</h1>
                <p class="desc">Accédez à vos services municipaux, prenez des rendez-vous et gérez vos démarches en ligne — simplement, rapidement et en toute sécurité.</p>

                <form class="search" action="<?= htmlspecialchars($url('index.php'), ENT_QUOTES, 'UTF-8') ?>" method="get">
                    <input type="hidden" name="route" value="page">
                    <input type="hidden" name="page" value="services">
                    <span class="search-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" focusable="false">
                            <path fill="currentColor" d="M10 2a8 8 0 1 0 4.9 14.3l4.4 4.4l1.4-1.4l-4.4-4.4A8 8 0 0 0 10 2m0 2a6 6 0 1 1 0 12a6 6 0 0 1 0-12"/>
                        </svg>
                    </span>
                    <input name="q" type="text" placeholder="Rechercher un service..." autocomplete="off">
                    <button class="search-btn" type="submit">Rechercher</button>
                </form>

                <div class="cta">
                    <a class="btn btn-primary" href="<?= htmlspecialchars($url('index.php?route=page&page=services'), ENT_QUOTES, 'UTF-8') ?>">Accéder aux services</a>
                    <a class="btn btn-outline" href="<?= htmlspecialchars($url('index.php?route=login'), ENT_QUOTES, 'UTF-8') ?>">Se connecter</a>
                </div>
            </section>
        </main>
    </div>

<?php else: ?>

    <div class="main">

    <?php if ($isAuth): ?>
        <div class="auth-shell">
            <header class="auth-topbar">
                <a class="auth-topbarLogo" href="<?= htmlspecialchars($url('index.php?route=page&page=home'), ENT_QUOTES, 'UTF-8') ?>" aria-label="Accueil">
                    <img src="<?= htmlspecialchars($asset('views/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="Smart Municipality">
                </a>

                <nav class="auth-topbarLinks" aria-label="Navigation">
                    <a href="<?= htmlspecialchars($url('index.php?route=page&page=home'), ENT_QUOTES, 'UTF-8') ?>">Accueil</a>
                    <a href="<?= htmlspecialchars($url('index.php?route=page&page=services'), ENT_QUOTES, 'UTF-8') ?>">Services</a>
                    <a href="<?= htmlspecialchars($url('index.php?route=page&page=propos'), ENT_QUOTES, 'UTF-8') ?>">À propos</a>
                    <a href="<?= htmlspecialchars($url('index.php?route=page&page=contact'), ENT_QUOTES, 'UTF-8') ?>">Contact</a>
                </nav>
            </header>
    <?php endif; ?>

    <div class="container">

        <?php if ($isAuth): ?>
            <div class="auth-card card">
                <div class="brand">
                    <img class="logo" src="<?= htmlspecialchars($asset('views/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="Smart Municipality">
                </div>
        <?php else: ?>
            <div class="brand">
                <img class="logo" src="<?= htmlspecialchars($asset('views/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="Smart Municipality">
            </div>
        <?php endif; ?>

    <?php if ($page === 'login'): ?>
        <form id="loginForm" data-form="login" novalidate action="<?= htmlspecialchars($url('index.php?route=login'), ENT_QUOTES, 'UTF-8') ?>" method="post">

            <div class="avatar">
                <img src="<?= htmlspecialchars($asset('views/admin.jpeg'), ENT_QUOTES, 'UTF-8') ?>" alt="Photo admin">
            </div>

            <?php if ($success !== ''): ?>
                <div style="text-align:left; margin-top: 12px; font-size: 14px; line-height: 1.6; font-weight: 900; color: var(--green-700);">
                    ✓ <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <label for="mail">Email</label>
            <div class="input-group">
                <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M12 12a4 4 0 1 0-4-4a4 4 0 0 0 4 4m0 2c-4.42 0-8 2-8 4.5V21h16v-2.5c0-2.5-3.58-4.5-8-4.5"/>
                </svg>
                <input id="mail" name="mail" type="text" placeholder="Email" value="<?= htmlspecialchars((string)($old['mail'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="input <?= isset($errors['mail']) ? 'is-invalid' : '' ?>">
            </div>
            <div class="error-message" id="error-mail"><?= isset($errors['mail']) ? htmlspecialchars((string)$errors['mail'], ENT_QUOTES, 'UTF-8') : '' ?></div>

            <label for="motdepasse">Mot de passe</label>
            <div class="input-group">
                <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M12 17a2 2 0 0 0 2-2a2 2 0 0 0-2-2a2 2 0 0 0-2 2a2 2 0 0 0 2 2m6-6h-1V9a5 5 0 0 0-10 0v2H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2m-3 0H9V9a3 3 0 0 1 6 0z"/>
                </svg>
                <input id="motdepasse" name="motdepasse" type="password" placeholder="Mot de passe" class="input <?= isset($errors['motdepasse']) ? 'is-invalid' : '' ?>">
            </div>
            <div class="error-message" id="error-motdepasse"><?= isset($errors['motdepasse']) ? htmlspecialchars((string)$errors['motdepasse'], ENT_QUOTES, 'UTF-8') : '' ?></div>

            <div class="captcha-card" data-js-login-captcha>
                <div class="captcha-row">
                    <label class="captcha-check" for="robot_check">
                        <input id="robot_check" name="robot_check" type="checkbox" autocomplete="off">
                        <span class="captcha-box" aria-hidden="true"></span>
                        <span class="captcha-text">I’m not a robot</span>
                    </label>

                    <div class="captcha-robot" aria-hidden="true">
                        <svg viewBox="0 0 48 48" focusable="false" aria-hidden="true">
                            <path fill="currentColor" d="M22 6a2 2 0 0 1 4 0v2.1a16 16 0 0 1 14 13.7l2.1.2a2 2 0 1 1-.4 4l-1.7-.2V34a8 8 0 0 1-8 8H16a8 8 0 0 1-8-8v-8.2l-1.7.2a2 2 0 1 1-.4-4l2.1-.2A16 16 0 0 1 22 8.1zM16 18a10 10 0 0 0-10 10v6a4 4 0 0 0 4 4h28a4 4 0 0 0 4-4v-6a10 10 0 0 0-10-10zm5 6a3 3 0 1 1 0 6a3 3 0 0 1 0-6m14 0a3 3 0 1 1 0 6a3 3 0 0 1 0-6"/>
                        </svg>
                    </div>
                </div>

                <!-- Real CAPTCHA (Turnstile) kept invisible to preserve the exact existing design -->
                <div data-turnstile-container aria-hidden="true" style="position:absolute; left:-9999px; top:-9999px; width:1px; height:1px; overflow:hidden;"></div>
                <input type="hidden" id="turnstile_token" name="turnstile_token" value="">
            </div>
            <div class="error-message" id="error-captcha"><?= isset($errors['captcha']) ? htmlspecialchars((string)$errors['captcha'], ENT_QUOTES, 'UTF-8') : '' ?></div>

            <div class="login-submitWrap" data-js-login-submit>
                <button class="btn btn-primary" type="submit">Se connecter</button>
            </div>

            <button class="btn btn-ghost" type="button" data-faceid-login-btn>Connexion avec Face ID</button>

            <a href="<?= htmlspecialchars($url('index.php?route=signup'), ENT_QUOTES, 'UTF-8') ?>">S'inscrire?</a>

        </form>

        <div id="faceIdLoginModal" class="faceid-modal" aria-hidden="true">
            <div class="faceid-backdrop"></div>
            <div class="faceid-dialog" role="dialog" aria-modal="true" aria-label="Connexion Face ID">
                <div class="faceid-head">
                    <div class="faceid-title">Connexion avec Face ID</div>
                    <button class="faceid-close" type="button" data-faceid-close>Fermer</button>
                </div>
                <div class="faceid-body">
                    <div class="faceid-videoWrap">
                        <video class="faceid-video" playsinline autoplay muted></video>
                    </div>
                    <div class="faceid-msg" data-faceid-msg></div>
                    <div class="faceid-actions">
                        <button class="btn btn-ghost" type="button" data-faceid-start>Ouvrir la caméra</button>
                        <button class="btn btn-primary" type="button" data-faceid-login>Se connecter</button>
                    </div>
                    <div class="muted" style="font-size:12px; font-weight:700;">
                        Astuce: saisissez votre email puis regardez la caméra.
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($page === 'signup'): ?>
        <form id="signupForm" data-form="signup" novalidate action="<?= htmlspecialchars($url('index.php?route=signup'), ENT_QUOTES, 'UTF-8') ?>" method="post">

            <div class="avatar">
                <img src="<?= htmlspecialchars($asset('views/admin.jpeg'), ENT_QUOTES, 'UTF-8') ?>" alt="Photo admin">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <div class="input-group">
                        <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path fill="currentColor" d="M12 12a4 4 0 1 0-4-4a4 4 0 0 0 4 4m0 2c-4.42 0-8 2-8 4.5V21h16v-2.5c0-2.5-3.58-4.5-8-4.5"/>
                        </svg>
                        <input id="prenom" name="prenom" type="text" placeholder="Prénom" value="<?= htmlspecialchars((string)($old['prenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="input <?= isset($errors['prenom']) ? 'is-invalid' : '' ?>">
                    </div>
                    <div class="error-message" id="error-prenom"><?= isset($errors['prenom']) ? htmlspecialchars((string)$errors['prenom'], ENT_QUOTES, 'UTF-8') : '' ?></div>
                </div>

                <div class="form-group">
                    <label for="nom">Nom</label>
                    <div class="input-group">
                        <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path fill="currentColor" d="M12 12a4 4 0 1 0-4-4a4 4 0 0 0 4 4m0 2c-4.42 0-8 2-8 4.5V21h16v-2.5c0-2.5-3.58-4.5-8-4.5"/>
                        </svg>
                        <input id="nom" name="nom" type="text" placeholder="Nom" value="<?= htmlspecialchars((string)($old['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="input <?= isset($errors['nom']) ? 'is-invalid' : '' ?>">
                    </div>
                    <div class="error-message" id="error-nom"><?= isset($errors['nom']) ? htmlspecialchars((string)$errors['nom'], ENT_QUOTES, 'UTF-8') : '' ?></div>
                </div>
            </div>

            <label for="email">Email</label>
            <div class="input-group">
                <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M20 8l-8 5l-8-5V6l8 5l8-5m0-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2"/>
                </svg>
                <input id="email" name="email" type="text" placeholder="Email" value="<?= htmlspecialchars((string)($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="input <?= isset($errors['email']) ? 'is-invalid' : '' ?>">
            </div>
            <div class="error-message" id="error-email"><?= isset($errors['email']) ? htmlspecialchars((string)$errors['email'], ENT_QUOTES, 'UTF-8') : '' ?></div>

            <label for="motdepasse">Mot de passe</label>
            <div class="input-group">
                <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M12 17a2 2 0 0 0 2-2a2 2 0 0 0-2-2a2 2 0 0 0-2 2a2 2 0 0 0 2 2m6-6h-1V9a5 5 0 0 0-10 0v2H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2m-3 0H9V9a3 3 0 0 1 6 0z"/>
                </svg>
                <input id="motdepasse" name="motdepasse" type="password" placeholder="Mot de passe" class="input <?= isset($errors['motdepasse']) ? 'is-invalid' : '' ?>">
            </div>
            <div class="error-message" id="error-motdepasse"><?= isset($errors['motdepasse']) ? htmlspecialchars((string)$errors['motdepasse'], ENT_QUOTES, 'UTF-8') : '' ?></div>

            <label for="confirmMotdepasse">Confirmer mot de passe</label>
            <div class="input-group">
                <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M12 17a2 2 0 0 0 2-2a2 2 0 0 0-2-2a2 2 0 0 0-2 2a2 2 0 0 0 2 2m6-6h-1V9a5 5 0 0 0-10 0v2H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2m-3 0H9V9a3 3 0 0 1 6 0z"/>
                </svg>
                <input id="confirmMotdepasse" name="confirmMotdepasse" type="password" placeholder="Confirmer mot de passe" class="input <?= isset($errors['confirmMotdepasse']) ? 'is-invalid' : '' ?>">
            </div>
            <div class="error-message" id="error-confirmMotdepasse"><?= isset($errors['confirmMotdepasse']) ? htmlspecialchars((string)$errors['confirmMotdepasse'], ENT_QUOTES, 'UTF-8') : '' ?></div>

            <button class="btn btn-primary" type="submit">Créer un compte</button>

            <a href="<?= htmlspecialchars($url('index.php?route=login'), ENT_QUOTES, 'UTF-8') ?>">Déjà un compte ? Se connecter</a>

        </form>

    <?php elseif ($page === 'propos'): ?>
        <form novalidate>
            <h2 class="title">À propos</h2>
            <p style="text-align:left; margin-top: 12px; font-size: 14px; line-height: 1.6;">
                Cette page présente l’application et son objectif.
            </p>
            <a href="<?= htmlspecialchars($url('index.php?route=login'), ENT_QUOTES, 'UTF-8') ?>">Retour au login</a>
        </form>

    <?php elseif ($page === 'services'): ?>
        <form novalidate>
            <h2 class="title">Services</h2>
            <p style="text-align:left; margin-top: 12px; font-size: 14px; line-height: 1.6;">
                Cette page décrit les services proposés.
            </p>
            <a href="<?= htmlspecialchars($url('index.php?route=login'), ENT_QUOTES, 'UTF-8') ?>">Retour au login</a>
        </form>

    <?php elseif ($page === 'support'): ?>
        <form novalidate>
            <h2 class="title">Support</h2>
            <p style="text-align:left; margin-top: 12px; font-size: 14px; line-height: 1.6;">
                Cette page fournit des informations de support et d’assistance.
            </p>
            <a href="<?= htmlspecialchars($url('index.php?route=login'), ENT_QUOTES, 'UTF-8') ?>">Retour au login</a>
        </form>

    <?php elseif ($page === 'plan'): ?>
        <form novalidate>
            <h2 class="title">Plan du site</h2>
            <p style="text-align:left; margin-top: 12px; font-size: 14px; line-height: 1.6;">
                Cette page donne une vue d’ensemble des pages disponibles.
            </p>
            <a href="<?= htmlspecialchars($url('index.php?route=login'), ENT_QUOTES, 'UTF-8') ?>">Retour au login</a>
        </form>

    <?php elseif ($page === 'contact'): ?>
        <form novalidate>
            <h2 class="title">Contact</h2>
            <p style="text-align:left; margin-top: 12px; font-size: 14px; line-height: 1.6;">
                Cette page contient les informations de contact.
            </p>
            <a href="<?= htmlspecialchars($url('index.php?route=login'), ENT_QUOTES, 'UTF-8') ?>">Retour au login</a>
        </form>

    <?php endif; ?>

        <?php if ($isAuth): ?>
            </div>
        <?php endif; ?>

    </div>

    </div>

    <?php if ($isAuth): ?>
        </div>
    <?php endif; ?>

    <footer class="footer">
        <a href="<?= htmlspecialchars($url('index.php?route=page&page=propos'), ENT_QUOTES, 'UTF-8') ?>">À propos</a>
        <a href="<?= htmlspecialchars($url('index.php?route=page&page=services'), ENT_QUOTES, 'UTF-8') ?>">Services</a>
        <a href="<?= htmlspecialchars($url('index.php?route=page&page=support'), ENT_QUOTES, 'UTF-8') ?>">Support</a>
        <a href="<?= htmlspecialchars($url('index.php?route=page&page=plan'), ENT_QUOTES, 'UTF-8') ?>">Plan du site</a>
        <a href="<?= htmlspecialchars($url('index.php?route=page&page=contact'), ENT_QUOTES, 'UTF-8') ?>">Contact</a>
    </footer>

<?php endif; ?>

</body>
</html>
