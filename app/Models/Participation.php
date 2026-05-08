<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Participation
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function exists(int $userId, int $eventId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM participations WHERE user_id = :user_id AND event_id = :event_id');
        $stmt->execute([':user_id' => $userId, ':event_id' => $eventId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create(array $data): array
    {
        try {
            $userId = $data['user_id'] ?? null;
            $eventId = $data['event_id'] ?? null;
            $nbParticipants = $data['nombre_participants'] ?? 1;

            // Check if already registered
            if ($this->exists($userId, $eventId)) {
                return ['success' => false, 'message' => 'Vous êtes déjà inscrit'];
            }

            // Check available spots
            $eventStmt = $this->pdo->prepare('SELECT max_participants FROM evenements WHERE id = :id');
            $eventStmt->execute([':id' => $eventId]);
            $event = $eventStmt->fetch();

            if ($event) {
                $validParticipations = $this->countValidated($eventId);
                if ($validParticipations + $nbParticipants > $event['max_participants']) {
                    return ['success' => false, 'message' => 'Nombre de places insuffisant'];
                }
            }

            // Insert participation
            $stmt = $this->pdo->prepare('
                INSERT INTO participations (user_id, event_id, date_participation, statut, statut_validation, nombre_participants) 
                VALUES (:user_id, :event_id, NOW(), "inscrit", "en_attente", :nombre_participants)
            ');
            $success = $stmt->execute([
                ':user_id' => $userId,
                ':event_id' => $eventId,
                ':nombre_participants' => $nbParticipants
            ]);

            return $success 
                ? ['success' => true, 'message' => '✅ Inscription en attente de validation.']
                : ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    public function createDirect(int $userId, int $eventId, int $nbParticipants = 1): array
    {
        try {
            if ($this->exists($userId, $eventId)) {
                return ['success' => false, 'message' => 'Vous êtes déjà inscrit'];
            }

            $eventStmt = $this->pdo->prepare('SELECT max_participants FROM evenements WHERE id = :event_id');
            $eventStmt->execute([':event_id' => $eventId]);
            $event = $eventStmt->fetch();

            if ($event) {
                $validParticipations = $this->countValidated($eventId);
                $remaining = $event['max_participants'] - $validParticipations;
                if ($remaining < $nbParticipants) {
                    return ['success' => false, 'message' => 'Places insuffisantes'];
                }
            }

            $stmt = $this->pdo->prepare('
                INSERT INTO participations (user_id, event_id, date_participation, statut, statut_validation, nombre_participants) 
                VALUES (:user_id, :event_id, NOW(), "inscrit", "valide", :nombre_participants)
            ');
            $success = $stmt->execute([
                ':user_id' => $userId,
                ':event_id' => $eventId,
                ':nombre_participants' => $nbParticipants
            ]);

            return $success 
                ? ['success' => true, 'message' => 'Inscription réussie']
                : ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function validate(int $participationId): array
    {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE participations 
                SET statut_validation = "valide", date_validation = NOW() 
                WHERE id = :id
            ');
            $success = $stmt->execute([':id' => $participationId]);

            return $success 
                ? ['success' => true, 'message' => '✅ Participation validée.']
                : ['success' => false, 'message' => 'Erreur lors de la validation'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function reject(int $participationId, ?string $comment = null): array
    {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE participations 
                SET statut_validation = "refuse", commentaire_refus = :commentaire 
                WHERE id = :id
            ');
            $success = $stmt->execute([
                ':id' => $participationId,
                ':commentaire' => $comment
            ]);

            return $success 
                ? ['success' => true, 'message' => 'Participation refusée']
                : ['success' => false, 'message' => 'Erreur lors du refus'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancel(int $userId, int $eventId): array
    {
        try {
            $stmt = $this->pdo->prepare('
                DELETE FROM participations 
                WHERE user_id = :user_id AND event_id = :event_id
            ');
            $success = $stmt->execute([
                ':user_id' => $userId,
                ':event_id' => $eventId
            ]);

            return $success 
                ? ['success' => true, 'message' => 'Participation annulée']
                : ['success' => false, 'message' => 'Erreur lors de l\'annulation'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function countValidated(int $eventId): int
    {
        $stmt = $this->pdo->prepare('
            SELECT SUM(nombre_participants) as total 
            FROM participations 
            WHERE event_id = :event_id AND statut_validation = "valide"
        ');
        $stmt->execute([':event_id' => $eventId]);
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    public function countPending(int $eventId): int
    {
        $stmt = $this->pdo->prepare('
            SELECT SUM(nombre_participants) as total 
            FROM participations 
            WHERE event_id = :event_id AND statut_validation = "en_attente"
        ');
        $stmt->execute([':event_id' => $eventId]);
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT p.*, e.titre, e.date_evenement, e.lieu
            FROM participations p
            JOIN evenements e ON p.event_id = e.id
            WHERE p.user_id = :user_id
            ORDER BY e.date_evenement DESC
        ');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getByEvent(int $eventId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT p.*, u.nom, u.prenom, u.email
            FROM participations p
            JOIN users u ON p.user_id = u.id
            WHERE p.event_id = :event_id
            ORDER BY p.date_participation DESC
        ');
        $stmt->execute([':event_id' => $eventId]);
        return $stmt->fetchAll();
    }

    public function find(int $participationId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT p.*, u.nom, u.prenom, u.email, e.titre, e.lieu, e.date_evenement, e.heure
            FROM participations p
            JOIN users u ON p.user_id = u.id
            JOIN evenements e ON p.event_id = e.id
            WHERE p.id = :id
        ');
        $stmt->execute([':id' => $participationId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}