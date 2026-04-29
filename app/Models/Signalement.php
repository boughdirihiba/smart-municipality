<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\MapDataProviderInterface;
use App\Core\Database;
use PDO;

class Signalement implements MapDataProviderInterface
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

            $entity = $this->toEntity($data);

            $locationStmt = $this->pdo->prepare(
                'INSERT INTO localisations (adresse, quartier, latitude, longitude)
                 VALUES (:adresse, :quartier, :latitude, :longitude)'
            );
            $locationStmt->execute([
                ':adresse' => $data['adresse'],
                ':quartier' => $data['quartier'],
                ':latitude' => $entity->getLatitude(),
                ':longitude' => $entity->getLongitude(),
            ]);

            $localisationId = (int)$this->pdo->lastInsertId();
            $entity->setLocalisationId($localisationId);
            $entityData = $entity->toArray();

            $signalementStmt = $this->pdo->prepare(
                'INSERT INTO signalements (titre, description, image, categorie, latitude, longitude, statut, progression, date_signalement, user_id, localisation_id)
                 VALUES (:titre, :description, :image, :categorie, :latitude, :longitude, :statut, :progression, NOW(), :user_id, :localisation_id)'
            );
            $ok = $signalementStmt->execute([
                ':titre' => $entityData['titre'],
                ':description' => $entityData['description'],
                ':image' => $entityData['image'],
                ':categorie' => $entityData['categorie'],
                ':latitude' => $entityData['latitude'],
                ':longitude' => $entityData['longitude'],
                ':statut' => $entityData['statut'],
                ':progression' => (int)($entityData['progression'] ?? 0),
                ':user_id' => $entityData['user_id'],
                ':localisation_id' => $entityData['localisation_id'],
            ]);

            if ($ok) {
                $signalementId = (int)$this->pdo->lastInsertId();
                $this->insertPositionHistory(
                    $signalementId,
                    (float)$entityData['latitude'],
                    (float)$entityData['longitude'],
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

            // Log error for debugging
            error_log('Signalement creation error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());

            return false;
        }
    }

    public function toEntity(array $data): SignalementEntity
    {
        return SignalementEntity::fromArray($data);
    }

    public function findEntity(int $id): ?SignalementEntity
    {
        $row = $this->find($id);
        if ($row === null) {
            return null;
        }

        return $this->toEntity($row);
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
        $sql = 'SELECT s.*, l.adresse, l.quartier,
                   u.nom AS user_nom,
                   u.prenom AS user_prenom,
                   u.email AS user_email,
                   u.avatar AS user_avatar,
                   u.role AS user_role
            FROM signalements s
            LEFT JOIN localisations l ON l.id = s.localisation_id
            LEFT JOIN utilisateurs u ON u.id = s.user_id
                WHERE s.user_id = :user_id
                ORDER BY s.date_signalement DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $sql = 'SELECT s.*, l.adresse, l.quartier,
                   u.nom AS user_nom,
                   u.prenom AS user_prenom,
                   u.email AS user_email,
                   u.avatar AS user_avatar,
                   u.role AS user_role
            FROM signalements s
            LEFT JOIN localisations l ON l.id = s.localisation_id
            LEFT JOIN utilisateurs u ON u.id = s.user_id
                WHERE s.id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function allWithFilters(?string $categorie, ?string $statut): array
    {
        $sql = 'SELECT s.*, l.adresse, l.quartier,
                   u.nom AS user_nom,
                   u.prenom AS user_prenom,
                   u.email AS user_email,
                   u.avatar AS user_avatar,
                   u.role AS user_role
            FROM signalements s
            LEFT JOIN localisations l ON l.id = s.localisation_id
            LEFT JOIN utilisateurs u ON u.id = s.user_id
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

    public function updateStatusAndPosition(int $id, string $statut, int $progression, ?float $latitude, ?float $longitude, ?string $commentaire): bool
    {
        try {
            $this->pdo->beginTransaction();

            $current = $this->find($id);
            if (!$current) {
                $this->pdo->rollBack();
                return false;
            }

            $statusStmt = $this->pdo->prepare('UPDATE signalements SET statut = :statut, progression = :progression WHERE id = :id');
            $okStatus = $statusStmt->execute([':statut' => $statut, ':progression' => $progression, ':id' => $id]);

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
                  s.statut, s.progression, s.date_signalement, l.adresse, l.quartier,
                  u.nom AS user_nom,
                  u.prenom AS user_prenom,
                  u.role AS user_role
                FROM signalements s
                LEFT JOIN localisations l ON l.id = s.localisation_id
              LEFT JOIN utilisateurs u ON u.id = s.user_id
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
        $items = $stmt->fetchAll();

        foreach ($items as &$item) {
            $item['priority'] = $this->inferPriority($item);
        }
        unset($item);

        return $items;
    }

    private function inferPriority(array $signalement): string
    {
        $titre = mb_strtolower((string)($signalement['titre'] ?? ''));
        $description = mb_strtolower((string)($signalement['description'] ?? ''));
        $categorie = (string)($signalement['categorie'] ?? '');

        $priorityScore = 0;
        if (preg_match('/(danger|accident|urgent|bloque|incendie|electrocution|fuite majeure)/u', $description . ' ' . $titre) === 1) {
            $priorityScore += 3;
        }

        if (in_array($categorie, ['eau', 'route', 'eclairage'], true)) {
            $priorityScore += 1;
        }

        if (mb_strlen($description) >= 120) {
            $priorityScore += 1;
        }

        if ($priorityScore >= 4) {
            return 'urgent';
        }

        if ($priorityScore >= 2) {
            return 'moyen';
        }

        return 'faible';
    }

    public function countByUser(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM signalements WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }
}
