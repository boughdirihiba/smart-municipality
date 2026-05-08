<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class RendezVous
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Get all appointments
     */
    public function all(): array
    {
        $stmt = $this->pdo->query('
            SELECT r.*, c.nom AS service_nom, u.nom AS user_nom, u.prenom AS user_prenom, u.email AS user_email
            FROM rendez_vous r
            JOIN categories c ON r.categorie_id = c.id
            JOIN utilisateurs u ON r.user_id = u.id
            ORDER BY r.date_rdv DESC, r.heure ASC
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get appointments for specific user
     */
    public function getByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT r.*, c.nom AS service_nom
            FROM rendez_vous r
            JOIN categories c ON r.categorie_id = c.id
            WHERE r.user_id = :user_id
            ORDER BY r.date_rdv DESC, r.heure ASC
        ');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get single appointment
     */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT r.*, c.nom AS service_nom, u.nom AS user_nom, u.prenom AS user_prenom
            FROM rendez_vous r
            JOIN categories c ON r.categorie_id = c.id
            JOIN utilisateurs u ON r.user_id = u.id
            WHERE r.id = :id
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Create appointment
     */
    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO rendez_vous (user_id, categorie_id, date_rdv, heure, statut)
            VALUES (:user_id, :categorie_id, :date_rdv, :heure, :statut)
        ');
        return $stmt->execute([
            ':user_id' => $data['user_id'] ?? null,
            ':categorie_id' => $data['categorie_id'] ?? null,
            ':date_rdv' => $data['date_rdv'] ?? null,
            ':heure' => $data['heure'] ?? null,
            ':statut' => $data['statut'] ?? 'confirme'
        ]);
    }

    /**
     * Update appointment
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE rendez_vous
            SET categorie_id = :categorie_id, date_rdv = :date_rdv, heure = :heure, statut = :statut
            WHERE id = :id
        ');
        return $stmt->execute([
            ':id' => $id,
            ':categorie_id' => $data['categorie_id'] ?? null,
            ':date_rdv' => $data['date_rdv'] ?? null,
            ':heure' => $data['heure'] ?? null,
            ':statut' => $data['statut'] ?? 'confirme'
        ]);
    }

    /**
     * Delete appointment
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM rendez_vous WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get available time slots for a category and date
     */
    public function getAvailableSlots(int $categoryId, string $date): array
    {
        $allSlots = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];

        $stmt = $this->pdo->prepare('
            SELECT heure FROM rendez_vous
            WHERE categorie_id = :categorie_id AND date_rdv = :date_rdv AND statut != "annule"
        ');
        $stmt->execute([
            ':categorie_id' => $categoryId,
            ':date_rdv' => $date
        ]);

        $bookedSlots = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $available = array_diff($allSlots, $bookedSlots);

        return array_values($available);
    }

    /**
     * Check if a time slot is taken
     */
    public function isSlotTaken(int $categoryId, string $date, string $time): bool
    {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) FROM rendez_vous
            WHERE categorie_id = :categorie_id AND date_rdv = :date_rdv AND heure = :heure AND statut != "annule"
        ');
        $stmt->execute([
            ':categorie_id' => $categoryId,
            ':date_rdv' => $date,
            ':heure' => $time
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Count appointments
     */
    public function count(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM rendez_vous');
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get appointments by date range
     */
    public function getByDateRange(string $startDate, string $endDate): array
    {
        $stmt = $this->pdo->prepare('
            SELECT r.*, c.nom AS service_nom, u.nom AS user_nom, u.prenom AS user_prenom
            FROM rendez_vous r
            JOIN categories c ON r.categorie_id = c.id
            JOIN utilisateurs u ON r.user_id = u.id
            WHERE r.date_rdv BETWEEN :start AND :end
            ORDER BY r.date_rdv ASC, r.heure ASC
        ');
        $stmt->execute([
            ':start' => $startDate,
            ':end' => $endDate
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
?>
