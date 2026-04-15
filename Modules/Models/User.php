<?php

declare(strict_types=1);

namespace Modules\Models;

use Modules\Core\Database;
use PDO;

final class User
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public static function fromDatabase(): self
    {
        return new self(Database::pdo());
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findByMail(string $mail): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, nom, prenom, mail, mdp FROM utilisateur WHERE mail = :mail LIMIT 1');
        $stmt->execute(['mail' => $mail]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function mailExists(string $mail): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM utilisateur WHERE mail = :mail LIMIT 1');
        $stmt->execute(['mail' => $mail]);
        return (bool) $stmt->fetch();
    }

    public function create(string $nom, string $prenom, string $mail, string $passwordHash): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO utilisateur (nom, prenom, mail, mdp) VALUES (:nom, :prenom, :mail, :mdp)'
        );
        $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'mail' => $mail,
            'mdp' => $passwordHash,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function upgradePasswordHash(int $id, string $newHash): void
    {
        $stmt = $this->pdo->prepare('UPDATE utilisateur SET mdp = :mdp WHERE id = :id');
        $stmt->execute([
            'mdp' => $newHash,
            'id' => $id,
        ]);
    }
}
