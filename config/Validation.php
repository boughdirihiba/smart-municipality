<?php

declare(strict_types=1);

namespace Config;

final class Validation
{
    private const PHONE_REGEX = '/^[0-9+()\s.-]{6,20}$/';

    /** @return array<string, string> */
    public static function login(string $mail, string $motdepasse): array
    {
        $errors = [];

        $mail = trim($mail);
        if ($mail === '') {
            $errors['mail'] = "L'email est obligatoire.";
        } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $errors['mail'] = "Format d'email invalide.";
        }

        if ($motdepasse === '') {
            $errors['motdepasse'] = 'Le mot de passe est obligatoire.';
        }

        return $errors;
    }

    /** @return array<string, string> */
    public static function signup(
        string $prenom,
        string $nom,
        string $mail,
        string $motdepasse,
        string $confirm
    ): array {
        $errors = [];

        $prenom = trim($prenom);
        $nom = trim($nom);
        $mail = trim($mail);

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

        return $errors;
    }

    /** @return array<string, string> */
    public static function adminUserCreate(
        string $prenom,
        string $nom,
        string $mail,
        string $telephone,
        string $password,
        string $confirm
    ): array {
        $errors = [];

        $prenom = trim($prenom);
        $nom = trim($nom);
        $mail = trim($mail);
        $telephone = trim($telephone);

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
        if ($telephone !== '' && !preg_match(self::PHONE_REGEX, $telephone)) {
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

        return $errors;
    }

    /** @return array<string, string> */
    public static function adminUserUpdate(
        string $prenom,
        string $nom,
        string $mail,
        string $telephone,
        string $password,
        string $confirm
    ): array {
        $errors = [];

        $prenom = trim($prenom);
        $nom = trim($nom);
        $mail = trim($mail);
        $telephone = trim($telephone);

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
        if ($telephone !== '' && !preg_match(self::PHONE_REGEX, $telephone)) {
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

        return $errors;
    }

    /** @return array<string, string> */
    public static function profileInfo(string $prenom, string $nom, string $mail, string $telephone): array
    {
        $errors = [];

        $prenom = trim($prenom);
        $nom = trim($nom);
        $mail = trim($mail);
        $telephone = trim($telephone);

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
        if ($telephone !== '' && !preg_match(self::PHONE_REGEX, $telephone)) {
            $errors['telephone'] = 'Format de téléphone invalide.';
        }

        return $errors;
    }

    /** @return array<string, string> */
    public static function profilePassword(string $current, string $new, string $confirm): array
    {
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

        return $errors;
    }
}
