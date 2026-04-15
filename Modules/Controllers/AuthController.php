<?php

declare(strict_types=1);

namespace Modules\Controllers;

use Modules\Core\View;
use Modules\Models\User;
use Throwable;

final class AuthController
{
    private User $users;

    public function __construct()
    {
        $this->users = User::fromDatabase();
    }

    public function showLogin(): void
    {
        View::render('login.html');
    }

    public function showSignup(): void
    {
        View::render('login1.html');
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $mail = trim((string)($_POST['mail'] ?? ''));
        $motdepasse = (string)($_POST['motdepasse'] ?? '');

        if ($mail === '' || $motdepasse === '' || !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('index.php?route=login&error=1');
            return;
        }

        try {
            $user = $this->users->findByMail($mail);
            if (!$user || !isset($user['mdp'])) {
                $this->redirect('index.php?route=login&error=1');
                return;
            }

            $storedPassword = (string) $user['mdp'];
            $isOk = password_verify($motdepasse, $storedPassword);

            // Compat: si mdp est encore en clair, on upgrade.
            if (!$isOk && hash_equals($storedPassword, $motdepasse)) {
                $isOk = true;
                $this->users->upgradePasswordHash((int) $user['id'], password_hash($motdepasse, PASSWORD_DEFAULT));
            }

            if (!$isOk) {
                $this->redirect('index.php?route=login&error=1');
                return;
            }

            session_start();
            session_regenerate_id(true);

            $_SESSION['user'] = [
                'id' => (string)($user['mail'] ?? ''),
                'db_id' => (int)($user['id'] ?? 0),
                'nom' => (string)($user['nom'] ?? ''),
                'prenom' => (string)($user['prenom'] ?? ''),
                'mail' => (string)($user['mail'] ?? ''),
            ];

            $this->redirect('index.php?route=login');
        } catch (Throwable $e) {
            $this->redirect('index.php?route=login&error=1');
        }
    }

    public function signup(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $prenom = trim((string)($_POST['prenom'] ?? ''));
        $nom = trim((string)($_POST['nom'] ?? ''));
        $mail = trim((string)($_POST['email'] ?? ''));
        $motdepasse = (string)($_POST['motdepasse'] ?? '');
        $confirm = (string)($_POST['confirmMotdepasse'] ?? '');

        if ($prenom === '' || $nom === '' || $mail === '' || $motdepasse === '' || $confirm === '') {
            $this->redirect('index.php?route=signup&error=1');
            return;
        }

        if (!filter_var($mail, FILTER_VALIDATE_EMAIL) || $motdepasse !== $confirm || strlen($motdepasse) < 6) {
            $this->redirect('index.php?route=signup&error=1');
            return;
        }

        try {
            if ($this->users->mailExists($mail)) {
                $this->redirect('index.php?route=signup&error=1');
                return;
            }

            $hash = password_hash($motdepasse, PASSWORD_DEFAULT);
            $this->users->create($nom, $prenom, $mail, $hash);

            $this->redirect('index.php?route=login');
        } catch (Throwable $e) {
            $this->redirect('index.php?route=signup&error=1');
        }
    }

    public function logout(): void
    {
        session_start();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }

        session_destroy();
        $this->redirect('index.php?route=login');
    }

    private function redirect(string $to): void
    {
        header('Location: ' . $to);
        exit;
    }
}
