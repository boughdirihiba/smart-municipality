<?php
// config/Language.php
// Language/translation stub — extend as needed.

if (!defined('APP_LANG')) {
    define('APP_LANG', 'fr');
}

$GLOBALS['__lang'] = [
    'fr' => [
        'error_required'    => 'Ce champ est obligatoire.',
        'error_invalid'     => 'Valeur invalide.',
        'success_saved'     => 'Enregistrement réussi.',
        'success_deleted'   => 'Suppression réussie.',
        'error_not_found'   => 'Élément introuvable.',
        'error_permission'  => 'Accès non autorisé.',
        'error_db'          => 'Erreur de base de données.',
    ],
];

function lang(string $key, string $locale = APP_LANG): string
{
    return $GLOBALS['__lang'][$locale][$key] ?? $key;
}
