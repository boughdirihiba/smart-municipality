<?php

declare(strict_types=1);

namespace App\Models;

class SignalementEntity
{
    private int $id = 0;
    private int $userId = 0;
    private int $localisationId = 0;
    private string $titre = '';
    private string $description = '';
    private ?string $image = null;
    private string $categorie = '';
    private float $latitude = 0.0;
    private float $longitude = 0.0;
    private string $statut = 'en_attente';
    private ?string $dateSignalement = null;

    public static function fromArray(array $data): self
    {
        $image = null;
        if (array_key_exists('image', $data) && $data['image'] !== null && $data['image'] !== '') {
            $image = (string)$data['image'];
        }

        $entity = new self();
        $entity
            ->setId((int)($data['id'] ?? 0))
            ->setUserId((int)($data['user_id'] ?? 0))
            ->setLocalisationId((int)($data['localisation_id'] ?? 0))
            ->setTitre((string)($data['titre'] ?? ''))
            ->setDescription((string)($data['description'] ?? ''))
            ->setImage($image)
            ->setCategorie((string)($data['categorie'] ?? ''))
            ->setLatitude((float)($data['latitude'] ?? 0))
            ->setLongitude((float)($data['longitude'] ?? 0))
            ->setStatut((string)($data['statut'] ?? 'en_attente'))
            ->setDateSignalement(isset($data['date_signalement']) ? (string)$data['date_signalement'] : null);

        return $entity;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getLocalisationId(): int
    {
        return $this->localisationId;
    }

    public function setLocalisationId(int $localisationId): self
    {
        $this->localisationId = $localisationId;
        return $this;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getCategorie(): string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateSignalement(): ?string
    {
        return $this->dateSignalement;
    }

    public function setDateSignalement(?string $dateSignalement): self
    {
        $this->dateSignalement = $dateSignalement;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'localisation_id' => $this->localisationId,
            'titre' => $this->titre,
            'description' => $this->description,
            'image' => $this->image,
            'categorie' => $this->categorie,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'statut' => $this->statut,
            'date_signalement' => $this->dateSignalement,
        ];
    }
}
