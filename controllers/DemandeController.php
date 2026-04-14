<?php
require_once "models/Demande.php";
require_once "config/database.php";

class DemandeController {

    private $model;

    public function __construct() {
        $db = (new Database())->connect();
        $this->model = new Demande($db);
    }

    public function manage() {
        $demandes = $this->model->read();
        include "views/demandes/manage.php";
    }

    public function create() {
        include "views/demandes/create.php";
    }

    public function store() {
        if(
            empty($_POST['id']) ||
            empty($_POST['nom']) ||
            empty($_POST['type_service']) ||
            empty($_POST['documents']) ||
            empty($_POST['date_creation'])
        ){
            echo "Tous les champs sont obligatoires !";
            return;
        }

        if(!is_numeric($_POST['id'])){
            echo "ID doit être numérique !";
            return;
        }

        $this->model->id = $_POST['id'];
        $this->model->nom = $_POST['nom'];
        $this->model->type_service = $_POST['type_service'];
        $this->model->documents = $_POST['documents'];
        $this->model->date_creation = $_POST['date_creation'];

        if($this->model->create()){
            header("Location: index.php?action=manage");
        } else {
            echo "Erreur insertion";
        }
    }

    public function edit() {
        if(isset($_GET['id'])) {
            $id = $_GET['id'];
            $demande = $this->model->getById($id);
            if($demande) {
                include "views/demandes/edit.php";
            } else {
                echo "Demande non trouvée";
            }
        } else {
            header("Location: index.php?action=manage");
        }
    }

    public function update() {
        if(
            empty($_POST['id']) ||
            empty($_POST['nom']) ||
            empty($_POST['type_service']) ||
            empty($_POST['documents']) ||
            empty($_POST['date_creation'])
        ){
            echo "Tous les champs sont obligatoires !";
            return;
        }

        $this->model->id = $_POST['id'];
        $this->model->nom = $_POST['nom'];
        $this->model->type_service = $_POST['type_service'];
        $this->model->documents = $_POST['documents'];
        $this->model->date_creation = $_POST['date_creation'];

        if($this->model->update()){
            header("Location: index.php?action=manage");
        } else {
            echo "Erreur lors de la modification";
        }
    }

    public function delete() {
        if(isset($_GET['id'])) {
            $this->model->id = $_GET['id'];
            if($this->model->delete()){
                header("Location: index.php?action=manage");
            } else {
                echo "Erreur lors de la suppression";
            }
        } else {
            header("Location: index.php?action=manage");
        }
    }

    // DASHBOARD AVEC STATISTIQUES
    public function dashboard() {
        // Récupérer toutes les statistiques
        $total_demandes = $this->model->getTotalDemandes();
        $services_stats = $this->model->getDemandesByService();
        $demandes_mois = $this->model->getDemandesByMonth();
        $top_services = $this->model->getTopServices();
        $last_demandes = $this->model->getLastDemandes(5);
        
        include "views/demandes/dashboard.php";
    }
}
?>