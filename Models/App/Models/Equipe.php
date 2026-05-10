<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Equipe
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO equipes (nom, description, type_intervention, nombre_agents, statut)
             VALUES (:nom, :description, :type_intervention, :nombre_agents, :statut)'
        );

        $stmt->execute([
            ':nom' => $data['nom'],
            ':description' => $data['description'] ?? null,
            ':type_intervention' => $data['type_intervention'],
            ':nombre_agents' => $data['nombre_agents'] ?? 1,
            ':statut' => 'disponible',
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM equipes WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return is_array($result) ? $result : null;
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM equipes ORDER BY nom ASC');
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $statut): bool
    {
        $stmt = $this->pdo->prepare('UPDATE equipes SET statut = :statut, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([':statut' => $statut, ':id' => $id]);
    }

    public function addAgent(int $equipeId, int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO agents_equipe (equipe_id, user_id, role, active) VALUES (:equipe_id, :user_id, :role, 1)'
            );
            return $stmt->execute([':equipe_id' => $equipeId, ':user_id' => $userId, ':role' => 'agent']);
        } catch (\Throwable $e) {
            error_log('Error adding agent: ' . $e->getMessage());
            return false;
        }
    }

    public function getAgents(int $equipeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.*, u.nom, u.prenom, u.email FROM agents_equipe a
             LEFT JOIN utilisateurs u ON u.id = a.user_id
             WHERE a.equipe_id = :equipe_id AND a.active = 1'
        );
        $stmt->execute([':equipe_id' => $equipeId]);
        return $stmt->fetchAll();
    }
}
