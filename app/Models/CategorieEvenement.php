<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class CategorieEvenement
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM categorie_evenement ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categorie_evenement WHERE id = :id');
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO categorie_evenement (nom, description, image_url) 
            VALUES (:nom, :description, :image_url)
        ');
        return $stmt->execute([
            ':nom' => $data['nom'] ?? null,
            ':description' => $data['description'] ?? null,
            ':image_url' => $data['image_url'] ?? null
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE categorie_evenement 
            SET nom = :nom, description = :description, image_url = :image_url 
            WHERE id = :id
        ');
        return $stmt->execute([
            ':id' => $id,
            ':nom' => $data['nom'] ?? null,
            ':description' => $data['description'] ?? null,
            ':image_url' => $data['image_url'] ?? null
        ]);
    }

    public function delete(int $id): array
    {
        try {
            // Check if category has events
            $checkStmt = $this->pdo->prepare('SELECT COUNT(*) FROM evenements WHERE categorie_id = :id');
            $checkStmt->execute([':id' => $id]);
            $count = (int)$checkStmt->fetchColumn();

            if ($count > 0) {
                return ['success' => false, 'message' => 'Cette catégorie contient des événements'];
            }

            $stmt = $this->pdo->prepare('DELETE FROM categorie_evenement WHERE id = :id');
            $success = $stmt->execute([':id' => $id]);

            return $success 
                ? ['success' => true]
                : ['success' => false, 'message' => 'Erreur lors de la suppression'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function countEventsByCategory(int $categoryId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM evenements WHERE categorie_id = :categorie_id');
        $stmt->execute([':categorie_id' => $categoryId]);
        return (int)$stmt->fetchColumn();
    }
}