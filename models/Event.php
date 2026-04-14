<?php
class Event {
    private $conn;
    private $table_name = "evenements";

    public $id;
    public $titre;
    public $description;
    public $lieu;
    public $date_evenement;
    public $heure;
    public $categorie;
    public $image_url;

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE - Ajouter un événement
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET titre=:titre, description=:description, lieu=:lieu,
                      date_evenement=:date_evenement, heure=:heure,
                      categorie=:categorie, image_url=:image_url";

        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->lieu = htmlspecialchars(strip_tags($this->lieu));
        $this->categorie = htmlspecialchars(strip_tags($this->categorie));

        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":lieu", $this->lieu);
        $stmt->bindParam(":date_evenement", $this->date_evenement);
        $stmt->bindParam(":heure", $this->heure);
        $stmt->bindParam(":categorie", $this->categorie);
        $stmt->bindParam(":image_url", $this->image_url);

        return $stmt->execute();
    }

    // READ - Lire tous les événements
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY date_evenement ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // READ - Lire un événement par ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->titre = $row['titre'];
            $this->description = $row['description'];
            $this->lieu = $row['lieu'];
            $this->date_evenement = $row['date_evenement'];
            $this->heure = $row['heure'];
            $this->categorie = $row['categorie'];
            $this->image_url = $row['image_url'];
            return true;
        }
        return false;
    }

    // UPDATE - Modifier un événement
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET titre = :titre, description = :description, lieu = :lieu,
                      date_evenement = :date_evenement, heure = :heure,
                      categorie = :categorie, image_url = :image_url
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->lieu = htmlspecialchars(strip_tags($this->lieu));
        $this->categorie = htmlspecialchars(strip_tags($this->categorie));

        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":lieu", $this->lieu);
        $stmt->bindParam(":date_evenement", $this->date_evenement);
        $stmt->bindParam(":heure", $this->heure);
        $stmt->bindParam(":categorie", $this->categorie);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // DELETE - Supprimer un événement
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }

    // Filtre par catégorie
    public function filterByCategory($categorie) {
        if($categorie == 'all') {
            return $this->readAll();
        }
        $query = "SELECT * FROM " . $this->table_name . " WHERE categorie = :categorie ORDER BY date_evenement ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":categorie", $categorie);
        $stmt->execute();
        return $stmt;
    }
}
?>