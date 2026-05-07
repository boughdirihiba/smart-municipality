<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Find user by ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Get all users
     */
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    /**
     * Create new user
     */
    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO users (nom, prenom, email, mot_de_passe, telephone, adresse, role, created_at)
            VALUES (:nom, :prenom, :email, :mot_de_passe, :telephone, :adresse, :role, NOW())
        ');
        return $stmt->execute([
            ':nom' => $data['nom'] ?? null,
            ':prenom' => $data['prenom'] ?? null,
            ':email' => $data['email'] ?? null,
            ':mot_de_passe' => password_hash($data['password'] ?? '', PASSWORD_DEFAULT),
            ':telephone' => $data['telephone'] ?? null,
            ':adresse' => $data['adresse'] ?? null,
            ':role' => $data['role'] ?? 'citoyen'
        ]);
    }

    /**
     * Update user
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE users 
            SET nom = :nom, prenom = :prenom, email = :email, telephone = :telephone, adresse = :adresse
            WHERE id = :id
        ');
        return $stmt->execute([
            ':id' => $id,
            ':nom' => $data['nom'] ?? null,
            ':prenom' => $data['prenom'] ?? null,
            ':email' => $data['email'] ?? null,
            ':telephone' => $data['telephone'] ?? null,
            ':adresse' => $data['adresse'] ?? null
        ]);
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        if (password_verify($password, $hash)) {
            return true;
        }
        // Fallback for MD5 hashes (legacy)
        if (md5($password) === $hash) {
            return true;
        }
        return false;
    }

    /**
     * Count users
     */
    public function count(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM users');
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get users by role
     */
    public function getByRole(string $role): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE role = :role ORDER BY created_at DESC');
        $stmt->execute([':role' => $role]);
        return $stmt->fetchAll();
    }

    /**
     * Delete user
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
?>
