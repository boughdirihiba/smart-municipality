<?php

declare(strict_types=1);

class Participation
{
    private ?int $id = null;
    private int $userId = 0;
    private int $eventId = 0;
    private string $dateParticipation = '';
    private string $statut = 'inscrit';
    private string $statutValidation = 'en_attente';
    private int $nombreParticipants = 1;
    private ?string $commentaireRefus = null;

    public function __construct(
        int $userId = 0,
        int $eventId = 0,
        string $statut = 'inscrit',
        int $nombreParticipants = 1,
        ?string $commentaireRefus = null
    ) {
        $this->userId           = $userId;
        $this->eventId          = $eventId;
        $this->statut           = $statut;
        $this->nombreParticipants = $nombreParticipants;
        $this->commentaireRefus = $commentaireRefus;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getUserId(): int { return $this->userId; }
    public function setUserId(int $userId): void { $this->userId = $userId; }

    public function getEventId(): int { return $this->eventId; }
    public function setEventId(int $eventId): void { $this->eventId = $eventId; }

    public function getDateParticipation(): string { return $this->dateParticipation; }
    public function setDateParticipation(string $date): void { $this->dateParticipation = $date; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): void { $this->statut = $statut; }

    public function getStatutValidation(): string { return $this->statutValidation; }
    public function setStatutValidation(string $statut): void { $this->statutValidation = $statut; }

    public function getNombreParticipants(): int { return $this->nombreParticipants; }
    public function setNombreParticipants(int $n): void { $this->nombreParticipants = $n; }

    public function getCommentaireRefus(): ?string { return $this->commentaireRefus; }
    public function setCommentaireRefus(?string $c): void { $this->commentaireRefus = $c; }
}
