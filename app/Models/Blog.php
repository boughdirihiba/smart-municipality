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
            FROM blog_posts p
            LEFT JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC
        ');
        return $stmt->fetchAll();
    }

    /**
     * Get single post by ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT p.*, u.nom, u.prenom, u.avatar, u.email
            FROM blog_posts p
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.id = :id
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Get posts by user
     */
    public function getByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT p.*
            FROM blog_posts p
            WHERE p.user_id = :user_id
            ORDER BY p.created_at DESC
        ');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Create new post
     */
    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO blog_posts (user_id, content, image, video, created_at)
            VALUES (:user_id, :content, :image, :video, NOW())
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
            UPDATE blog_posts 
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
            $deleteComments = $this->pdo->prepare('DELETE FROM blog_comments WHERE post_id = :id');
            $deleteComments->execute([':id' => $id]);

            // Delete reactions
            $deleteReactions = $this->pdo->prepare('DELETE FROM blog_reactions WHERE post_id = :id');
            $deleteReactions->execute([':id' => $id]);

            // Delete post
            $stmt = $this->pdo->prepare('DELETE FROM blog_posts WHERE id = :id');
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
            FROM blog_posts p
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.content LIKE :query OR u.nom LIKE :query OR u.prenom LIKE :query
            ORDER BY p.created_at DESC
        ');
        $stmt->execute([':query' => $searchTerm]);
        return $stmt->fetchAll();
    }

    /**
     * Count posts
     */
    public function count(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM blog_posts');
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get recent posts
     */
    public function getRecent(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare('
            SELECT p.*, u.nom, u.prenom, u.avatar
            FROM blog_posts p
            LEFT JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
