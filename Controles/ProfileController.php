<?php

declare(strict_types=1);

namespace Controles;

use Config\Auth;
use Config\Database;
use Config\Flash;
use Config\View;
use Models\PdoUserRepository;
use Throwable;

final class ProfileController
{
    public function show(): void
    {
        Auth::requireLogin();
        $flash = Flash::consume();

        $repo = new PdoUserRepository(Database::pdo());
        $user = $repo->findById(Auth::id());

        if (!$user) {
            Auth::logout();
            header('Location: index.php?route=login');
            exit;
        }

        View::render('layout/app.php', [
            'title' => 'Profil',
            'active' => 'profile',
            'contentView' => 'site.php',
            'page' => 'profile',
            'flash' => $flash,
            'user' => $user,
        ]);
    }

    public function update(): void
    {
        Auth::requireLogin();

        if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $action = (string)($_POST['action'] ?? 'info');

        if ($action === 'password') {
            $this->changePassword();
            return;
        }

        if ($action === 'settings') {
            $this->saveSettings();
            return;
        }

        $this->saveInfo();
    }

    private function saveInfo(): void
    {
        $prenom = trim((string)($_POST['prenom'] ?? ''));
        $nom = trim((string)($_POST['nom'] ?? ''));
        $mail = trim((string)($_POST['mail'] ?? ''));
        $telephone = trim((string)($_POST['telephone'] ?? ''));

        $errors = [];

        if ($prenom === '') {
            $errors['prenom'] = 'Le prénom est obligatoire.';
        }
        if ($nom === '') {
            $errors['nom'] = 'Le nom est obligatoire.';
        }
        if ($mail === '') {
            $errors['mail'] = "L'email est obligatoire.";
        } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $errors['mail'] = "Format d'email invalide.";
        }
        if ($telephone !== '' && !preg_match('/^[0-9+()\s.-]{6,20}$/', $telephone)) {
            $errors['telephone'] = 'Format de téléphone invalide.';
        }

        if ($errors !== []) {
            Flash::setErrors($errors, [
                'prenom' => $prenom,
                'nom' => $nom,
                'mail' => $mail,
                'telephone' => $telephone,
            ]);
            header('Location: index.php?route=profile');
            exit;
        }

        try {
            $repo = new PdoUserRepository(Database::pdo());
            $repo->updateProfile(Auth::id(), [
                'prenom' => $prenom,
                'nom' => $nom,
                'mail' => $mail,
                'telephone' => $telephone,
            ]);

            Auth::startSession();
            $_SESSION['user']['prenom'] = $prenom;
            $_SESSION['user']['nom'] = $nom;
            $_SESSION['user']['mail'] = $mail;
            $_SESSION['user']['telephone'] = $telephone;

            Flash::success('Profil mis à jour.');
            header('Location: index.php?route=profile');
            exit;
        } catch (Throwable $e) {
            Flash::setErrors(['mail' => 'Une erreur est survenue. Réessaie plus tard.'], [
                'prenom' => $prenom,
                'nom' => $nom,
                'mail' => $mail,
                'telephone' => $telephone,
            ]);
            header('Location: index.php?route=profile');
            exit;
        }
    }

    private function changePassword(): void
    {
        $current = (string)($_POST['current_password'] ?? '');
        $new = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        $errors = [];

        if ($current === '') {
            $errors['current_password'] = 'Mot de passe actuel requis.';
        }
        if ($new === '') {
            $errors['new_password'] = 'Nouveau mot de passe requis.';
        } elseif (strlen($new) < 6) {
            $errors['new_password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }
        if ($confirm === '') {
            $errors['confirm_password'] = 'Veuillez confirmer le mot de passe.';
        } elseif ($new !== '' && $new !== $confirm) {
            $errors['confirm_password'] = 'Les mots de passe ne correspondent pas.';
        }

        if ($errors !== []) {
            Flash::setErrors($errors);
            header('Location: index.php?route=profile');
            exit;
        }

        try {
            $repo = new PdoUserRepository(Database::pdo());
            $user = $repo->findById(Auth::id());

            if (!$user || !password_verify($current, $user->getMdp())) {
                Flash::setErrors(['current_password' => 'Mot de passe actuel incorrect.']);
                header('Location: index.php?route=profile');
                exit;
            }

            $repo->updatePassword($user->getId(), password_hash($new, PASSWORD_DEFAULT));
            Flash::success('Mot de passe mis à jour.');
            header('Location: index.php?route=profile');
            exit;
        } catch (Throwable $e) {
            Flash::setErrors(['new_password' => 'Une erreur est survenue. Réessaie plus tard.']);
            header('Location: index.php?route=profile');
            exit;
        }
    }

    private function saveSettings(): void
    {
        Auth::startSession();

        $notifications = isset($_POST['notifications']);
        $darkMode = isset($_POST['dark_mode']);

        $_SESSION['settings'] = [
            'notifications' => $notifications,
            'dark_mode' => $darkMode,
        ];

        Flash::success('Paramètres enregistrés.');
        header('Location: index.php?route=profile');
        exit;
    }
}
