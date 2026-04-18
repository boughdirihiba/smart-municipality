<?php

declare(strict_types=1);

namespace Models;

final class User
{
    private int $id = 0;
    private string $nom = '';
    private string $prenom = '';
    private string $mail = '';
    private string $mdp = '';
    private string $telephone = '';

    public function getId(): int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getMail(): string { return $this->mail; }
    public function getMdp(): string { return $this->mdp; }
    public function getTelephone(): string { return $this->telephone; }

    public function setId(int $id): void { $this->id = $id; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setPrenom(string $prenom): void { $this->prenom = $prenom; }
    public function setMail(string $mail): void { $this->mail = $mail; }
    public function setMdp(string $mdp): void { $this->mdp = $mdp; }
    public function setTelephone(string $telephone): void { $this->telephone = $telephone; }
}
