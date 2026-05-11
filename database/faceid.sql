-- Face ID storage (face-api.js descriptor)
-- Run this in your MySQL database (XAMPP).
-- DB name in config/Database.php: smart_municipality

CREATE TABLE IF NOT EXISTS utilisateur_face_id (
  user_id INT NOT NULL,
  descriptor_json MEDIUMTEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  CONSTRAINT fk_utilisateur_face_id_user
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
