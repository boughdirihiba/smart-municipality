<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use Config\Auth;
use Config\Captcha;
use Config\Validation;
use Config\View;
use PDO;

require_once BASE_PATH . '/config/Auth.php';
require_once BASE_PATH . '/config/Captcha.php';
require_once BASE_PATH . '/config/Validation.php';
require_once BASE_PATH . '/config/View.php';

class AuthController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function index(): void
    {
        $this->login();
    }

    public function login(): void
    {
        Auth::startSession();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (Auth::check()) {
                $this->redirectAfterLogin();
            }

            $this->renderPage('login');
            return;
        }

        $mail = trim((string)($_POST['mail'] ?? ''));
        $motdepasse = (string)($_POST['motdepasse'] ?? '');
        $turnstileToken = trim((string)($_POST['turnstile_token'] ?? ''));

        $errors = Validation::login($mail, $motdepasse);
        if ($errors !== []) {
            $this->renderPage('login', [
                'errors' => $errors,
                'old' => ['mail' => $mail],
            ]);
            return;
        }

        if (Captcha::isConfigured() && $turnstileToken !== '' && !Captcha::verifyTurnstile($turnstileToken, $_SERVER['REMOTE_ADDR'] ?? null)) {
            $this->renderPage('login', [
                'errors' => ['captcha' => 'CAPTCHA invalide. Réessaie.'],
                'old' => ['mail' => $mail],
            ]);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'SELECT id, nom, prenom, email, mot_de_passe, avatar, telephone, role, adresse
                 FROM utilisateurs
                 WHERE email = :email
                 LIMIT 1'
            );
            $stmt->execute([':email' => $mail]);
            $user = $stmt->fetch();

            $storedPassword = is_array($user) ? (string)($user['mot_de_passe'] ?? '') : '';
            if (!is_array($user) || !$this->passwordMatches($motdepasse, $storedPassword)) {
                $this->renderPage('login', [
                    'errors' => ['motdepasse' => 'Email ou mot de passe incorrect.'],
                    'old' => ['mail' => $mail],
                ]);
                return;
            }

            if ($storedPassword !== '' && $this->shouldUpgradePassword($storedPassword, $motdepasse)) {
                $upgradeStmt = $this->pdo->prepare('UPDATE utilisateurs SET mot_de_passe = :mot_de_passe WHERE id = :id');
                $upgradeStmt->execute([
                    ':mot_de_passe' => password_hash($motdepasse, PASSWORD_DEFAULT),
                    ':id' => (int)$user['id'],
                ]);
            }

            session_regenerate_id(true);

            $displayName = trim((string)($user['prenom'] ?? '') . ' ' . (string)($user['nom'] ?? ''));
            if ($displayName === '') {
                $displayName = $mail;
            }

            $role = (string)($user['role'] ?? 'citoyen');
            $avatar = (string)($user['avatar'] ?? 'sidebar-photo.svg');

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'db_id' => (int)$user['id'],
                'nom' => (string)($user['nom'] ?? ''),
                'prenom' => (string)($user['prenom'] ?? ''),
                'name' => $displayName,
                'email' => (string)($user['email'] ?? $mail),
                'mail' => (string)($user['email'] ?? $mail),
                'telephone' => (string)($user['telephone'] ?? ''),
                'adresse' => (string)($user['adresse'] ?? ''),
                'avatar' => $avatar !== '' ? $avatar : 'sidebar-photo.svg',
                'role' => $role !== '' ? $role : 'citoyen',
            ];
            $_SESSION['user_name'] = $displayName;
            $_SESSION['user_role'] = $role !== '' ? $role : 'citoyen';
            $_SESSION['user_avatar'] = $avatar !== '' ? $avatar : 'sidebar-photo.svg';

            $this->redirectAfterLogin();
        } catch (\Throwable $e) {
            $this->renderPage('login', [
                'errors' => ['mail' => 'Une erreur est survenue. Réessaie plus tard.'],
                'old' => ['mail' => $mail],
            ]);
        }
    }

    public function signup(): void
    {
        Auth::startSession();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->renderPage('signup');
            return;
        }

        $prenom = trim((string)($_POST['prenom'] ?? ''));
        $nom = trim((string)($_POST['nom'] ?? ''));
        $mail = trim((string)($_POST['email'] ?? ''));
        $motdepasse = (string)($_POST['motdepasse'] ?? '');
        $confirm = (string)($_POST['confirmMotdepasse'] ?? '');

        $errors = Validation::signup($prenom, $nom, $mail, $motdepasse, $confirm);
        if ($errors !== []) {
            $this->renderPage('signup', [
                'errors' => $errors,
                'old' => [
                    'prenom' => $prenom,
                    'nom' => $nom,
                    'email' => $mail,
                ],
            ]);
            return;
        }

        try {
            $existsStmt = $this->pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email LIMIT 1');
            $existsStmt->execute([':email' => $mail]);
            if ($existsStmt->fetchColumn()) {
                $this->renderPage('signup', [
                    'errors' => ['email' => 'Cet email est déjà utilisé.'],
                    'old' => [
                        'prenom' => $prenom,
                        'nom' => $nom,
                        'email' => $mail,
                    ],
                ]);
                return;
            }

            $stmt = $this->pdo->prepare(
                'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, avatar, statut)
                 VALUES (:nom, :prenom, :email, :mot_de_passe, :role, :avatar, :statut)'
            );
            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $mail,
                ':mot_de_passe' => password_hash($motdepasse, PASSWORD_DEFAULT),
                ':role' => 'citoyen',
                ':avatar' => 'sidebar-photo.svg',
                ':statut' => 'actif',
            ]);

            $this->renderPage('login', [
                'success' => 'Compte créé. Vous pouvez maintenant vous connecter.',
            ]);
        } catch (\Throwable $e) {
            $this->renderPage('signup', [
                'errors' => ['email' => 'Une erreur est survenue. Réessaie plus tard.'],
                'old' => [
                    'prenom' => $prenom,
                    'nom' => $nom,
                    'email' => $mail,
                ],
            ]);
        }
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('index.php?route=auth/login');
    }

    private function renderPage(string $page, array $flash = []): void
    {
        View::render('pages.php', array_merge([
            'page' => $page,
            'flash' => $flash,
            'captchaSiteKey' => Captcha::siteKey(),
        ], $flash));
    }

    private function redirectAfterLogin(): void
    {
        $route = Auth::isAdmin() ? 'admin/list' : 'home/index';
        $this->redirect('index.php?route=' . $route);
    }

    private function redirect(string $to): void
    {
        header('Location: ' . $to);
        exit;
    }

    private function passwordMatches(string $plainPassword, string $storedPassword): bool
    {
        if ($storedPassword === '') {
            return false;
        }

        if (password_verify($plainPassword, $storedPassword)) {
            return true;
        }

        if (strlen($storedPassword) === 32 && ctype_xdigit($storedPassword)) {
            return hash_equals(strtolower($storedPassword), md5($plainPassword));
        }

        return hash_equals($storedPassword, $plainPassword);
    }

    private function shouldUpgradePassword(string $storedPassword, string $plainPassword): bool
    {
        if (password_get_info($storedPassword)['algo'] !== 0) {
            return false;
        }

        return $storedPassword === $plainPassword || (strlen($storedPassword) === 32 && ctype_xdigit($storedPassword));
    }
}
