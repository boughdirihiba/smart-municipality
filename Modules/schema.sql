-- Exécute ce script dans phpMyAdmin (ou mysql).
-- Exemple:
--   CREATE DATABASE projet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--   USE projet;

CREATE TABLE IF NOT EXISTS utilisateur (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  mail VARCHAR(255) NOT NULL,
  mdp VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_utilisateur_mail (mail)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
