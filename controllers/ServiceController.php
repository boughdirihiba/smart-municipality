<?php
require_once "models/Service.php";
require_once "config/database.php";

class ServiceController {

    private $model;
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->model = new Service($this->db);
    }

    // Lister les services (BackOffice) avec recherche
    public function list() {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        if(!empty($search)) {
            $sql = "SELECT * FROM services WHERE nom LIKE :search ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            $searchTerm = "%$search%";
            $stmt->bindParam(":search", $searchTerm);
        } else {
            $sql = "SELECT * FROM services ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
        }
        
        $stmt->execute();
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        include "views/services/list.php";
    }

    // Afficher le formulaire d'ajout
    public function createForm() {
        include "views/services/create.php";
    }

    // Ajouter un service (sans statut actif/inactif)
    public function store() {
        if(empty($_POST['nom']) || empty($_POST['description'])){
            header("Location: index.php?action=create_service&error=Tous les champs sont obligatoires");
            exit();
        }

        $icone = !empty($_POST['icone']) ? $_POST['icone'] : 'fas fa-folder-open';

        $sql = "INSERT INTO services (nom, description, icone, date_creation) 
                VALUES (:nom, :description, :icone, NOW())";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(":nom", $_POST['nom']);
        $stmt->bindParam(":description", $_POST['description']);
        $stmt->bindParam(":icone", $icone);
        
        if($stmt->execute()){
            header("Location: index.php?action=list_services&success=1");
            exit();
        } else {
            $error = $stmt->errorInfo();
            header("Location: index.php?action=create_service&error=Erreur lors de l'ajout: " . $error[2]);
            exit();
        }
    }

    // Récupérer un service par son ID
    public function getServiceById($id) {
        $sql = "SELECT * FROM services WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Afficher le formulaire de modification
    public function editForm() {
        if(isset($_GET['id'])) {
            $id = $_GET['id'];
            $service = $this->getServiceById($id);
            
            if($service) {
                include "views/services/edit.php";
            } else {
                header("Location: index.php?action=list_services&error=Service non trouvé");
                exit();
            }
        } else {
            header("Location: index.php?action=list_services");
            exit();
        }
    }

    // Mettre à jour un service
    public function update() {
        if(empty($_POST['id']) || empty($_POST['nom']) || empty($_POST['description'])){
            header("Location: index.php?action=edit_service&id=".$_POST['id']."&error=Tous les champs sont obligatoires");
            exit();
        }

        $sql = "UPDATE services 
                SET nom = :nom, 
                    description = :description, 
                    icone = :icone 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(":id", $_POST['id']);
        $stmt->bindParam(":nom", $_POST['nom']);
        $stmt->bindParam(":description", $_POST['description']);
        $stmt->bindParam(":icone", $_POST['icone']);
        
        if($stmt->execute()){
            header("Location: index.php?action=list_services&success=1");
            exit();
        } else {
            header("Location: index.php?action=edit_service&id=".$_POST['id']."&error=Erreur lors de la modification");
            exit();
        }
    }

    // Supprimer un service
    public function delete() {
        if(isset($_GET['id'])) {
            $sql = "DELETE FROM services WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":id", $_GET['id']);
            
            if($stmt->execute()){
                header("Location: index.php?action=list_services&deleted=1");
                exit();
            } else {
                header("Location: index.php?action=list_services&error=Erreur lors de la suppression");
                exit();
            }
        } else {
            header("Location: index.php?action=list_services");
            exit();
        }
    }

    // Récupérer TOUS les services pour le Front Office (plus de filtre actif)
    public function getServicesFront() {
        $sql = "SELECT * FROM services ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>