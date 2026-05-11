DROP DATABASE IF EXISTS smart_municipality;
CREATE DATABASE IF NOT EXISTS smart_municipality DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE smart_municipality;

CREATE TABLE utilisateurs (
  id int(11) NOT NULL AUTO_INCREMENT,
  nom varchar(100) NOT NULL,
  prenom varchar(100) NOT NULL,
  email varchar(255) NOT NULL,
  mot_de_passe varchar(255) NOT NULL,
  avatar varchar(255) DEFAULT 'default-avatar.png',
  telephone varchar(20) DEFAULT NULL,
  adresse varchar(255) DEFAULT NULL,
  role enum('citoyen','admin') DEFAULT 'citoyen',
  statut enum('actif','inactif','banni') DEFAULT 'actif',
  created_at datetime DEFAULT current_timestamp(),
  updated_at datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE categories (
  id int(11) NOT NULL AUTO_INCREMENT,
  nom varchar(100) NOT NULL,
  description text DEFAULT NULL,
  icone varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE categorie_evenement (
  id int(11) NOT NULL AUTO_INCREMENT,
  nom varchar(100) NOT NULL,
  description text DEFAULT NULL,
  image_url varchar(500) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE services (
  id int(11) NOT NULL AUTO_INCREMENT,
  nom varchar(100) NOT NULL,
  description text DEFAULT NULL,
  icone varchar(50) DEFAULT 'fas fa-folder-open',
  actif tinyint(4) DEFAULT 1,
  date_creation datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE services_en_ligne (
  id int(11) NOT NULL AUTO_INCREMENT,
  nom varchar(100) NOT NULL,
  description text DEFAULT NULL,
  icone varchar(255) DEFAULT NULL,
  documents_requis text DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE evenements (
  id int(11) NOT NULL AUTO_INCREMENT,
  titre varchar(255) NOT NULL,
  description text DEFAULT NULL,
  max_participants int(11) DEFAULT 50,
  lieu varchar(255) NOT NULL,
  date_evenement date NOT NULL,
  heure varchar(10) DEFAULT NULL,
  categorie_id int(11) NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY fk_evenements_categorie (categorie_id),
  CONSTRAINT fk_evenements_categorie
    FOREIGN KEY (categorie_id)
    REFERENCES categorie_evenement (id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE participations (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  event_id int(11) NOT NULL,
  date_participation datetime NOT NULL,
  statut enum('inscrit','present','absent') DEFAULT 'inscrit',
  statut_validation enum('en_attente','valide','refuse') DEFAULT 'en_attente',
  date_validation datetime DEFAULT NULL,
  commentaire_refus text DEFAULT NULL,
  nombre_participants int(11) DEFAULT 1,
  PRIMARY KEY (id),
  KEY event_id (event_id),
  KEY user_id (user_id),
  CONSTRAINT fk_participations_evenement
    FOREIGN KEY (event_id)
    REFERENCES evenements (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_participations_user
    FOREIGN KEY (user_id)
    REFERENCES utilisateurs (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE demandes (
  id int(11) NOT NULL AUTO_INCREMENT,
  nom varchar(100) DEFAULT NULL,
  type_service varchar(100) DEFAULT NULL,
  documents varchar(255) DEFAULT NULL,
  date_creation date DEFAULT NULL,
  statut_admin varchar(30) DEFAULT 'En attente',
  user_id int(11) DEFAULT NULL,
  service_id int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY fk_demandes_user (user_id),
  KEY fk_demandes_service (service_id),
  CONSTRAINT fk_demandes_service
    FOREIGN KEY (service_id)
    REFERENCES services (id)
    ON DELETE SET NULL,
  CONSTRAINT fk_demandes_user
    FOREIGN KEY (user_id)
    REFERENCES utilisateurs (id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE documents (
  id int(11) NOT NULL AUTO_INCREMENT,
  demande_id int(11) NOT NULL,
  nom_fichier varchar(255) NOT NULL,
  chemin_fichier varchar(500) NOT NULL,
  type_fichier varchar(100) DEFAULT NULL,
  taille int(11) DEFAULT NULL,
  uploaded_at datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY demande_id (demande_id),
  CONSTRAINT documents_ibfk_1
    FOREIGN KEY (demande_id)
    REFERENCES demandes (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE notifications (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  message text DEFAULT NULL,
  statut varchar(20) DEFAULT 'non_lu',
  date_creation datetime DEFAULT current_timestamp(),
  document_id int(11) DEFAULT NULL,
  demande_id int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY document_id (document_id),
  KEY fk_notifications_user (user_id),
  CONSTRAINT fk_notifications_user
    FOREIGN KEY (user_id)
    REFERENCES utilisateurs (id)
    ON DELETE CASCADE,
  CONSTRAINT notifications_ibfk_1
    FOREIGN KEY (document_id)
    REFERENCES documents (id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE ratings (
  id int(11) NOT NULL AUTO_INCREMENT,
  service_id int(11) NOT NULL,
  visitor_id varchar(100) NOT NULL,
  rating int(11) DEFAULT NULL CHECK (rating between 1 and 5),
  comment text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY unique_rating (service_id,visitor_id),
  KEY idx_service_id (service_id),
  KEY idx_visitor_id (visitor_id),
  CONSTRAINT fk_ratings_service
    FOREIGN KEY (service_id)
    REFERENCES services (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE localisations (
  id int(11) NOT NULL AUTO_INCREMENT,
  adresse varchar(255) NOT NULL,
  quartier varchar(120) DEFAULT NULL,
  latitude decimal(10,8) NOT NULL,
  longitude decimal(11,8) NOT NULL,
  created_at datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE signalements (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) DEFAULT NULL,
  localisation_id int(11) DEFAULT NULL,
  titre varchar(255) NOT NULL,
  description text NOT NULL,
  image varchar(255) DEFAULT NULL,
  categorie varchar(100) NOT NULL,
  latitude decimal(10,8) NOT NULL,
  longitude decimal(11,8) NOT NULL,
  statut enum('en_attente','en_cours','resolu','rejete') DEFAULT 'en_attente',
  progression tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  date_signalement datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY fk_signalements_user (user_id),
  KEY fk_signalements_localisation (localisation_id),
  CONSTRAINT fk_signalements_localisation
    FOREIGN KEY (localisation_id)
    REFERENCES localisations (id)
    ON DELETE SET NULL,
  CONSTRAINT fk_signalements_user
    FOREIGN KEY (user_id)
    REFERENCES utilisateurs (id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE interventions (
  id int(11) NOT NULL AUTO_INCREMENT,
  titre varchar(255) NOT NULL,
  description text NOT NULL,
  tasks_json longtext DEFAULT NULL,
  type enum('route','eclairage','eau','transport','ordures','autre') NOT NULL,
  latitude decimal(10,8) NOT NULL,
  longitude decimal(11,8) NOT NULL,
  statut enum('planifiee','en_cours','terminee','annulee') NOT NULL DEFAULT 'planifiee',
  progression tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  date_intervention date DEFAULT NULL,
  signalement_id int(11) DEFAULT NULL,
  created_at datetime DEFAULT current_timestamp(),
  updated_at datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  KEY fk_intervention_signalement (signalement_id),
  CONSTRAINT fk_intervention_signalement
    FOREIGN KEY (signalement_id)
    REFERENCES signalements (id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE equipes (
  id int(11) NOT NULL AUTO_INCREMENT,
  nom varchar(100) NOT NULL,
  description text DEFAULT NULL,
  type_intervention varchar(50) NOT NULL,
  nombre_agents int(11) DEFAULT 1,
  statut enum('disponible','en_mission','repos','inactif') DEFAULT 'disponible',
  created_at datetime DEFAULT current_timestamp(),
  updated_at datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE agents_equipe (
  id int(11) NOT NULL AUTO_INCREMENT,
  equipe_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  role varchar(50) DEFAULT 'agent',
  active tinyint(1) DEFAULT 1,
  created_at datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY fk_agents_equipe (equipe_id),
  KEY fk_agents_user (user_id),
  CONSTRAINT fk_agents_equipe
    FOREIGN KEY (equipe_id)
    REFERENCES equipes (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_agents_user
    FOREIGN KEY (user_id)
    REFERENCES utilisateurs (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE couts_intervention (
  id int(11) NOT NULL AUTO_INCREMENT,
  intervention_id int(11) NOT NULL,
  type_intervention varchar(50) NOT NULL,
  cout_base decimal(10,2) DEFAULT 0.00,
  cout_materiel decimal(10,2) DEFAULT 0.00,
  cout_main_oeuvre decimal(10,2) DEFAULT 0.00,
  cout_deplacement decimal(10,2) DEFAULT 0.00,
  cout_total decimal(10,2) DEFAULT 0.00,
  estimations_ml varchar(255) DEFAULT NULL,
  facteurs_ajustement longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(facteurs_ajustement)),
  historique_similar longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(historique_similar)),
  created_at datetime DEFAULT current_timestamp(),
  updated_at datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY unique_cout_intervention (intervention_id),
  CONSTRAINT fk_cout_intervention
    FOREIGN KEY (intervention_id)
    REFERENCES interventions (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE budgets (
  id int(11) NOT NULL AUTO_INCREMENT,
  titre varchar(255) NOT NULL,
  annee int(11) NOT NULL,
  categorie varchar(50) NOT NULL,
  zone varchar(100) DEFAULT NULL,
  montant_alloue decimal(12,2) NOT NULL,
  montant_depense decimal(12,2) DEFAULT 0.00,
  montant_reserve decimal(12,2) DEFAULT 0.00,
  statut enum('planifie','en_cours','termine','depassement') DEFAULT 'planifie',
  description text DEFAULT NULL,
  responsable_id int(11) DEFAULT NULL,
  created_at datetime DEFAULT current_timestamp(),
  updated_at datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY unique_budget_period (annee,categorie,zone),
  KEY fk_budget_responsable (responsable_id),
  CONSTRAINT fk_budget_responsable
    FOREIGN KEY (responsable_id)
    REFERENCES utilisateurs (id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE budget_forecasts (
  id int(11) NOT NULL AUTO_INCREMENT,
  budget_id int(11) NOT NULL,
  mois int(11) NOT NULL,
  depenses_estimees decimal(12,2) NOT NULL,
  depenses_reelles decimal(12,2) DEFAULT 0.00,
  precision_score decimal(5,2) DEFAULT 0.00,
  facteurs longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(facteurs)),
  created_at datetime DEFAULT current_timestamp(),
  updated_at datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY unique_forecast_period (budget_id,mois),
  CONSTRAINT fk_forecast_budget
    FOREIGN KEY (budget_id)
    REFERENCES budgets (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE budget_transactions (
  id int(11) NOT NULL AUTO_INCREMENT,
  budget_id int(11) NOT NULL,
  intervention_id int(11) DEFAULT NULL,
  montant decimal(12,2) NOT NULL,
  type enum('debit','credit') DEFAULT 'debit',
  description varchar(255) DEFAULT NULL,
  created_at datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY fk_transaction_budget (budget_id),
  KEY fk_transaction_intervention (intervention_id),
  CONSTRAINT fk_transaction_budget
    FOREIGN KEY (budget_id)
    REFERENCES budgets (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_transaction_intervention
    FOREIGN KEY (intervention_id)
    REFERENCES interventions (id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE posts (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  content text NOT NULL,
  image varchar(255) DEFAULT NULL,
  video varchar(255) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY fk_posts_user (user_id),
  CONSTRAINT fk_posts_user
    FOREIGN KEY (user_id)
    REFERENCES utilisateurs (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE comments (
  id int(11) NOT NULL AUTO_INCREMENT,
  post_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  content text NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY fk_comments_post (post_id),
  KEY fk_comments_user (user_id),
  CONSTRAINT fk_comments_post
    FOREIGN KEY (post_id)
    REFERENCES posts (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_comments_user
    FOREIGN KEY (user_id)
    REFERENCES utilisateurs (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE reactions (
  id int(11) NOT NULL AUTO_INCREMENT,
  post_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  type enum('like','love','haha','wow','sad','angry') NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY unique_reaction (post_id,user_id),
  KEY fk_reactions_user (user_id),
  CONSTRAINT fk_reactions_post
    FOREIGN KEY (post_id)
    REFERENCES posts (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_reactions_user
    FOREIGN KEY (user_id)
    REFERENCES utilisateurs (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE alertes (
  id int(11) NOT NULL AUTO_INCREMENT,
  signalement_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  message text NOT NULL,
  lu tinyint(1) DEFAULT 0,
  created_at datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY fk_alertes_signalement (signalement_id),
  KEY fk_alertes_user (user_id),
  CONSTRAINT fk_alertes_signalement
    FOREIGN KEY (signalement_id)
    REFERENCES signalements (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_alertes_user
    FOREIGN KEY (user_id)
    REFERENCES utilisateurs (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE rendez_vous (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  categorie_id int(11) NOT NULL,
  date_rdv date NOT NULL,
  heure time NOT NULL,
  statut enum('en_attente','confirme','annule','termine') DEFAULT 'en_attente',
  created_at datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY fk_rdv_user (user_id),
  KEY fk_rdv_categorie (categorie_id),
  CONSTRAINT fk_rdv_categorie
    FOREIGN KEY (categorie_id)
    REFERENCES categories (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_rdv_user
    FOREIGN KEY (user_id)
    REFERENCES utilisateurs (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, avatar, telephone, adresse, role, statut) VALUES
('Admin', 'Systeme', 'admin@smart.local', '$2y$10$adminhashplaceholder', 'default-avatar.png', '70000000', 'Mairie Centrale', 'admin', 'actif'),
('Ali', 'Ben Salah', 'ali.bensalah@smart.local', '$2y$10$userhashplaceholder', 'default-avatar.png', '71000000', 'Avenue Habib Bourguiba', 'citoyen', 'actif'),
('Sara', 'Trabelsi', 'sara.trabelsi@smart.local', '$2y$10$userhashplaceholder2', 'default-avatar.png', '72000000', 'Rue de Marseille', 'citoyen', 'actif');

INSERT INTO categories (nom, description, icone) VALUES
('Etat Civil', 'Actes et papiers officiels', 'fa-id-card'),
('Urbanisme', 'Permis et plans urbains', 'fa-city'),
('Affaires Sociales', 'Demandes et assistance sociale', 'fa-hands-helping');

INSERT INTO categorie_evenement (nom, description, image_url) VALUES
('Culture', 'Evenements culturels et associatifs', 'uploads/categories/culture.jpg'),
('Environnement', 'Actions de nettoyage et sensibilisation', 'uploads/categories/environnement.jpg');

INSERT INTO services (nom, description, icone, actif) VALUES
('Legalisation', 'Legalisation de documents', 'fas fa-stamp', 1),
('Permis de construire', 'Depot et suivi des permis', 'fas fa-building', 1);

INSERT INTO services_en_ligne (nom, description, icone, documents_requis) VALUES
('Extrait de naissance', 'Demande d extrait en ligne', 'fas fa-file-alt', 'CIN, date de naissance'),
('Paiement taxe municipale', 'Paiement des taxes locales', 'fas fa-credit-card', 'Reference fiscale');

INSERT INTO evenements (titre, description, max_participants, lieu, date_evenement, heure, categorie_id) VALUES
('Journee de proprete', 'Campagne locale de nettoyage', 100, 'Place Centrale', '2026-06-01', '09:00', 2),
('Festival local', 'Animation culturelle municipale', 300, 'Maison de Culture', '2026-06-15', '18:00', 1);

INSERT INTO participations (user_id, event_id, date_participation, statut, statut_validation, date_validation, commentaire_refus, nombre_participants) VALUES
(2, 1, '2026-05-11 10:00:00', 'inscrit', 'valide', '2026-05-11 10:05:00', NULL, 2),
(3, 2, '2026-05-11 11:00:00', 'inscrit', 'en_attente', NULL, NULL, 1);

INSERT INTO demandes (nom, type_service, documents, date_creation, statut_admin, user_id, service_id) VALUES
('Demande extrait', 'Extrait de naissance', 'cin.pdf', '2026-05-11', 'En attente', 2, 1),
('Permis maison', 'Permis de construire', 'plan.pdf', '2026-05-11', 'En cours', 3, 2);

INSERT INTO documents (demande_id, nom_fichier, chemin_fichier, type_fichier, taille) VALUES
(1, 'cin.pdf', 'uploads/demandes/cin.pdf', 'application/pdf', 154321),
(2, 'plan.pdf', 'uploads/demandes/plan.pdf', 'application/pdf', 345876);

INSERT INTO notifications (user_id, message, statut, date_creation, document_id, demande_id) VALUES
(2, 'Votre demande est en cours de traitement.', 'non_lu', '2026-05-11 12:00:00', 1, 1),
(3, 'Un document a ete valide.', 'lu', '2026-05-11 12:10:00', 2, 2);

INSERT INTO ratings (service_id, visitor_id, rating, comment) VALUES
(1, 'visitor-ali-001', 5, 'Service rapide et clair.'),
(2, 'visitor-sara-001', 4, 'Bonne experience globale.');

INSERT INTO localisations (adresse, quartier, latitude, longitude) VALUES
('Avenue Habib Bourguiba', 'Tunis Centre', 36.80080000, 10.18000000),
('Rue de Marseille', 'Bab Bhar', 36.79920000, 10.18360000);

INSERT INTO signalements (user_id, localisation_id, titre, description, image, categorie, latitude, longitude, statut, progression, date_signalement) VALUES
(2, 1, 'Nid-de-poule', 'Nid-de-poule dangereux sur la voie principale', NULL, 'route', 36.80080000, 10.18000000, 'en_attente', 10, '2026-05-11 09:00:00'),
(3, 2, 'Lampadaire en panne', 'Panne eclairage depuis 3 jours', NULL, 'eclairage', 36.79920000, 10.18360000, 'en_cours', 45, '2026-05-11 09:30:00');

INSERT INTO interventions (titre, description, tasks_json, type, latitude, longitude, statut, progression, date_intervention, signalement_id) VALUES
('Reparation chausssee', 'Intervention sur voirie', '["balisage","rebouchage"]', 'route', 36.80080000, 10.18000000, 'planifiee', 20, '2026-05-12', 1),
('Maintenance eclairage', 'Verification et remplacement ampoule', '["diagnostic","remplacement"]', 'eclairage', 36.79920000, 10.18360000, 'en_cours', 60, '2026-05-12', 2);

INSERT INTO equipes (nom, description, type_intervention, nombre_agents, statut) VALUES
('Equipe Voirie A', 'Equipe route et trottoirs', 'route', 5, 'disponible'),
('Equipe Lumiere B', 'Equipe eclairage public', 'eclairage', 4, 'en_mission');

INSERT INTO agents_equipe (equipe_id, user_id, role, active) VALUES
(1, 1, 'chef', 1),
(2, 2, 'agent', 1);

INSERT INTO couts_intervention (intervention_id, type_intervention, cout_base, cout_materiel, cout_main_oeuvre, cout_deplacement, cout_total, estimations_ml, facteurs_ajustement, historique_similar) VALUES
(1, 'route', 500.00, 300.00, 400.00, 80.00, 1280.00, 'modele_v1', '{"meteo":1.05}', '[{"id":10,"cout":1200}]'),
(2, 'eclairage', 250.00, 200.00, 250.00, 50.00, 750.00, 'modele_v1', '{"nuit":1.10}', '[{"id":11,"cout":700}]');

INSERT INTO budgets (titre, annee, categorie, zone, montant_alloue, montant_depense, montant_reserve, statut, description, responsable_id) VALUES
('Budget Voirie', 2026, 'infrastructure', 'Tunis Centre', 150000.00, 12000.00, 10000.00, 'en_cours', 'Budget interventions routes', 1),
('Budget Eclairage', 2026, 'maintenance', 'Bab Bhar', 90000.00, 8000.00, 5000.00, 'en_cours', 'Budget eclairage public', 1);

INSERT INTO budget_forecasts (budget_id, mois, depenses_estimees, depenses_reelles, precision_score, facteurs) VALUES
(1, 5, 18000.00, 12000.00, 92.50, '{"saisonnalite":"moyenne"}'),
(2, 5, 12000.00, 8000.00, 89.20, '{"pannes":"faibles"}');

INSERT INTO budget_transactions (budget_id, intervention_id, montant, type, description) VALUES
(1, 1, 4500.00, 'debit', 'Achat materiaux voirie'),
(2, 2, 2200.00, 'debit', 'Remplacement lampadaires');

INSERT INTO posts (user_id, content, image, video) VALUES
(2, 'Nouvelle campagne citoyenne lancee cette semaine.', NULL, NULL),
(3, 'Merci a la municipalite pour les actions de nettoyage.', NULL, NULL);

INSERT INTO comments (post_id, user_id, content) VALUES
(1, 3, 'Excellente initiative.'),
(2, 2, 'Bravo a toutes les equipes.');

INSERT INTO reactions (post_id, user_id, type) VALUES
(1, 2, 'like'),
(2, 3, 'love');

INSERT INTO alertes (signalement_id, user_id, message, lu) VALUES
(1, 2, 'Votre signalement a ete pris en charge.', 0),
(2, 3, 'Intervention programmee pour votre signalement.', 1);

INSERT INTO rendez_vous (user_id, categorie_id, date_rdv, heure, statut) VALUES
(2, 1, '2026-05-20', '10:00:00', 'confirme'),
(3, 2, '2026-05-21', '14:30:00', 'en_attente');

COMMIT;