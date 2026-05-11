<?php
// config1.php - avec vérification pour éviter les redéfinitions
if (!defined('GROK_API_KEY')) {
    define('GROK_API_KEY', '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');
}
if (!defined('GROQ_MODEL')) {
    define('GROQ_MODEL', 'llama-3.1-8b-instant');
}