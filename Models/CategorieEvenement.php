<?php

declare(strict_types=1);

class CategorieEvenement
{
	private int $id = 0;
	private string $nom = '';
	private string $description = '';
	private string $image_url = '';

	public function getId(): int { return $this->id; }
	public function getNom(): string { return $this->nom; }
	public function getDescription(): string { return $this->description; }
	public function getImageUrl(): string { return $this->image_url; }

	public function setId(int $id): void { $this->id = $id; }
	public function setNom(string $nom): void { $this->nom = $nom; }
	public function setDescription(string $description): void { $this->description = $description; }
	public function setImageUrl(string $imageUrl): void { $this->image_url = $imageUrl; }
}