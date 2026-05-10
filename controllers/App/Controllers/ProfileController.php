<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class ProfileController extends Controller
{
    public function index(): void
    {
        $user = $_SESSION['user'] ?? [];
        $this->render('frontoffice/profile', [
            'title' => 'Mon Profil',
            'user'  => $user,
        ]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('profile');
        }

        $prenom    = trim($_POST['prenom'] ?? '');
        $nom       = trim($_POST['nom'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $adresse   = trim($_POST['adresse'] ?? '');

        $errors = [];
        if ($prenom === '') $errors[] = 'Le prénom est obligatoire.';
        if ($nom === '')    $errors[] = 'Le nom est obligatoire.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';

        if ($errors) {
            set_flash('error', implode(' ', $errors));
            redirect('profile');
        }

        $userId = (int)($_SESSION['user']['id'] ?? 0);
        try {
            $pdo  = pdo_connection();
            $stmt = $pdo->prepare(
                'UPDATE utilisateurs SET prenom=:prenom, nom=:nom, email=:email, telephone=:telephone, adresse=:adresse WHERE id=:id'
            );
            $stmt->execute([
                'prenom'    => $prenom,
                'nom'       => $nom,
                'email'     => $email,
                'telephone' => $telephone,
                'adresse'   => $adresse,
                'id'        => $userId,
            ]);

            $_SESSION['user']['prenom']    = $prenom;
            $_SESSION['user']['nom']       = $nom;
            $_SESSION['user']['email']     = $email;
            $_SESSION['user']['telephone'] = $telephone;
            $_SESSION['user']['adresse']   = $adresse;

            set_flash('success', 'Profil mis à jour avec succès.');
        } catch (\Throwable $e) {
            set_flash('error', 'Erreur lors de la mise à jour.');
        }

        redirect('profile');
    }

    public function password(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('profile');
        }

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($new !== $confirm) {
            set_flash('error', 'Les mots de passe ne correspondent pas.');
            redirect('profile');
        }
        if (strlen($new) < 6) {
            set_flash('error', 'Le mot de passe doit contenir au moins 6 caractères.');
            redirect('profile');
        }

        $userId = (int)($_SESSION['user']['id'] ?? 0);
        try {
            $pdo  = pdo_connection();
            $row  = $pdo->prepare('SELECT password FROM utilisateurs WHERE id=:id');
            $row->execute(['id' => $userId]);
            $dbUser = $row->fetch();

            if (!$dbUser || !password_verify($current, $dbUser['password'])) {
                set_flash('error', 'Mot de passe actuel incorrect.');
                redirect('profile');
            }

            $hash = password_hash($new, PASSWORD_BCRYPT);
            $upd  = $pdo->prepare('UPDATE utilisateurs SET password=:password WHERE id=:id');
            $upd->execute(['password' => $hash, 'id' => $userId]);

            set_flash('success', 'Mot de passe changé avec succès.');
        } catch (\Throwable $e) {
            set_flash('error', 'Erreur lors du changement de mot de passe.');
        }

        redirect('profile');
    }
}
