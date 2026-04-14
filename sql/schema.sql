CREATE DATABASE smart_municipality CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE smart_municipality;

CREATE TABLE localisations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adresse VARCHAR(255) NOT NULL,
    quartier VARCHAR(120) NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE signalements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) NULL,
    categorie VARCHAR(100) NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    statut ENUM('en_attente', 'en_cours', 'resolu', 'rejete') DEFAULT 'en_attente',
    date_signalement DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INT NULL,
    localisation_id INT NULL,
    CONSTRAINT fk_signalements_localisations
        FOREIGN KEY (localisation_id) REFERENCES localisations(id)
        ON DELETE SET NULL
);

CREATE TABLE historique_positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    signalement_id INT NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    source ENUM('citoyen', 'admin', 'systeme') NOT NULL DEFAULT 'systeme',
    commentaire VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historique_signalement
        FOREIGN KEY (signalement_id) REFERENCES signalements(id)
        ON DELETE CASCADE
);

CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    mot_de_passe VARCHAR(255),
    role ENUM('citoyen', 'admin') DEFAULT 'citoyen'
);

INSERT INTO utilisateurs (nom, email, mot_de_passe, role)
VALUES ('Admin Demo', 'admin@demo.tn', '$2y$10$abcdefghijklmnopqrstuv', 'admin');
