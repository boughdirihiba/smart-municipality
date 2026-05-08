<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class BlogComment
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Get comments for a post
     */
    public function getByPost(int $postId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT c.*, u.nom, u.prenom, u.avatar
            FROM blog_comments c
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.post_id = :post_id
            ORDER BY c.created_at DESC
        ');
        $stmt->execute([':post_id' => $postId]);
        return $stmt->fetchAll();
    }

    /**
     * Create comment
     */
    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO blog_comments (post_id, user_id, content, created_at)
            VALUES (:post_id, :user_id, :content, NOW())
        ');
        return $stmt->execute([
            ':post_id' => $data['post_id'] ?? null,
            ':user_id' => $data['user_id'] ?? null,
            ':content' => $data['content'] ?? null
        ]);
    }

    /**
     * Update comment
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE blog_comments 
            SET content = :content
            WHERE id = :id
        ');
        return $stmt->execute([
            ':id' => $id,
            ':content' => $data['content'] ?? null
        ]);
    }

    /**
     * Delete comment
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM blog_comments WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get single comment
     */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT c.*, u.nom, u.prenom, u.avatar
            FROM blog_comments c
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.id = :id
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Count comments on post
     */
    public function countByPost(int $postId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM blog_comments WHERE post_id = :post_id');
        $stmt->execute([':post_id' => $postId]);
        return (int)$stmt->fetchColumn();
    }
}
?>
