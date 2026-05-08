<?php

declare(strict_types=1);

namespace Models;

use Controles\PdoFaceIdRepositoryController;
use PDO;

final class PdoFaceIdRepository implements FaceIdRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /** @param array<int,float> $descriptor */
    public function upsert(int $userId, array $descriptor): void
    {
        PdoFaceIdRepositoryController::upsert($this, $userId, $descriptor);
    }

    /** @return array<int,float>|null */
    public function getByUserId(int $userId): ?array
    {
        return PdoFaceIdRepositoryController::getByUserId($this, $userId);
    }
}
