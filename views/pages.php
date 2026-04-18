<?php

declare(strict_types=1);

$page = isset($page) ? (string) $page : (string)($_GET['page'] ?? 'login');

$allowedPages = [
    'login',
    'signup',
    'forgot',
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
    'login' => 'Login',
    'signup' => 'Créer un compte',
    'forgot' => 'Mot de passe oublié',
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

$forgotSent = isset($forgotSent) ? (bool) $forgotSent : false;

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
    <link rel="stylesheet" href="<?= htmlspecialchars($asset('views/Login.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>

<body>

<div class="main">

<div class="container">

    <div class="brand">
        <img class="logo" src="<?= htmlspecialchars($asset('views/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="Smart Municipality">
    </div>

    <?php if ($page === 'login'): ?>
        <form id="loginForm" data-form="login" novalidate action="<?= htmlspecialchars($url('index.php?route=login'), ENT_QUOTES, 'UTF-8') ?>" method="post">

            <div class="avatar">
                <img src="<?= htmlspecialchars($asset('views/admin.jpeg'), ENT_QUOTES, 'UTF-8') ?>" alt="Photo admin">
            </div>

            <label for="mail">Email</label>
            <div class="input-group">
                <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M12 12a4 4 0 1 0-4-4a4 4 0 0 0 4 4m0 2c-4.42 0-8 2-8 4.5V21h16v-2.5c0-2.5-3.58-4.5-8-4.5"/>
                </svg>
                <input id="mail" name="mail" type="text" placeholder="Email" value="<?= htmlspecialchars((string)($old['mail'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="<?= isset($errors['mail']) ? 'is-invalid' : '' ?>">
            </div>
            <div class="error-message" id="error-mail"><?= isset($errors['mail']) ? htmlspecialchars((string)$errors['mail'], ENT_QUOTES, 'UTF-8') : '' ?></div>

            <label for="motdepasse">Mot de passe</label>
            <div class="input-group">
                <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M12 17a2 2 0 0 0 2-2a2 2 0 0 0-2-2a2 2 0 0 0-2 2a2 2 0 0 0 2 2m6-6h-1V9a5 5 0 0 0-10 0v2H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2m-3 0H9V9a3 3 0 0 1 6 0z"/>
                </svg>
                <input id="motdepasse" name="motdepasse" type="password" placeholder="Mot de passe" class="<?= isset($errors['motdepasse']) ? 'is-invalid' : '' ?>">
            </div>
            <div class="error-message" id="error-motdepasse"><?= isset($errors['motdepasse']) ? htmlspecialchars((string)$errors['motdepasse'], ENT_QUOTES, 'UTF-8') : '' ?></div>

            <button type="submit">Se connecter</button>

            <a href="<?= htmlspecialchars($url('index.php?route=signup'), ENT_QUOTES, 'UTF-8') ?>">S'inscrire?</a>
            <a href="<?= htmlspecialchars($url('index.php?route=page&page=forgot'), ENT_QUOTES, 'UTF-8') ?>">Mot de passe oublié ?</a>

        </form>

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
                        <input id="prenom" name="prenom" type="text" placeholder="Prénom" value="<?= htmlspecialchars((string)($old['prenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="<?= isset($errors['prenom']) ? 'is-invalid' : '' ?>">
                    </div>
                    <div class="error-message" id="error-prenom"><?= isset($errors['prenom']) ? htmlspecialchars((string)$errors['prenom'], ENT_QUOTES, 'UTF-8') : '' ?></div>
                </div>

                <div class="form-group">
                    <label for="nom">Nom</label>
                    <div class="input-group">
                        <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path fill="currentColor" d="M12 12a4 4 0 1 0-4-4a4 4 0 0 0 4 4m0 2c-4.42 0-8 2-8 4.5V21h16v-2.5c0-2.5-3.58-4.5-8-4.5"/>
                        </svg>
                        <input id="nom" name="nom" type="text" placeholder="Nom" value="<?= htmlspecialchars((string)($old['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="<?= isset($errors['nom']) ? 'is-invalid' : '' ?>">
                    </div>
                    <div class="error-message" id="error-nom"><?= isset($errors['nom']) ? htmlspecialchars((string)$errors['nom'], ENT_QUOTES, 'UTF-8') : '' ?></div>
                </div>
            </div>

            <label for="email">Email</label>
            <div class="input-group">
                <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M20 8l-8 5l-8-5V6l8 5l8-5m0-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2"/>
                </svg>
                <input id="email" name="email" type="text" placeholder="Email" value="<?= htmlspecialchars((string)($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="<?= isset($errors['email']) ? 'is-invalid' : '' ?>">
            </div>
            <div class="error-message" id="error-email"><?= isset($errors['email']) ? htmlspecialchars((string)$errors['email'], ENT_QUOTES, 'UTF-8') : '' ?></div>

            <label for="motdepasse">Mot de passe</label>
            <div class="input-group">
                <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M12 17a2 2 0 0 0 2-2a2 2 0 0 0-2-2a2 2 0 0 0-2 2a2 2 0 0 0 2 2m6-6h-1V9a5 5 0 0 0-10 0v2H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2m-3 0H9V9a3 3 0 0 1 6 0z"/>
                </svg>
                <input id="motdepasse" name="motdepasse" type="password" placeholder="Mot de passe" class="<?= isset($errors['motdepasse']) ? 'is-invalid' : '' ?>">
            </div>
            <div class="error-message" id="error-motdepasse"><?= isset($errors['motdepasse']) ? htmlspecialchars((string)$errors['motdepasse'], ENT_QUOTES, 'UTF-8') : '' ?></div>

            <label for="confirmMotdepasse">Confirmer mot de passe</label>
            <div class="input-group">
                <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M12 17a2 2 0 0 0 2-2a2 2 0 0 0-2-2a2 2 0 0 0-2 2a2 2 0 0 0 2 2m6-6h-1V9a5 5 0 0 0-10 0v2H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2m-3 0H9V9a3 3 0 0 1 6 0z"/>
                </svg>
                <input id="confirmMotdepasse" name="confirmMotdepasse" type="password" placeholder="Confirmer mot de passe" class="<?= isset($errors['confirmMotdepasse']) ? 'is-invalid' : '' ?>">
            </div>
            <div class="error-message" id="error-confirmMotdepasse"><?= isset($errors['confirmMotdepasse']) ? htmlspecialchars((string)$errors['confirmMotdepasse'], ENT_QUOTES, 'UTF-8') : '' ?></div>

            <button type="submit">Créer un compte</button>

            <a href="<?= htmlspecialchars($url('index.php?route=login'), ENT_QUOTES, 'UTF-8') ?>">Déjà un compte ? Se connecter</a>

        </form>

    <?php elseif ($page === 'forgot'): ?>
        <form id="forgotForm" novalidate action="<?= htmlspecialchars($url('index.php?route=page&page=forgot'), ENT_QUOTES, 'UTF-8') ?>" method="post">

            <div class="avatar">
                <img src="<?= htmlspecialchars($asset('views/admin.jpeg'), ENT_QUOTES, 'UTF-8') ?>" alt="Photo admin">
            </div>

            <h2 class="title">Mot de passe oublié</h2>

            <?php if ($forgotSent): ?>
                <p style="text-align:left; margin-top: 12px; font-size: 14px; line-height: 1.6;">
                    Si un compte existe, un email de réinitialisation sera envoyé.
                </p>
            <?php endif; ?>

            <label for="mail">Email</label>
            <div class="input-group">
                <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M20 8l-8 5l-8-5V6l8 5l8-5m0-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2"/>
                </svg>
                <input id="mail" name="mail" type="text" placeholder="Email" value="<?= htmlspecialchars((string)($old['mail'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="<?= isset($errors['mail']) ? 'is-invalid' : '' ?>">
            </div>
            <div class="error-message" id="error-mail"><?= isset($errors['mail']) ? htmlspecialchars((string)$errors['mail'], ENT_QUOTES, 'UTF-8') : '' ?></div>

            <button type="submit">Envoyer</button>

            <a href="<?= htmlspecialchars($url('index.php?route=login'), ENT_QUOTES, 'UTF-8') ?>">Retour au login</a>

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

</div>

</div>

<footer class="footer">
    <a href="<?= htmlspecialchars($url('index.php?route=page&page=propos'), ENT_QUOTES, 'UTF-8') ?>">À propos</a>
    <a href="<?= htmlspecialchars($url('index.php?route=page&page=services'), ENT_QUOTES, 'UTF-8') ?>">Services</a>
    <a href="<?= htmlspecialchars($url('index.php?route=page&page=support'), ENT_QUOTES, 'UTF-8') ?>">Support</a>
    <a href="<?= htmlspecialchars($url('index.php?route=page&page=plan'), ENT_QUOTES, 'UTF-8') ?>">Plan du site</a>
    <a href="<?= htmlspecialchars($url('index.php?route=page&page=contact'), ENT_QUOTES, 'UTF-8') ?>">Contact</a>
</footer>

</body>
</html>
