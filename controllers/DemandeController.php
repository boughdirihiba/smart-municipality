<?php
require_once "models/Demande.php";
require_once "config/database.php";

class DemandeController {

    private $model;
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->model = new Demande($this->db);
    }

    public function manage() {
        require_once "controllers/ServiceController.php";
        $serviceController = new ServiceController();
        $allServices = $serviceController->getServicesFront();
        
        // Récupérer les demandes avec le nom du service
        $sql = "SELECT d.*, s.nom as service_nom, s.icone as service_icone 
                FROM demandes d
                LEFT JOIN services s ON d.service_id = s.id
                ORDER BY d.date_creation DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($demandes as &$demande) {
            $sqlDocs = "SELECT * FROM documents WHERE demande_id = :demande_id ORDER BY uploaded_at DESC";
            $stmtDocs = $this->db->prepare($sqlDocs);
            $stmtDocs->bindParam(":demande_id", $demande['id']);
            $stmtDocs->execute();
            $demande['fichiers'] = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);
            $demande['fichiers_count'] = count($demande['fichiers']);
        }
        
        include "views/demandes/manage.php";
    }

    public function create() {
        $service_prefill = isset($_GET['service']) ? $_GET['service'] : '';
        $errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : [];
        $old_input = isset($_SESSION['old_input']) ? $_SESSION['old_input'] : [];
        unset($_SESSION['errors']);
        unset($_SESSION['old_input']);
        include "views/demandes/create.php";
    }

    public function store() {
        $errors = [];
        
        if(empty($_POST['id'])) {
            $errors[] = "L'ID est obligatoire";
        } elseif(!is_numeric($_POST['id']) || $_POST['id'] <= 0) {
            $errors[] = "L'ID doit être un nombre positif";
        }
        
        if(empty($_POST['nom'])) {
            $errors[] = "Le nom est obligatoire";
        } elseif(strlen($_POST['nom']) > 50) {
            $errors[] = "Le nom ne doit pas dépasser 50 caractères";
        }
        
        if(empty($_POST['type_service'])) {
            $errors[] = "Le type de service est obligatoire";
        }
        
        if(empty($_POST['documents'])) {
            $errors[] = "La liste des documents est obligatoire";
        } elseif(strlen($_POST['documents']) > 40) {
            $errors[] = "Les documents ne doivent pas dépasser 40 caractères";
        }
        
        if(!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            header("Location: index.php?action=create");
            exit();
        }
        
        // Récupérer le service_id à partir du nom du service
        $service_id = null;
        $sqlService = "SELECT id FROM services WHERE nom = :nom";
        $stmtService = $this->db->prepare($sqlService);
        $stmtService->bindParam(":nom", $_POST['type_service']);
        $stmtService->execute();
        $service = $stmtService->fetch(PDO::FETCH_ASSOC);
        if($service) {
            $service_id = $service['id'];
        }
        
        $sql = "INSERT INTO demandes (id, nom, type_service, documents, date_creation, service_id, user_id)
                VALUES (:id, :nom, :type_service, :documents, :date_creation, :service_id, 1)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(":id", $_POST['id']);
        $stmt->bindParam(":nom", $_POST['nom']);
        $stmt->bindParam(":type_service", $_POST['type_service']);
        $stmt->bindParam(":documents", $_POST['documents']);
        $stmt->bindParam(":date_creation", $_POST['date_creation']);
        $stmt->bindParam(":service_id", $service_id);
        
        if($stmt->execute()){
            header("Location: index.php?action=manage&success=1");
            exit();
        } else {
            $_SESSION['errors'] = ["Erreur lors de l'insertion"];
            header("Location: index.php?action=create");
            exit();
        }
    }

    public function edit() {
        if(isset($_GET['id'])) {
            $id = $_GET['id'];
            $sql = "SELECT d.*, s.nom as service_nom 
                    FROM demandes d
                    LEFT JOIN services s ON d.service_id = s.id
                    WHERE d.id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $demande = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($demande) {
                include "views/demandes/edit.php";
            } else {
                echo "Demande non trouvée";
            }
        } else {
            header("Location: index.php?action=manage");
            exit();
        }
    }

    public function update() {
        if(empty($_POST['id']) || empty($_POST['nom']) || empty($_POST['type_service']) || empty($_POST['documents'])) {
            header("Location: index.php?action=edit&id=".$_POST['id']."&error=Tous les champs sont obligatoires");
            exit();
        }

        // Récupérer le service_id
        $service_id = null;
        $sqlService = "SELECT id FROM services WHERE nom = :nom";
        $stmtService = $this->db->prepare($sqlService);
        $stmtService->bindParam(":nom", $_POST['type_service']);
        $stmtService->execute();
        $service = $stmtService->fetch(PDO::FETCH_ASSOC);
        if($service) {
            $service_id = $service['id'];
        }

        $sql = "UPDATE demandes 
                SET nom = :nom, 
                    type_service = :type_service, 
                    documents = :documents, 
                    date_creation = :date_creation,
                    service_id = :service_id
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(":id", $_POST['id']);
        $stmt->bindParam(":nom", $_POST['nom']);
        $stmt->bindParam(":type_service", $_POST['type_service']);
        $stmt->bindParam(":documents", $_POST['documents']);
        $stmt->bindParam(":date_creation", $_POST['date_creation']);
        $stmt->bindParam(":service_id", $service_id);
        
        if($stmt->execute()){
            header("Location: index.php?action=manage&success=1");
            exit();
        } else {
            header("Location: index.php?action=edit&id=".$_POST['id']."&error=Erreur lors de la modification");
            exit();
        }
    }

    public function delete() {
        if(isset($_GET['id'])) {
            $sql = "DELETE FROM demandes WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":id", $_GET['id']);
            
            if($stmt->execute()){
                header("Location: index.php?action=manage&deleted=1");
                exit();
            } else {
                echo "Erreur lors de la suppression";
            }
        } else {
            header("Location: index.php?action=manage");
            exit();
        }
    }

    public function dashboard() {
        // Utiliser service_id pour les statistiques
        $sql = "SELECT COUNT(*) as total FROM demandes";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $total_demandes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $sql = "SELECT s.nom as type_service, COUNT(d.id) as nombre 
                FROM demandes d
                JOIN services s ON d.service_id = s.id
                GROUP BY d.service_id 
                ORDER BY nombre DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $services_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sql = "SELECT DATE_FORMAT(date_creation, '%Y-%m') as mois, COUNT(*) as nombre 
                FROM demandes 
                WHERE date_creation >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(date_creation, '%Y-%m') 
                ORDER BY mois DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $demandes_mois = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sql = "SELECT s.nom as type_service, COUNT(d.id) as nombre 
                FROM demandes d
                JOIN services s ON d.service_id = s.id
                GROUP BY d.service_id 
                ORDER BY nombre DESC 
                LIMIT 3";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $top_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sql = "SELECT d.*, s.nom as service_nom 
                FROM demandes d
                LEFT JOIN services s ON d.service_id = s.id
                ORDER BY d.date_creation DESC LIMIT 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $last_demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        include "views/demandes/dashboard.php";
    }
}
?>