<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Evenement
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('
            SELECT e.*, c.nom AS categorie_nom, c.image_url AS categorie_image
            FROM evenements e
            LEFT JOIN categorie_evenement c ON e.categorie_id = c.id
            ORDER BY e.date_evenement DESC
        ');

        return $stmt->fetchAll();
    }

    public function upcoming(): array
    {
        $stmt = $this->pdo->query('
            SELECT e.*, c.nom AS categorie_nom, c.image_url AS categorie_image
            FROM evenements e
            LEFT JOIN categorie_evenement c ON e.categorie_id = c.id
            WHERE e.date_evenement >= CURDATE()
            ORDER BY e.date_evenement ASC
        ');

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT e.*, c.nom AS categorie_nom, c.image_url AS categorie_image
            FROM evenements e
            LEFT JOIN categorie_evenement c ON e.categorie_id = c.id
            WHERE e.id = :id
        ');
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO evenements (titre, description, max_participants, lieu, date_evenement, heure, categorie_id) 
            VALUES (:titre, :description, :max_participants, :lieu, :date_evenement, :heure, :categorie_id)
        ');
        return $stmt->execute([
            ':titre' => $data['titre'] ?? null,
            ':description' => $data['description'] ?? null,
            ':max_participants' => $data['max_participants'] ?? null,
            ':lieu' => $data['lieu'] ?? null,
            ':date_evenement' => $data['date_evenement'] ?? null,
            ':heure' => $data['heure'] ?? null,
            ':categorie_id' => $data['categorie_id'] ?? null
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE evenements SET 
                titre = :titre, 
                description = :description, 
                lieu = :lieu, 
                date_evenement = :date_evenement, 
                heure = :heure, 
                categorie_id = :categorie_id 
            WHERE id = :id
        ');
        return $stmt->execute([
            ':id' => $id,
            ':titre' => $data['titre'] ?? null,
            ':description' => $data['description'] ?? null,
            ':lieu' => $data['lieu'] ?? null,
            ':date_evenement' => $data['date_evenement'] ?? null,
            ':heure' => $data['heure'] ?? null,
            ':categorie_id' => $data['categorie_id'] ?? null
        ]);
    }

    public function delete(int $id): bool
    {
        try {
            // Delete participations first
            $deleteParticipation = $this->pdo->prepare('DELETE FROM participations WHERE event_id = :id');
            $deleteParticipation->execute([':id' => $id]);
            
            // Then delete the event
            $stmt = $this->pdo->prepare('DELETE FROM evenements WHERE id = :id');
            return $stmt->execute([':id' => $id]);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function count(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM evenements');
        return (int)$stmt->fetchColumn();
    }

    public function countByCategory(int $categoryId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM evenements WHERE categorie_id = :categorie_id');
        $stmt->execute([':categorie_id' => $categoryId]);
        return (int)$stmt->fetchColumn();
    }

    public function countByCategories(): array
    {
        $stmt = $this->pdo->query('
            SELECT c.nom, COUNT(e.id) as total 
            FROM categorie_evenement c 
            LEFT JOIN evenements e ON c.id = e.categorie_id 
            GROUP BY c.id
        ');
        return $stmt->fetchAll();
    }
}