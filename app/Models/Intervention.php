<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\MapDataProviderInterface;
use App\Core\Database;
use PDO;

class Intervention implements MapDataProviderInterface
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function all(?string $type = null, ?string $statut = null): array
    {
        $sql = 'SELECT * FROM interventions WHERE 1=1';
        $params = [];

        if ($type !== null && $type !== '') {
            $sql .= ' AND type = :type';
            $params[':type'] = $type;
        }

        if ($statut !== null && $statut !== '') {
            $sql .= ' AND statut = :statut';
            $params[':statut'] = $statut;
        }

        $sql .= ' ORDER BY created_at DESC, id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM interventions WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO interventions (titre, description, type, latitude, longitude, statut, progression, date_intervention)
             VALUES (:titre, :description, :type, :latitude, :longitude, :statut, :progression, :date_intervention)'
        );

        return $stmt->execute([
            ':titre' => $data['titre'],
            ':description' => $data['description'],
            ':type' => $data['type'],
            ':latitude' => $data['latitude'],
            ':longitude' => $data['longitude'],
            ':statut' => $data['statut'],
            ':progression' => $data['progression'],
            ':date_intervention' => $data['date_intervention'] !== '' ? $data['date_intervention'] : null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE interventions
             SET titre = :titre,
                 description = :description,
                 type = :type,
                 latitude = :latitude,
                 longitude = :longitude,
                 statut = :statut,
                 progression = :progression,
                 date_intervention = :date_intervention,
                 updated_at = NOW()
             WHERE id = :id'
        );

        return $stmt->execute([
            ':id' => $id,
            ':titre' => $data['titre'],
            ':description' => $data['description'],
            ':type' => $data['type'],
            ':latitude' => $data['latitude'],
            ':longitude' => $data['longitude'],
            ':statut' => $data['statut'],
            ':progression' => $data['progression'],
            ':date_intervention' => $data['date_intervention'] !== '' ? $data['date_intervention'] : null,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM interventions WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function mapData(?string $categorie, ?string $date, ?string $zone): array
    {
        $sql = 'SELECT id, titre, description, type, latitude, longitude, statut, progression, date_intervention, created_at
                FROM interventions
                WHERE 1=1';
        $params = [];

        if ($categorie !== null && $categorie !== '') {
            $sql .= ' AND type = :type';
            $params[':type'] = $categorie;
        }

        if ($date !== null && $date !== '') {
            $sql .= ' AND DATE(COALESCE(date_intervention, created_at)) = :d';
            $params[':d'] = $date;
        }

        if ($zone === 'centre') {
            $sql .= ' AND latitude BETWEEN 36.78 AND 36.84 AND longitude BETWEEN 10.14 AND 10.24';
        } elseif ($zone === 'nord') {
            $sql .= ' AND latitude > 36.84';
        } elseif ($zone === 'sud') {
            $sql .= ' AND latitude < 36.78';
        }

        $sql .= ' ORDER BY created_at DESC, id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
