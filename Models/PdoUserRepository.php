<?php

declare(strict_types=1);

namespace Models;

use Controles\PdoUserRepositoryController;
use PDO;

final class PdoUserRepository implements UserRepository
{
    private PDO $pdo;
    private ?bool $hasTelephoneColumn = null;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function getHasTelephoneColumn(): ?bool
    {
        return $this->hasTelephoneColumn;
    }

    public function setHasTelephoneColumn(?bool $value): void
    {
        $this->hasTelephoneColumn = $value;
    }

    public function findByMail(string $mail): ?User
    {
        return PdoUserRepositoryController::findByMail($this, $mail);
    }

    public function findById(int $id): ?User
    {
        return PdoUserRepositoryController::findById($this, $id);
    }

    public function countUsers(): int
    {
        return PdoUserRepositoryController::countUsers($this);
    }

    /** @return array<int, array{id:int, nom:string, prenom:string, mail:string, telephone?:string}> */
    public function listUsers(int $limit = 200, int $offset = 0): array
    {
        return PdoUserRepositoryController::listUsers($this, $limit, $offset);
    }

    /** @param array{nom:string, prenom:string, mail:string, telephone?:string} $data */
    public function updateUser(int $id, array $data): void
    {
        PdoUserRepositoryController::updateUser($this, $id, $data);
    }

    public function deleteUser(int $id): void
    {
        PdoUserRepositoryController::deleteUser($this, $id);
    }

    public function mailExistsForOtherId(string $mail, int $id): bool
    {
        return PdoUserRepositoryController::mailExistsForOtherId($this, $mail, $id);
    }

    public function mailExists(string $mail): bool
    {
        return PdoUserRepositoryController::mailExists($this, $mail);
    }

    public function createUser(string $nom, string $prenom, string $mail, string $passwordHash): int
    {
        return PdoUserRepositoryController::createUser($this, $nom, $prenom, $mail, $passwordHash);
    }

    public function updatePassword(int $id, string $newHash): void
    {
        PdoUserRepositoryController::updatePassword($this, $id, $newHash);
    }

    /** @param array{nom:string, prenom:string, mail:string, telephone?:string} $data */
    public function updateProfile(int $id, array $data): void
    {
        PdoUserRepositoryController::updateProfile($this, $id, $data);
    }
}
