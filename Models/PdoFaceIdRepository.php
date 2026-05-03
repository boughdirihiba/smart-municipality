<?php

declare(strict_types=1);

namespace Models;

use PDO;

final class PdoFaceIdRepository implements FaceIdRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @param array<int,float> $descriptor */
    public function upsert(int $userId, array $descriptor): void
    {
        $json = json_encode($descriptor, JSON_UNESCAPED_UNICODE);
        if (!is_string($json)) {
            throw new \RuntimeException('Unable to encode face descriptor');
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO utilisateur_face_id (user_id, descriptor_json) VALUES (?, ?) '
            . 'ON DUPLICATE KEY UPDATE descriptor_json = ?'
        );
        $stmt->execute([$userId, $json, $json]);
    }

    /** @return array<int,float>|null */
    public function getByUserId(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT descriptor_json FROM utilisateur_face_id WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !isset($row['descriptor_json'])) {
            return null;
        }

        $decoded = json_decode((string)$row['descriptor_json'], true);
        if (!is_array($decoded)) {
            return null;
        }

        $out = [];
        foreach ($decoded as $v) {
            if (!is_numeric($v)) {
                return null;
            }
            $out[] = (float)$v;
        }

        return $out;
    }
}
