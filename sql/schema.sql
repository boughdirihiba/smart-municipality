CREATE DATABASE IF NOT EXISTS smart_municipality CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE smart_municipality;

-- ========================================
-- 1. UTILISATEURS
-- ========================================

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    telephone VARCHAR(20) NULL,
    adresse VARCHAR(255) NULL,
    role ENUM('citoyen', 'admin') DEFAULT 'citoyen',
    statut ENUM('actif', 'inactif', 'banni') DEFAULT 'actif',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- 2. RENDEZ-VOUS
-- ========================================

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT NULL,
    icone VARCHAR(255) NULL
);

CREATE TABLE IF NOT EXISTS rendez_vous (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    categorie_id INT NOT NULL,
    date_rdv DATE NOT NULL,
    heure TIME NOT NULL,
    statut ENUM('en_attente', 'confirme', 'annule', 'termine') DEFAULT 'en_attente',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rdv_user FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_rdv_categorie FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- ========================================
-- 3. EVENEMENTS
-- ========================================

CREATE TABLE IF NOT EXISTS evenements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT NULL,
    lieu VARCHAR(255) NOT NULL,
    date_evenement DATE NOT NULL,
    heure VARCHAR(10) NULL,
    categorie VARCHAR(50) NULL,
    image_url VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS participations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    date_participation DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('inscrit', 'present', 'absent') DEFAULT 'inscrit',
    CONSTRAINT fk_participation_user FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_participation_event FOREIGN KEY (event_id) REFERENCES evenements(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participation (user_id, event_id)
);

-- ========================================
-- 4. BLOG
-- ========================================

CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255) NULL,
    video VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comments_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    type ENUM('like', 'love', 'haha', 'wow', 'sad', 'angry') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reactions_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_reactions_user FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reaction (post_id, user_id)
);

-- ========================================
-- 5. SIGNALEMENTS + CARTE INTELLIGENTE
-- ========================================

CREATE TABLE IF NOT EXISTS localisations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adresse VARCHAR(255) NOT NULL,
    quartier VARCHAR(120) NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS signalements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    localisation_id INT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) NULL,
    categorie VARCHAR(100) NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    statut ENUM('en_attente', 'en_cours', 'resolu', 'rejete') DEFAULT 'en_attente',
    date_signalement DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_signalements_user FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    CONSTRAINT fk_signalements_localisation FOREIGN KEY (localisation_id) REFERENCES localisations(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS historique_positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    signalement_id INT NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    source ENUM('citoyen', 'admin', 'systeme') NOT NULL DEFAULT 'systeme',
    commentaire VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historique_signalement FOREIGN KEY (signalement_id) REFERENCES signalements(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS alertes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    signalement_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    lu TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_alertes_signalement FOREIGN KEY (signalement_id) REFERENCES signalements(id) ON DELETE CASCADE,
    CONSTRAINT fk_alertes_user FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- ========================================
-- 6. SERVICES EN LIGNE
-- ========================================

CREATE TABLE IF NOT EXISTS services_en_ligne (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT NULL,
    icone VARCHAR(255) NULL,
    documents_requis TEXT NULL
);

CREATE TABLE IF NOT EXISTS demandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    statut ENUM('choix_service', 'documents_requis', 'televersement', 'soumission', 'en_traitement', 'accepte', 'refuse') DEFAULT 'choix_service',
    commentaire TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_demandes_user FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_demandes_service FOREIGN KEY (service_id) REFERENCES services_en_ligne(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    demande_id INT NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin_fichier VARCHAR(500) NOT NULL,
    type_fichier VARCHAR(20) NULL,
    taille INT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_documents_demande FOREIGN KEY (demande_id) REFERENCES demandes(id) ON DELETE CASCADE
);

-- ========================================
-- DONNEES DE TEST
-- ========================================

INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, avatar, telephone, adresse, role, statut) VALUES
('Eliza', 'Thorne', 'eliza@email.com', '1234', 'default-avatar.png', '00000000', 'Centre-ville', 'citoyen', 'actif'),
('Super', 'Admin', 'admin@email.com', '1234', 'default-avatar.png', '11111111', 'Mairie Centrale', 'admin', 'actif');

INSERT INTO categories (nom, description, icone) VALUES
('Etat Civil', 'Services lies aux actes de naissance, mariage, deces', 'etat-civil.png'),
('Urbanisme', 'Permis de construire, plans d urbanisme', 'urbanisme.png'),
('Cadastre', 'Consultation et modification des plans cadastraux', 'cadastre.png'),
('Services Sociaux', 'Aide sociale, allocations, accompagnement', 'services-sociaux.png'),
('Services Usagers', 'Eau, electricite, assainissement', 'services-usagers.png');

INSERT INTO rendez_vous (user_id, categorie_id, date_rdv, heure, statut) VALUES
(1, 1, '2026-04-14', '10:00:00', 'en_attente'),
(1, 2, '2026-04-16', '14:00:00', 'confirme'),
(1, 3, '2026-04-20', '09:00:00', 'annule');

INSERT INTO evenements (titre, description, lieu, date_evenement, heure, categorie) VALUES
('Journee portes ouvertes', 'Decouvrez les services de la municipalite', 'Mairie Centrale', '2026-05-01', '09:00', 'Culturel'),
('Campagne de nettoyage', 'Nettoyage collectif du quartier', 'Place Centrale', '2026-05-10', '08:00', 'Environnement');

INSERT INTO localisations (adresse, quartier, latitude, longitude) VALUES
('Avenue de la Republique', 'Secteur 1', 36.80650000, 10.18150000),
('Rue des Pecheurs', 'Secteur 1', 36.80710000, 10.18290000),
('Rue des Pecheurs', 'Secteur 2', 36.80800000, 10.18340000),
('Place Centrale', 'Beni Khalled', 36.65050000, 10.60010000);

INSERT INTO signalements (user_id, localisation_id, titre, description, image, categorie, latitude, longitude, statut) VALUES
(1, 1, 'Nid-de-poule', 'Nid-de-poule dangereux sur la route principale', NULL, 'route', 36.80650000, 10.18150000, 'en_attente'),
(1, 2, 'Panne d eclairage', 'Lampadaire en panne depuis 3 jours', NULL, 'eclairage', 36.80710000, 10.18290000, 'en_cours'),
(1, 4, 'Fuite d eau', 'Fuite d eau majeure sur conduite d egout', NULL, 'eau', 36.65050000, 10.60010000, 'en_attente'),
(2, 1, 'Bac a ordures plein', 'Le bac a ordures deborde depuis ce matin dans la rue principale', NULL, 'ordures', 36.80650000, 10.18150000, 'en_cours'),
(1, 2, 'Feu tricolore bloque', 'Le feu tricolore reste bloque au rouge depuis la nuit derniere', NULL, 'transport', 36.80710000, 10.18290000, 'en_attente'),
(2, 3, 'Trottoir casse', 'Trottoir casse avec risque de chute pour les pietons', NULL, 'route', 36.80800000, 10.18340000, 'en_attente'),
(1, 4, 'Odeur suspecte', 'Odeur suspecte et fuite visible pres de la canalisation', NULL, 'eau', 36.65050000, 10.60010000, 'en_cours'),
(2, 1, 'Poteau endommage', 'Poteau endommage apres un choc vehicule, intervention necessaire', NULL, 'eclairage', 36.80650000, 10.18150000, 'rejete'),
(1, 2, 'Bouche d egout ouverte', 'Bouche d egout ouverte sans protection dans la voie publique', NULL, 'route', 36.80710000, 10.18290000, 'en_attente'),
(2, 3, 'Collecte en retard', 'La collecte des dechets a pris beaucoup de retard dans le secteur', NULL, 'ordures', 36.80800000, 10.18340000, 'en_cours'),
(1, 4, 'Bus en panne', 'Bus immobilise au point d arret avec voyageurs sur le trottoir', NULL, 'transport', 36.65050000, 10.60010000, 'en_attente'),
(2, 1, 'Flaque d eau permanente', 'Flaque d eau permanente apres fuite sur reseau secondaire', NULL, 'eau', 36.80650000, 10.18150000, 'resolu'),
(1, 2, 'Lampadaire clignotant', 'Lampadaire clignotant de facon intermittente en soiree', NULL, 'eclairage', 36.80710000, 10.18290000, 'en_attente'),
(2, 3, 'Chaussée fissuree', 'Chaussée fissuree sur plusieurs metres et circulation difficile', NULL, 'route', 36.80800000, 10.18340000, 'en_cours'),
(1, 4, 'Dechets abandonnés', 'Dechets abandonnés autour du point de collecte apres passage incomplet', NULL, 'ordures', 36.65050000, 10.60010000, 'en_attente'),
(2, 1, 'Passage pieton efface', 'Marquage du passage pieton presque efface devant l ecole', NULL, 'route', 36.80650000, 10.18150000, 'resolu'),
(1, 2, 'Canalisation percee', 'Canalisation percee avec perte d eau importante dans la rue', NULL, 'eau', 36.80710000, 10.18290000, 'en_cours'),
(2, 3, 'Arret de bus sale', 'Abri de bus sale et encombre par des emballages', NULL, 'transport', 36.80800000, 10.18340000, 'en_attente'),
(1, 4, 'Cable expose', 'Cable expose pres du lampadaire apres deterioration du boitier', NULL, 'eclairage', 36.65050000, 10.60010000, 'rejete'),
(2, 1, 'Depose sauvage', 'Depose sauvage de sacs poubelles sur le terrain vague voisin', NULL, 'ordures', 36.80650000, 10.18150000, 'en_attente');

INSERT INTO services_en_ligne (nom, description, icone, documents_requis) VALUES
('Legalisation de documents', 'Authentifier rapidement vos documents officiels', 'legalisation.png', 'Carte d identite (Recto/Verso), Formulaire de demande rempli, Justificatif de domicile'),
('Extrait de naissance', 'Obtenez vos extraits d actes (naissance, mariage, deces)', 'extrait.png', 'Carte d identite, Livret de famille'),
('Paiement taxes', 'Reglez vos impots locaux et taxes foncieres', 'paiement.png', 'Avis d imposition, Carte d identite'),
('Depot de dossier', 'Soumettez vos dossiers d urbanisme, permis ou demandes', 'depot.png', 'Formulaire de demande, Plans, Carte d identite');

-- ========================================
-- 7. SIGNALEMENTS TEST EN TUNISIE
-- ========================================

INSERT INTO localisations (adresse, quartier, latitude, longitude) VALUES
('Avenue Habib Bourguiba', 'Tunis Centre', 36.80080000, 10.18000000),
('Rue de Marseille', 'Bab Bhar', 36.79920000, 10.18360000),
('Avenue Mohamed V', 'Belvedere', 36.82160000, 10.16890000),
('Route de La Goulette', 'Bardo', 36.80970000, 10.14730000),
('Avenue de Carthage', 'La Marsa', 36.87880000, 10.32420000),
('Rue Hedi Chaker', 'Sfax Centre', 34.73900000, 10.76000000),
('Avenue Taieb Mhiri', 'Sousse Centre', 35.82560000, 10.63460000),
('Boulevard 14 Janvier', 'Monastir', 35.77800000, 10.82640000),
('Avenue de l Environnement', 'Nabeul', 36.45690000, 10.73760000),
('Rue Habib Thameur', 'Bizerte Centre', 37.27450000, 9.87390000),
('Avenue Farhat Hached', 'Gabes Ville', 33.88150000, 10.09820000),
('Route de Tunis', 'Kairouan', 35.67800000, 10.10110000),
('Avenue Salah Ben Youssef', 'Kasserine', 35.16760000, 8.83670000),
('Rue Ali Belhouane', 'Jendouba', 36.50100000, 8.77830000),
('Avenue Mongi Slim', 'Sidi Bou Said', 36.86940000, 10.34310000);

INSERT INTO signalements (user_id, localisation_id, titre, description, image, categorie, latitude, longitude, statut) VALUES
(1, 5, 'Nid-de-poule au centre-ville', 'Nid-de-poule profond sur une voie tres frequentee de Tunis Centre', NULL, 'route', 36.80080000, 10.18000000, 'en_attente'),
(2, 6, 'Eclairage public coupe', 'Plusieurs lampadaires sont hors service dans le secteur de Bab Bhar', NULL, 'eclairage', 36.79920000, 10.18360000, 'en_cours'),
(1, 7, 'Fuite d eau sur trottoir', 'Une fuite importante provoque une mare d eau pres du Belvedere', NULL, 'eau', 36.82160000, 10.16890000, 'en_attente'),
(2, 8, 'Depots sauvages', 'Des sacs poubelles sont abandonnes le long de la route du Bardo', NULL, 'ordures', 36.80970000, 10.14730000, 'rejete'),
(1, 9, 'Bus en panne', 'Un bus bloque la circulation a La Marsa pendant l heure de pointe', NULL, 'transport', 36.87880000, 10.32420000, 'en_cours'),
(2, 10, 'Marquage routier efface', 'La signalisation au sol est presque invisible dans le centre de Sfax', NULL, 'route', 34.73900000, 10.76000000, 'en_attente'),
(1, 11, 'Lampadaire clignotant', 'Un lampadaire clignote de facon intermittente sur l avenue principale de Sousse', NULL, 'eclairage', 35.82560000, 10.63460000, 'en_attente'),
(2, 12, 'Fuite d egout', 'Une fuite d egout degage une mauvaise odeur dans le quartier de Monastir', NULL, 'eau', 35.77800000, 10.82640000, 'en_cours'),
(1, 13, 'Tas d ordures', 'Un tas d ordures bloque une partie du trottoir a Nabeul', NULL, 'ordures', 36.45690000, 10.73760000, 'en_attente'),
(2, 14, 'Feu tricolore defectueux', 'Le feu tricolore reste bloque sur rouge dans le centre de Bizerte', NULL, 'transport', 37.27450000, 9.87390000, 'en_cours'),
(1, 15, 'Trottoir casse', 'Le trottoir est casse et dangereux pour les passants a Gabes Ville', NULL, 'route', 33.88150000, 10.09820000, 'en_attente'),
(2, 16, 'Caniveau bouche', 'Le caniveau est bouche et provoque un risque d inondation a Kairouan', NULL, 'eau', 35.67800000, 10.10110000, 'en_cours'),
(1, 17, 'Poteau penche', 'Un poteau penche menace de tomber sur le passage pieton a Kasserine', NULL, 'eclairage', 35.16760000, 8.83670000, 'rejete'),
(2, 18, 'Collecte en retard', 'La collecte des dechets a pris du retard dans plusieurs rues de Jendouba', NULL, 'ordures', 36.50100000, 8.77830000, 'en_attente'),
(1, 19, 'Panne de bus', 'Un bus de ligne est tombe en panne pres de Sidi Bou Said', NULL, 'transport', 36.86940000, 10.34310000, 'en_attente');
