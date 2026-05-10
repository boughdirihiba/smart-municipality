<?php
class Document {
    private $db;
    private $id;
    private $demande_id;
    private $nom_fichier;
    private $chemin_fichier;
    private $type_fichier;
    private $taille;
    private $uploaded_at;

    public function __construct($db = null) {
        $this->db = $db;
    }

    public function getId() { return $this->id; }
    public function getDemandeId() { return $this->demande_id; }
    public function getNomFichier() { return $this->nom_fichier; }
    public function getCheminFichier() { return $this->chemin_fichier; }
    public function getTypeFichier() { return $this->type_fichier; }
    public function getTaille() { return $this->taille; }
    public function getUploadedAt() { return $this->uploaded_at; }
    public function getDb() { return $this->db; }

    public function setId($id) { $this->id = $id; }
    public function setDemandeId($demande_id) { $this->demande_id = $demande_id; }
    public function setNomFichier($nom_fichier) { $this->nom_fichier = $nom_fichier; }
    public function setCheminFichier($chemin_fichier) { $this->chemin_fichier = $chemin_fichier; }
    public function setTypeFichier($type_fichier) { $this->type_fichier = $type_fichier; }
    public function setTaille($taille) { $this->taille = $taille; }
    public function setUploadedAt($uploaded_at) { $this->uploaded_at = $uploaded_at; }
}
