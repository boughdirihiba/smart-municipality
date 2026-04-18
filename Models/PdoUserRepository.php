<?php

declare(strict_types=1);

namespace Models;

use PDO;

final class PdoUserRepository implements UserRepository
{
    private PDO $pdo;
    private ?bool $hasTelephoneColumn = null;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByMail(string $mail): ?User
    {
        $cols = $this->selectColumns();
        $stmt = $this->pdo->prepare('SELECT ' . $cols . ' FROM utilisateur WHERE mail = :mail LIMIT 1');
        $stmt->execute(['mail' => $mail]);
        $row = $stmt->fetch();
        return $this->hydrate($row);
    }

    public function findById(int $id): ?User
    {
        $cols = $this->selectColumns();
        $stmt = $this->pdo->prepare('SELECT ' . $cols . ' FROM utilisateur WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $this->hydrate($row);
    }

    public function countUsers(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS c FROM utilisateur');
        $row = $stmt->fetch();
        return (int)($row['c'] ?? 0);
    }

    /** @return array<int, array{id:int, nom:string, prenom:string, mail:string, telephone?:string}> */
    public function listUsers(int $limit = 200, int $offset = 0): array
    {
        $limit = max(1, min(500, $limit));
        $offset = max(0, $offset);

        $cols = $this->hasTelephone() ? 'id, nom, prenom, mail, telephone' : 'id, nom, prenom, mail';
        $sql = 'SELECT ' . $cols . ' FROM utilisateur ORDER BY id DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $item = [
                'id' => (int)($row['id'] ?? 0),
                'nom' => (string)($row['nom'] ?? ''),
                'prenom' => (string)($row['prenom'] ?? ''),
                'mail' => (string)($row['mail'] ?? ''),
            ];
            if ($this->hasTelephone()) {
                $item['telephone'] = (string)($row['telephone'] ?? '');
            }
            if ($item['id'] > 0) {
                $out[] = $item;
            }
        }

        return $out;
    }

    /** @param array{nom:string, prenom:string, mail:string, telephone?:string} $data */
    public function updateUser(int $id, array $data): void
    {
        $this->updateProfile($id, $data);
    }

    public function deleteUser(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM utilisateur WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function mailExistsForOtherId(string $mail, int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM utilisateur WHERE mail = :mail AND id <> :id LIMIT 1');
        $stmt->execute(['mail' => $mail, 'id' => $id]);
        return (bool) $stmt->fetch();
    }

    public function mailExists(string $mail): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM utilisateur WHERE mail = :mail LIMIT 1');
        $stmt->execute(['mail' => $mail]);
        return (bool) $stmt->fetch();
    }

    public function createUser(string $nom, string $prenom, string $mail, string $passwordHash): int
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

    public function updatePassword(int $id, string $newHash): void
    {
        $stmt = $this->pdo->prepare('UPDATE utilisateur SET mdp = :mdp WHERE id = :id');
        $stmt->execute(['mdp' => $newHash, 'id' => $id]);
    }

    /** @param array{nom:string, prenom:string, mail:string, telephone?:string} $data */
    public function updateProfile(int $id, array $data): void
    {
        $fields = ['nom = :nom', 'prenom = :prenom', 'mail = :mail'];
        $params = [
            'id' => $id,
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'mail' => $data['mail'],
        ];

        if ($this->hasTelephone()) {
            $fields[] = 'telephone = :telephone';
            $params['telephone'] = $data['telephone'] ?? '';
        }

        $sql = 'UPDATE utilisateur SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    private function hasTelephone(): bool
    {
        if ($this->hasTelephoneColumn !== null) {
            return $this->hasTelephoneColumn;
        }

        try {
            $stmt = $this->pdo->query("SHOW COLUMNS FROM utilisateur LIKE 'telephone'");
            $row = $stmt->fetch();
            $this->hasTelephoneColumn = (bool) $row;
        } catch (\Throwable $e) {
            $this->hasTelephoneColumn = false;
        }

        return $this->hasTelephoneColumn;
    }

    private function selectColumns(): string
    {
        $base = 'id, nom, prenom, mail, mdp';
        return $this->hasTelephone() ? ($base . ', telephone') : $base;
    }

    /** @param mixed $row */
    private function hydrate($row): ?User
    {
        if (!$row || !is_array($row)) {
            return null;
        }

        $user = new User();
        $user->setId((int)($row['id'] ?? 0));
        $user->setNom((string)($row['nom'] ?? ''));
        $user->setPrenom((string)($row['prenom'] ?? ''));
        $user->setMail((string)($row['mail'] ?? ''));
        $user->setMdp((string)($row['mdp'] ?? ''));
        $user->setTelephone((string)($row['telephone'] ?? ''));

        return $user;
    }
}
