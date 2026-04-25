<?php
require_once "models/Document.php";
require_once "models/Demande.php";
require_once "config/database.php";

class DocumentController {

    private $documentModel;
    private $demandeModel;
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->documentModel = new Document($this->db);
        $this->demandeModel = new Demande($this->db);
    }

    // ========== API JSON ==========
    // Récupérer les documents d'une demande (API AJAX)
    public function getDocuments() {
        if(isset($_GET['demande_id'])) {
            $sql = "SELECT * FROM documents WHERE demande_id = :demande_id ORDER BY uploaded_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":demande_id", $_GET['demande_id']);
            $stmt->execute();
            $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode($documents);
            exit;
        } else {
            echo json_encode([]);
            exit;
        }
    }

    // ========== FORMULAIRES ==========
    // Formulaire d'upload
    public function uploadForm() {
        if(isset($_GET['demande_id'])) {
            $demande_id = $_GET['demande_id'];
            $sql = "SELECT * FROM demandes WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":id", $demande_id);
            $stmt->execute();
            $demande = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($demande) {
                include "views/documents/upload.php";
            } else {
                echo "Demande non trouvée";
            }
        } else {
            header("Location: index.php?action=manage");
        }
    }

    // Upload d'un document
    public function upload() {
        if(!isset($_POST['demande_id']) || empty($_FILES['fichier']['name'])) {
            // Pour les requêtes AJAX
            if($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Données manquantes']);
                exit;
            }
            header("Location: index.php?action=manage");
            return;
        }

        $demande_id = $_POST['demande_id'];
        
        $target_dir = "uploads/demandes/" . $demande_id . "/";
        if(!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $extension = strtolower(pathinfo($_FILES["fichier"]["name"], PATHINFO_EXTENSION));
        $nom_fichier = time() . "_" . uniqid() . "." . $extension;
        $chemin_fichier = $target_dir . $nom_fichier;
        $type_fichier = $_FILES["fichier"]["type"];
        $taille = $_FILES["fichier"]["size"];

        if(move_uploaded_file($_FILES["fichier"]["tmp_name"], $chemin_fichier)) {
            $sql = "INSERT INTO documents (demande_id, nom_fichier, chemin_fichier, type_fichier, taille) 
                    VALUES (:demande_id, :nom_fichier, :chemin_fichier, :type_fichier, :taille)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":demande_id", $demande_id);
            $stmt->bindParam(":nom_fichier", $nom_fichier);
            $stmt->bindParam(":chemin_fichier", $chemin_fichier);
            $stmt->bindParam(":type_fichier", $type_fichier);
            $stmt->bindParam(":taille", $taille);
            
            if($stmt->execute()) {
                // Pour les requêtes AJAX
                if($this->isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Document téléversé avec succès']);
                    exit;
                }
                header("Location: index.php?action=manage");
            } else {
                if($this->isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement']);
                    exit;
                }
                echo "Erreur lors de l'enregistrement en base";
            }
        } else {
            if($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'upload']);
                exit;
            }
            echo "Erreur lors de l'upload du fichier";
        }
    }

    // Formulaire de modification d'un document
    public function editForm() {
        if(isset($_GET['id'])) {
            $sql = "SELECT * FROM documents WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":id", $_GET['id']);
            $stmt->execute();
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($doc) {
                include "views/documents/edit.php";
            } else {
                echo "Document non trouvé";
            }
        } else {
            header("Location: index.php?action=manage");
        }
    }

    // Mettre à jour le nom du document
    public function update() {
        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id']) && isset($_POST['nom_fichier'])) {
            
            // Récupérer l'extension actuelle du fichier
            $sql = "SELECT nom_fichier FROM documents WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":id", $_POST['id']);
            $stmt->execute();
            $old_doc = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($old_doc) {
                // Conserver l'extension
                $extension = pathinfo($old_doc['nom_fichier'], PATHINFO_EXTENSION);
                $nouveau_nom = $_POST['nom_fichier'] . '.' . $extension;
                
                // Mettre à jour
                $sql = "UPDATE documents SET nom_fichier = :nom_fichier WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(":nom_fichier", $nouveau_nom);
                $stmt->bindParam(":id", $_POST['id']);
                
                if($stmt->execute()) {
                    header("Location: index.php?action=manage&success=1");
                } else {
                    header("Location: index.php?action=edit_document&id=".$_POST['id']."&error=Erreur lors de la modification");
                }
            } else {
                header("Location: index.php?action=manage&error=Document non trouvé");
            }
        } else {
            header("Location: index.php?action=manage");
        }
    }

    // Remplacer un document
    public function replace() {
        if(isset($_GET['id']) && isset($_FILES['nouveau_fichier']) && $_FILES['nouveau_fichier']['error'] == 0) {
            $id = $_GET['id'];
            
            $sql = "SELECT * FROM documents WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $old_doc = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($old_doc) {
                if(file_exists($old_doc['chemin_fichier'])) {
                    unlink($old_doc['chemin_fichier']);
                }
                
                $target_dir = "uploads/demandes/" . $old_doc['demande_id'] . "/";
                $extension = strtolower(pathinfo($_FILES["nouveau_fichier"]["name"], PATHINFO_EXTENSION));
                $nom_fichier = time() . "_" . uniqid() . "." . $extension;
                $chemin_fichier = $target_dir . $nom_fichier;
                
                if(move_uploaded_file($_FILES["nouveau_fichier"]["tmp_name"], $chemin_fichier)) {
                    $sql = "UPDATE documents SET nom_fichier = :nom_fichier, chemin_fichier = :chemin_fichier, 
                            type_fichier = :type_fichier, taille = :taille, uploaded_at = NOW() WHERE id = :id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(":nom_fichier", $nom_fichier);
                    $stmt->bindParam(":chemin_fichier", $chemin_fichier);
                    $stmt->bindParam(":type_fichier", $_FILES["nouveau_fichier"]["type"]);
                    $stmt->bindParam(":taille", $_FILES["nouveau_fichier"]["size"]);
                    $stmt->bindParam(":id", $id);
                    
                    if($stmt->execute()) {
                        header("Location: index.php?action=manage");
                    } else {
                        echo "Erreur lors de la mise à jour";
                    }
                } else {
                    echo "Erreur lors de l'upload";
                }
            }
        } else {
            header("Location: index.php?action=manage");
        }
    }

    // Supprimer un document
    public function delete() {
        if(isset($_GET['id'])) {
            $sql = "SELECT chemin_fichier FROM documents WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":id", $_GET['id']);
            $stmt->execute();
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($doc && file_exists($doc['chemin_fichier'])) {
                unlink($doc['chemin_fichier']);
            }
            
            $sql = "DELETE FROM documents WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":id", $_GET['id']);
            
            if($stmt->execute()) {
                header("Location: index.php?action=manage");
            } else {
                echo "Erreur lors de la suppression";
            }
        } else {
            header("Location: index.php?action=manage");
        }
    }

    // Télécharger un document
    public function download() {
        if(isset($_GET['id'])) {
            $sql = "SELECT * FROM documents WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":id", $_GET['id']);
            $stmt->execute();
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($doc && file_exists($doc['chemin_fichier'])) {
                header('Content-Type: ' . $doc['type_fichier']);
                header('Content-Disposition: attachment; filename="' . $doc['nom_fichier'] . '"');
                header('Content-Length: ' . $doc['taille']);
                readfile($doc['chemin_fichier']);
                exit;
            } else {
                echo "Fichier non trouvé";
            }
        }
    }

    // ========== METHODE UTILITAIRE ==========
    // Vérifier si la requête est AJAX
    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}
?>