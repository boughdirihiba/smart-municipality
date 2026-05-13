<?php

declare(strict_types=1);

class Evenement
{
    private ?int    $id            = null;
    private string  $titre         = '';
    private string  $description   = '';
    private int     $maxParticipants = 0;
    private string  $lieu          = '';
    private string  $dateEvenement = '';
    private string  $heure         = '';
    private ?int    $categorieId   = null;

    public function getId(): ?int           { return $this->id; }
    public function getTitre(): string      { return $this->titre; }
    public function getDescription(): string{ return $this->description; }
    public function getMaxParticipants(): int{ return $this->maxParticipants; }
    public function getLieu(): string       { return $this->lieu; }
    public function getDateEvenement(): string{ return $this->dateEvenement; }
    public function getHeure(): string      { return $this->heure; }
    public function getCategorieId(): ?int  { return $this->categorieId; }

    public function setId(?int $id): void              { $this->id = $id; }
    public function setTitre(string $titre): void      { $this->titre = $titre; }
    public function setDescription(string $d): void    { $this->description = $d; }
    public function setMaxParticipants(int $n): void   { $this->maxParticipants = $n; }
    public function setLieu(string $lieu): void        { $this->lieu = $lieu; }
    public function setDateEvenement(string $d): void  { $this->dateEvenement = $d; }
    public function setHeure(string $h): void          { $this->heure = $h; }
    public function setCategorieId(?int $id): void     { $this->categorieId = $id; }
}
