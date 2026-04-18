<?php

declare(strict_types=1);

namespace Controles;

use Config\Database;
use Config\Auth;
use Config\View;
use Models\PdoUserRepository;
use Models\UserRepository;
use PDO;
use Throwable;

final class AuthController
{
    private PDO $pdo;
    private UserRepository $repo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
        $this->repo = new PdoUserRepository($this->pdo);
    }

    public function showLogin(): void
    {
        $flash = $this->consumeFlash();
        View::render('pages.php', ['page' => 'login', 'flash' => $flash]);
    }

    public function showSignup(): void
    {
        $flash = $this->consumeFlash();
        View::render('pages.php', ['page' => 'signup', 'flash' => $flash]);
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

        $errors = [];

        if ($mail === '') {
            $errors['mail'] = "L'email est obligatoire.";
        } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $errors['mail'] = "Format d'email invalide.";
        }

        if ($motdepasse === '') {
            $errors['motdepasse'] = 'Le mot de passe est obligatoire.';
        }

        if ($errors !== []) {
            $this->setFlashErrors($errors, ['mail' => $mail]);
            $this->redirect('index.php?route=login');
            return;
        }

        try {
            $user = $this->repo->findByMail($mail);
            if (!$user || $user->getMdp() === '') {
                $this->setFlashErrors(['motdepasse' => 'Email ou mot de passe incorrect.'], ['mail' => $mail]);
                $this->redirect('index.php?route=login');
                return;
            }

            $storedPassword = $user->getMdp();

            // If the DB schema truncates hashes (e.g., mdp VARCHAR(20/50)), login will always fail.
            // Bcrypt hashes are typically 60 chars; Argon2 can be longer.
            $looksHashed = str_starts_with($storedPassword, '$2y$')
                || str_starts_with($storedPassword, '$2a$')
                || str_starts_with($storedPassword, '$argon2');
            if ($looksHashed && strlen($storedPassword) < 55) {
                $this->setFlashErrors([
                    'motdepasse' => "Compte non connectable: le hash du mot de passe a été tronqué (ancien schéma BD). Mets 'utilisateur.mdp' en VARCHAR(255) puis recrée le compte ou réinitialise le mot de passe.",
                ], ['mail' => $mail]);
                $this->redirect('index.php?route=login');
                return;
            }

            $isOk = password_verify($motdepasse, $storedPassword);

            // Compat: si mdp est encore en clair, on upgrade.
            if (!$isOk && hash_equals($storedPassword, $motdepasse)) {
                $isOk = true;
                $this->repo->updatePassword($user->getId(), password_hash($motdepasse, PASSWORD_DEFAULT));
            }

            if (!$isOk) {
                $this->setFlashErrors(['motdepasse' => 'Email ou mot de passe incorrect.'], ['mail' => $mail]);
                $this->redirect('index.php?route=login');
                return;
            }

            Auth::login($user);
            if (Auth::isAdmin()) {
                $this->redirect('index.php?route=dashboard');
            }
            $this->redirect('index.php?route=profile');
        } catch (Throwable $e) {
            $this->setFlashErrors(['motdepasse' => 'Une erreur est survenue. Réessaie plus tard.'], ['mail' => $mail]);
            $this->redirect('index.php?route=login');
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

        $old = [
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => $mail,
        ];

        $errors = [];

        if ($prenom === '') {
            $errors['prenom'] = 'Le prénom est obligatoire.';
        }

        if ($nom === '') {
            $errors['nom'] = 'Le nom est obligatoire.';
        }

        if ($mail === '') {
            $errors['email'] = "L'email est obligatoire.";
        } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Format d'email invalide.";
        }

        if ($motdepasse === '') {
            $errors['motdepasse'] = 'Le mot de passe est obligatoire.';
        } elseif (strlen($motdepasse) < 6) {
            $errors['motdepasse'] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }

        if ($confirm === '') {
            $errors['confirmMotdepasse'] = 'Veuillez confirmer le mot de passe.';
        } elseif ($motdepasse !== '' && $motdepasse !== $confirm) {
            $errors['confirmMotdepasse'] = 'Les mots de passe ne correspondent pas.';
        }

        if ($errors !== []) {
            $this->setFlashErrors($errors, $old);
            $this->redirect('index.php?route=signup');
            return;
        }

        try {
            if ($this->repo->mailExists($mail)) {
                $this->setFlashErrors(['email' => 'Cet email est déjà utilisé.'], $old);
                $this->redirect('index.php?route=signup');
                return;
            }

            $hash = password_hash($motdepasse, PASSWORD_DEFAULT);
            $this->repo->createUser($nom, $prenom, $mail, $hash);

            // Safety: if the DB truncates the hash, we block completion and show a clear fix.
            $created = $this->repo->findByMail($mail);
            if ($created && $created->getMdp() !== '' && !hash_equals($created->getMdp(), $hash)) {
                $this->setFlashErrors([
                    'motdepasse' => "Configuration BD: le hash du mot de passe est tronqué. Modifie 'utilisateur.mdp' en VARCHAR(255).",
                ], $old);
                $this->redirect('index.php?route=signup');
                return;
            }

            $this->redirect('index.php?route=login');
        } catch (Throwable $e) {
            $this->setFlashErrors(['email' => 'Une erreur est survenue. Réessaie plus tard.'], $old);
            $this->redirect('index.php?route=signup');
        }
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('index.php?route=login');
    }

    private function redirect(string $to): void
    {
        header('Location: ' . $to);
        exit;
    }

    /**
     * @param array<string, string> $errors
     * @param array<string, mixed> $old
     */
    private function setFlashErrors(array $errors, array $old = []): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['_flash'] = [
            'errors' => $errors,
            'old' => $old,
        ];
    }

    /**
     * @return array{errors?:array<string,string>, old?:array<string,mixed>}|null
     */
    private function consumeFlash(): ?array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $flash = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);

        return is_array($flash) ? $flash : null;
    }
}
