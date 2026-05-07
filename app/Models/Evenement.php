<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Evenement {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /**
     * Get all events ordered by date
     */
    public function all(): array {
        $sql = 'SELECT e.* FROM evenements e ORDER BY e.date_evenement DESC';
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get upcoming events (future dates)
     */
    public function upcoming(): array {
        $sql = 'SELECT e.* FROM evenements e 
                WHERE e.date_evenement >= CURDATE() 
                ORDER BY e.date_evenement ASC';
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get past events
     */
    public function past(): array {
        $sql = 'SELECT e.* FROM evenements e 
                WHERE e.date_evenement < CURDATE() 
                ORDER BY e.date_evenement DESC';
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Find event by ID
     */
    public function find(int $id): ?array {
        $sql = 'SELECT e.* FROM evenements e WHERE e.id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Create new event
     */
    public function create(array $data): int {
        $sql = 'INSERT INTO evenements (titre, description, lieu, date_evenement, heure, categorie, image_url) 
                VALUES (:titre, :description, :lieu, :date_evenement, :heure, :categorie, :image_url)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':titre' => $data['titre'] ?? null,
            ':description' => $data['description'] ?? null,
            ':lieu' => $data['lieu'] ?? null,
            ':date_evenement' => $data['date_evenement'] ?? null,
            ':heure' => $data['heure'] ?? null,
            ':categorie' => $data['categorie'] ?? null,
            ':image_url' => $data['image_url'] ?? null
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Update event
     */
    public function update(int $id, array $data): bool {
        $sql = 'UPDATE evenements SET 
                titre = :titre,
                description = :description,
                lieu = :lieu,
                date_evenement = :date_evenement,
                heure = :heure,
                categorie = :categorie,
                image_url = :image_url
                WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':titre' => $data['titre'] ?? null,
            ':description' => $data['description'] ?? null,
            ':lieu' => $data['lieu'] ?? null,
            ':date_evenement' => $data['date_evenement'] ?? null,
            ':heure' => $data['heure'] ?? null,
            ':categorie' => $data['categorie'] ?? null,
            ':image_url' => $data['image_url'] ?? null
        ]);
    }

    /**
     * Delete event
     */
    public function delete(int $id): bool {
        $sql = 'DELETE FROM evenements WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get events by category
     */
    public function getByCategory(string $categorie): array {
        $sql = 'SELECT e.* FROM evenements e 
                WHERE e.categorie = :categorie
                ORDER BY e.date_evenement DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':categorie' => $categorie]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Count events by category
     */
    public function countByCategory(string $categorie): int {
        $sql = 'SELECT COUNT(*) as count FROM evenements WHERE categorie = :categorie';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':categorie' => $categorie]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get all unique categories
     */
    public function getCategories(): array {
        $sql = 'SELECT DISTINCT categorie FROM evenements WHERE categorie IS NOT NULL ORDER BY categorie';
        $stmt = $this->pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_column($results, 'categorie');
    }

    /**
     * Count events by categories (for dashboard)
     */
    public function countByCategories(): array {
        $sql = 'SELECT categorie, COUNT(*) as count FROM evenements 
                WHERE categorie IS NOT NULL
                GROUP BY categorie
                ORDER BY count DESC';
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Search events
     */
    public function search(string $query): array {
        $q = '%' . $query . '%';
        $sql = 'SELECT e.* FROM evenements e 
                WHERE e.titre LIKE :q OR e.description LIKE :q OR e.lieu LIKE :q
                ORDER BY e.date_evenement DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':q' => $q]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Count total events
     */
    public function count(): int {
        $sql = 'SELECT COUNT(*) as count FROM evenements';
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }
}
