<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Blog
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Get all blog posts
     */
    public function all(): array
    {
        $stmt = $this->pdo->query('
            SELECT p.*, u.nom, u.prenom, u.avatar
            FROM posts p
            LEFT JOIN utilisateurs u ON p.user_id = u.id
            ORDER BY p.created_at DESC
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get single post by ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT p.*, u.nom, u.prenom, u.avatar, u.email
            FROM posts p
            LEFT JOIN utilisateurs u ON p.user_id = u.id
            WHERE p.id = :id
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Get posts by user
     */
    public function getByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT p.*
            FROM posts p
            WHERE p.user_id = :user_id
            ORDER BY p.created_at DESC
        ');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Create new post
     */
    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO posts (user_id, content, image, video)
            VALUES (:user_id, :content, :image, :video)
        ');
        return $stmt->execute([
            ':user_id' => $data['user_id'] ?? null,
            ':content' => $data['content'] ?? null,
            ':image' => $data['image'] ?? null,
            ':video' => $data['video'] ?? null
        ]);
    }

    /**
     * Update post
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE posts 
            SET content = :content, image = :image, video = :video
            WHERE id = :id
        ');
        return $stmt->execute([
            ':id' => $id,
            ':content' => $data['content'] ?? null,
            ':image' => $data['image'] ?? null,
            ':video' => $data['video'] ?? null
        ]);
    }

    /**
     * Delete post
     */
    public function delete(int $id): bool
    {
        try {
            // Delete comments first
            $deleteComments = $this->pdo->prepare('DELETE FROM comments WHERE post_id = :id');
            $deleteComments->execute([':id' => $id]);

            // Delete reactions
            $deleteReactions = $this->pdo->prepare('DELETE FROM reactions WHERE post_id = :id');
            $deleteReactions->execute([':id' => $id]);

            // Delete post
            $stmt = $this->pdo->prepare('DELETE FROM posts WHERE id = :id');
            return $stmt->execute([':id' => $id]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Search posts
     */
    public function search(string $query): array
    {
        $searchTerm = '%' . $query . '%';
        $stmt = $this->pdo->prepare('
            SELECT p.*, u.nom, u.prenom
            FROM posts p
            LEFT JOIN utilisateurs u ON p.user_id = u.id
            WHERE p.content LIKE :query OR u.nom LIKE :query OR u.prenom LIKE :query
            ORDER BY p.created_at DESC
        ');
        $stmt->execute([':query' => $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Count posts
     */
    public function count(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM posts');
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get recent posts
     */
    public function getRecent(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare('
            SELECT p.*, u.nom, u.prenom, u.avatar
            FROM posts p
            LEFT JOIN utilisateurs u ON p.user_id = u.id
            ORDER BY p.created_at DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
?>
