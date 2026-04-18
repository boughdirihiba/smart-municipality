<?php

declare(strict_types=1);

namespace Controles;

use Config\Auth;
use Config\Database;
use Config\Flash;
use Models\PdoUserRepository;
use Throwable;

final class AdminUsersController
{
    public function index(): void
    {
        Auth::requireAdmin();
        header('Location: index.php?route=dashboard');
        exit;
    }

    public function create(): void
    {
        Auth::requireAdmin();

        if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $prenom = trim((string)($_POST['prenom'] ?? ''));
        $nom = trim((string)($_POST['nom'] ?? ''));
        $mail = trim((string)($_POST['mail'] ?? ''));
        $telephone = trim((string)($_POST['telephone'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        $old = [
            'prenom' => $prenom,
            'nom' => $nom,
            'mail' => $mail,
            'telephone' => $telephone,
        ];

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
        if ($password === '') {
            $errors['password'] = 'Mot de passe requis.';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }
        if ($confirm === '') {
            $errors['confirm_password'] = 'Veuillez confirmer le mot de passe.';
        } elseif ($password !== '' && $confirm !== $password) {
            $errors['confirm_password'] = 'Les mots de passe ne correspondent pas.';
        }

        if ($errors !== []) {
            Flash::setErrors($errors, $old);
            header('Location: index.php?route=dashboard#members');
            exit;
        }

        try {
            $repo = new PdoUserRepository(Database::pdo());
            if ($repo->mailExists($mail)) {
                Flash::setErrors(['mail' => 'Cet email est déjà utilisé.'], $old);
                header('Location: index.php?route=dashboard#members');
                exit;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $id = $repo->createUser($nom, $prenom, $mail, $hash);

            $repo->updateUser($id, [
                'nom' => $nom,
                'prenom' => $prenom,
                'mail' => $mail,
                'telephone' => $telephone,
            ]);

            Flash::success('Membre créé avec succès.');
            header('Location: index.php?route=dashboard#members');
            exit;
        } catch (Throwable $e) {
            Flash::setErrors(['mail' => 'Une erreur est survenue. Réessaie plus tard.'], $old);
            header('Location: index.php?route=dashboard#members');
            exit;
        }
    }

    public function update(): void
    {
        Auth::requireAdmin();

        if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            Flash::setErrors(['id' => 'ID invalide.']);
            header('Location: index.php?route=dashboard#members');
            exit;
        }

        $prenom = trim((string)($_POST['prenom'] ?? ''));
        $nom = trim((string)($_POST['nom'] ?? ''));
        $mail = trim((string)($_POST['mail'] ?? ''));
        $telephone = trim((string)($_POST['telephone'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

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

        if ($password !== '' || $confirm !== '') {
            if ($password === '') {
                $errors['password'] = 'Mot de passe requis.';
            } elseif (strlen($password) < 6) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }
            if ($confirm === '') {
                $errors['confirm_password'] = 'Veuillez confirmer le mot de passe.';
            } elseif ($password !== '' && $confirm !== $password) {
                $errors['confirm_password'] = 'Les mots de passe ne correspondent pas.';
            }
        }

        if ($errors !== []) {
            Flash::setErrors($errors, [
                'prenom' => $prenom,
                'nom' => $nom,
                'mail' => $mail,
                'telephone' => $telephone,
            ]);
            header('Location: index.php?route=dashboard&edit=' . $id . '#members');
            exit;
        }

        try {
            $repo = new PdoUserRepository(Database::pdo());

            if ($repo->mailExistsForOtherId($mail, $id)) {
                Flash::setErrors(['mail' => 'Cet email est déjà utilisé.'], [
                    'prenom' => $prenom,
                    'nom' => $nom,
                    'mail' => $mail,
                    'telephone' => $telephone,
                ]);
                header('Location: index.php?route=dashboard&edit=' . $id . '#members');
                exit;
            }

            $repo->updateUser($id, [
                'nom' => $nom,
                'prenom' => $prenom,
                'mail' => $mail,
                'telephone' => $telephone,
            ]);

            if ($password !== '') {
                $repo->updatePassword($id, password_hash($password, PASSWORD_DEFAULT));
            }

            Flash::success('Membre mis à jour.');
            header('Location: index.php?route=dashboard#members');
            exit;
        } catch (Throwable $e) {
            Flash::setErrors(['mail' => 'Une erreur est survenue. Réessaie plus tard.']);
            header('Location: index.php?route=dashboard&edit=' . $id . '#members');
            exit;
        }
    }

    public function delete(): void
    {
        Auth::requireAdmin();

        if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            Flash::setErrors(['id' => 'ID invalide.']);
            header('Location: index.php?route=dashboard#members');
            exit;
        }

        if ($id === Auth::id()) {
            Flash::setErrors(['id' => 'Impossible de supprimer votre propre compte admin.']);
            header('Location: index.php?route=dashboard#members');
            exit;
        }

        try {
            $repo = new PdoUserRepository(Database::pdo());
            $repo->deleteUser($id);
            Flash::success('Membre supprimé.');
            header('Location: index.php?route=dashboard#members');
            exit;
        } catch (Throwable $e) {
            Flash::setErrors(['id' => 'Une erreur est survenue.']);
            header('Location: index.php?route=dashboard#members');
            exit;
        }
    }
}
