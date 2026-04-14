<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Signalement
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function create(array $data): bool
    {
        try {
            $this->pdo->beginTransaction();

            $locationStmt = $this->pdo->prepare(
                'INSERT INTO localisations (adresse, quartier, latitude, longitude)
                 VALUES (:adresse, :quartier, :latitude, :longitude)'
            );
            $locationStmt->execute([
                ':adresse' => $data['adresse'],
                ':quartier' => $data['quartier'],
                ':latitude' => $data['latitude'],
                ':longitude' => $data['longitude'],
            ]);

            $localisationId = (int)$this->pdo->lastInsertId();

            $signalementStmt = $this->pdo->prepare(
                'INSERT INTO signalements (titre, description, image, categorie, latitude, longitude, statut, date_signalement, user_id, localisation_id)
                 VALUES (:titre, :description, :image, :categorie, :latitude, :longitude, :statut, NOW(), :user_id, :localisation_id)'
            );
            $ok = $signalementStmt->execute([
                ':titre' => $data['titre'],
                ':description' => $data['description'],
                ':image' => $data['image'],
                ':categorie' => $data['categorie'],
                ':latitude' => $data['latitude'],
                ':longitude' => $data['longitude'],
                ':statut' => 'en_attente',
                ':user_id' => $data['user_id'],
                ':localisation_id' => $localisationId,
            ]);

            if ($ok) {
                $signalementId = (int)$this->pdo->lastInsertId();
                $this->insertPositionHistory(
                    $signalementId,
                    (float)$data['latitude'],
                    (float)$data['longitude'],
                    'citoyen',
                    'Position initiale du signalement'
                );

                $this->pdo->commit();
                return true;
            }

            $this->pdo->rollBack();
            return false;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return false;
        }
    }

    private function insertPositionHistory(
        int $signalementId,
        float $latitude,
        float $longitude,
        string $source,
        ?string $commentaire
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO historique_positions (signalement_id, latitude, longitude, source, commentaire)
             VALUES (:signalement_id, :latitude, :longitude, :source, :commentaire)'
        );

        $stmt->execute([
            ':signalement_id' => $signalementId,
            ':latitude' => $latitude,
            ':longitude' => $longitude,
            ':source' => $source,
            ':commentaire' => $commentaire,
        ]);
    }

    public function allByUser(int $userId): array
    {
        $sql = 'SELECT s.*, l.adresse, l.quartier
                FROM signalements s
                LEFT JOIN localisations l ON l.id = s.localisation_id
                WHERE s.user_id = :user_id
                ORDER BY s.date_signalement DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $sql = 'SELECT s.*, l.adresse, l.quartier
                FROM signalements s
                LEFT JOIN localisations l ON l.id = s.localisation_id
                WHERE s.id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function allWithFilters(?string $categorie, ?string $statut): array
    {
        $sql = 'SELECT s.*, l.adresse, l.quartier
                FROM signalements s
                LEFT JOIN localisations l ON l.id = s.localisation_id
                WHERE 1=1';
        $params = [];

        if ($categorie !== null && $categorie !== '') {
            $sql .= ' AND categorie = :categorie';
            $params[':categorie'] = $categorie;
        }

        if ($statut !== null && $statut !== '') {
            $sql .= ' AND statut = :statut';
            $params[':statut'] = $statut;
        }

        $sql .= ' ORDER BY s.date_signalement DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $statut): bool
    {
        $stmt = $this->pdo->prepare('UPDATE signalements SET statut = :statut WHERE id = :id');
        return $stmt->execute([':statut' => $statut, ':id' => $id]);
    }

    public function updateStatusAndPosition(int $id, string $statut, ?float $latitude, ?float $longitude, ?string $commentaire): bool
    {
        try {
            $this->pdo->beginTransaction();

            $current = $this->find($id);
            if (!$current) {
                $this->pdo->rollBack();
                return false;
            }

            $statusStmt = $this->pdo->prepare('UPDATE signalements SET statut = :statut WHERE id = :id');
            $okStatus = $statusStmt->execute([':statut' => $statut, ':id' => $id]);

            if (!$okStatus) {
                $this->pdo->rollBack();
                return false;
            }

            if ($latitude !== null && $longitude !== null) {
                $hasMoved = ((float)$current['latitude'] !== $latitude) || ((float)$current['longitude'] !== $longitude);

                if ($hasMoved) {
                    $signalementPosStmt = $this->pdo->prepare(
                        'UPDATE signalements SET latitude = :latitude, longitude = :longitude WHERE id = :id'
                    );
                    $signalementPosStmt->execute([
                        ':latitude' => $latitude,
                        ':longitude' => $longitude,
                        ':id' => $id,
                    ]);

                    if (!empty($current['localisation_id'])) {
                        $locationStmt = $this->pdo->prepare(
                            'UPDATE localisations SET latitude = :latitude, longitude = :longitude WHERE id = :localisation_id'
                        );
                        $locationStmt->execute([
                            ':latitude' => $latitude,
                            ':longitude' => $longitude,
                            ':localisation_id' => (int)$current['localisation_id'],
                        ]);
                    }

                    $this->insertPositionHistory($id, $latitude, $longitude, 'admin', $commentaire ?: 'Position modifiee par admin');
                }
            }

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return false;
        }
    }

    public function positionHistory(int $signalementId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, signalement_id, latitude, longitude, source, commentaire, created_at
             FROM historique_positions
             WHERE signalement_id = :signalement_id
             ORDER BY created_at DESC, id DESC'
        );
        $stmt->execute([':signalement_id' => $signalementId]);
        return $stmt->fetchAll();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM signalements WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function mapData(?string $categorie, ?string $date, ?string $zone): array
    {
        $sql = 'SELECT s.id, s.titre, s.description, s.image, s.categorie,
                       COALESCE(l.latitude, s.latitude) AS latitude,
                       COALESCE(l.longitude, s.longitude) AS longitude,
                       s.statut, s.date_signalement, l.adresse, l.quartier
                FROM signalements s
                LEFT JOIN localisations l ON l.id = s.localisation_id
                WHERE 1=1';
        $params = [];

        if ($categorie !== null && $categorie !== '') {
            $sql .= ' AND categorie = :categorie';
            $params[':categorie'] = $categorie;
        }

        if ($date !== null && $date !== '') {
            $sql .= ' AND DATE(date_signalement) = :d';
            $params[':d'] = $date;
        }

        if ($zone === 'centre') {
            $sql .= ' AND latitude BETWEEN 36.78 AND 36.84 AND longitude BETWEEN 10.14 AND 10.24';
        } elseif ($zone === 'nord') {
            $sql .= ' AND latitude > 36.84';
        } elseif ($zone === 'sud') {
            $sql .= ' AND latitude < 36.78';
        }

        $sql .= ' ORDER BY s.date_signalement DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
