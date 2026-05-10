<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\MapDataProviderInterface;
use App\Core\Database;
use PDO;
use Throwable;

class Intervention implements MapDataProviderInterface
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->ensureTasksColumn();
    }

    private function ensureTasksColumn(): void
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'interventions'
                   AND COLUMN_NAME = 'tasks_json'"
            );
            $stmt->execute();

            if ((int)$stmt->fetchColumn() === 0) {
                $this->pdo->exec('ALTER TABLE interventions ADD COLUMN tasks_json LONGTEXT NULL AFTER description');
            }
        } catch (Throwable $exception) {
            // Ignore schema upgrade failures so existing features keep working.
        }
    }

    public function normalizeTasksJson(?string $tasksJson): array
    {
        if ($tasksJson === null || trim($tasksJson) === '') {
            return [];
        }

        $decoded = json_decode($tasksJson, true);
        if (!is_array($decoded)) {
            return [];
        }

        $tasks = [];
        foreach ($decoded as $task) {
            if (!is_array($task)) {
                continue;
            }

            $label = trim((string)($task['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $tasks[] = [
                'label' => $label,
                'done' => !empty($task['done']),
            ];
        }

        return $tasks;
    }

    public function encodeTasks(array $tasks): string
    {
        $normalized = [];
        foreach ($tasks as $task) {
            if (!is_array($task)) {
                continue;
            }

            $label = trim((string)($task['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $normalized[] = [
                'label' => $label,
                'done' => !empty($task['done']),
            ];
        }

        return json_encode($normalized, JSON_UNESCAPED_UNICODE) ?: '[]';
    }

    public function calculateProgressionFromTasks(array $tasks): int
    {
        $normalized = [];
        foreach ($tasks as $task) {
            if (!is_array($task)) {
                continue;
            }

            $label = trim((string)($task['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $normalized[] = [
                'label' => $label,
                'done' => !empty($task['done']),
            ];
        }

        $total = count($normalized);
        if ($total === 0) {
            return 0;
        }

        $doneCount = 0;
        foreach ($normalized as $task) {
            if (!empty($task['done'])) {
                $doneCount++;
            }
        }

        return (int)round(($doneCount / $total) * 100);
    }

    /**
     * Auto-generate tasks based on intervention type
     */
    public function generateTasksForType(string $type): array
    {
        $tasksByType = [
            'route' => [
                'Évaluer l\'état de la route',
                'Délimiter la zone de travail',
                'Préparer les matériaux',
                'Effectuer les réparations',
                'Tester et valider',
            ],
            'eclairage' => [
                'Couper l\'alimentation électrique',
                'Inspecter l\'équipement',
                'Remplacer ou réparer',
                'Tester le fonctionnement',
                'Rétablir l\'alimentation',
            ],
            'eau' => [
                'Localiser la fuite',
                'Couper l\'eau au besoin',
                'Accéder à la canalisation',
                'Effectuer la réparation',
                'Tester l\'étanchéité',
            ],
            'transport' => [
                'Inspecter l\'infrastructure',
                'Identifier les problèmes',
                'Planifier l\'intervention',
                'Effectuer les travaux',
                'Vérifier la fonctionnalité',
            ],
            'ordures' => [
                'Évaluer l\'accumulation',
                'Organiser la collecte',
                'Effectuer le nettoyage',
                'Évacuer les décombres',
                'Vérifier la propreté',
            ],
            'autre' => [
                'Évaluer la situation',
                'Planifier l\'intervention',
                'Rassembler les ressources',
                'Effectuer les travaux',
                'Tester et valider le résultat',
            ],
        ];

        $tasks = $tasksByType[$type] ?? $tasksByType['autre'] ?? [];
        
        return array_map(function($label) {
            return [
                'label' => $label,
                'done' => false,
            ];
        }, array_merge($tasks, [
            'Documenter l\'intervention',
            'Fermer les tickets associés',
        ]));
    }

    /**
     * Generate tasks using AI via Hugging Face free API
     * Falls back to template tasks if API fails
     */
    public function generateTasksWithAI(string $type, string $titre, string $description): array
    {
        try {
            $tasks = $this->generateTasksViaHuggingFace($type, $titre, $description);
            if (!empty($tasks)) {
                return $tasks;
            }
        } catch (Throwable $e) {
            // Silently fall back to template tasks
        }

        // Fallback to a dynamic plan based on description + type (not fixed length).
        return $this->generateTasksHeuristic($type, $titre, $description);
    }

    /**
     * Call Hugging Face Inference API to generate tasks
     * Uses free Mistral 7B model
     */
    private function generateTasksViaHuggingFace(string $type, string $titre, string $description): array
    {
        // Free HF API endpoint - no auth required for inference
        $apiUrl = 'https://api-inference.huggingface.co/models/mistralai/Mistral-7B-Instruct-v0.1';
        
        $prompt = "Tu es un expert en gestion d'intervention municipale. Génère entre 4 et 9 tâches spécifiques et pratiques pour cette intervention:\n"
            . "Type: $type\n"
            . "Titre: $titre\n"
            . "Description: $description\n\n"
            . "Format de réponse: une tâche par ligne, sans explications, sans numérotation.";

        $payload = [
            'inputs' => $prompt,
            'parameters' => [
                'max_new_tokens' => 150,
                'temperature' => 0.7,
            ],
        ];

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return [];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded) || empty($decoded[0]['generated_text'] ?? '')) {
            return [];
        }

        $generatedText = $decoded[0]['generated_text'];
        
        // Extract tasks from response and remove common bullet/number prefixes.
        $lines = array_values(array_filter(array_map(static function ($line) {
            $clean = trim((string)$line);
            $clean = preg_replace('/^[-*•\d\.)\s]+/u', '', $clean) ?? $clean;
            return trim($clean);
        }, explode("\n", $generatedText)), static fn($line) => $line !== '' && mb_strlen($line) >= 5));

        // Keep only a reasonable number of unique lines.
        $unique = [];
        foreach ($lines as $line) {
            if (!in_array($line, $unique, true)) {
                $unique[] = $line;
            }
        }

        // In many responses, model echoes the prompt first; keep trailing candidate lines.
        $aiTasks = array_slice($unique, -9);
        if (count($aiTasks) < 4) {
            return [];
        }

        return array_map(static function ($label) {
            return [
                'label' => trim((string)$label),
                'done' => false,
            ];
        }, $aiTasks);
    }

    private function generateTasksHeuristic(string $type, string $titre, string $description): array
    {
        $baseLabels = array_map(static fn(array $t) => (string)($t['label'] ?? ''), $this->generateTasksForType($type));

        $haystack = mb_strtolower(trim($titre . ' ' . $description));
        $extra = [];

        // Water/Plumbing tasks
        if (preg_match('/fuite|eau|canal|canalisation|pression|robinet|tuyau|evacuation/u', $haystack)) {
            $waterTasks = [
                'Isoler la section concernée pour sécuriser le réseau',
                'Vérifier l\'absence de fuite secondaire après réparation',
                'Mesurer le débit avant et après intervention',
                'Contrôler le pH et la qualité de l\'eau si applicable',
                'Remplir les tuyauteries et purger l\'air',
            ];
            $extra = array_merge($extra, array_rand(array_flip($waterTasks), min(2, count($waterTasks))));
        }

        // Road/Pavement tasks
        if (preg_match('/nid|poule|route|chauss|asphalte|trottoir|nid-de-poule|bordure|marquage/u', $haystack)) {
            $roadTasks = [
                'Sécuriser la circulation et poser une signalisation temporaire',
                'Contrôler la planéité et la tenue du revêtement',
                'Nettoyer les débris et préparer la surface de travail',
                'Vérifier l\'adhérence après remplissage et lissage',
                'Redessiner les marquages au sol si nécessaire',
            ];
            $selected = array_rand(array_flip($roadTasks), min(2, count($roadTasks)));
            $extra = array_merge($extra, is_array($selected) ? $selected : [$selected]);
        }

        // Lighting/Electrical tasks
        if (preg_match('/eclair|lampe|poteau|electri|cable|disjoncteur|transformateur|batterie/u', $haystack)) {
            $electricTasks = [
                'Mesurer la tension et vérifier les protections électriques',
                'Valider l\'éclairage en conditions nocturnes',
                'Tester les relais et capteurs de lumière',
                'Vérifier l\'isolation et l\'absence de surcharge',
                'Documenter les relevés de tension et d\'intensité',
            ];
            $selected = array_rand(array_flip($electricTasks), min(2, count($electricTasks)));
            $extra = array_merge($extra, is_array($selected) ? $selected : [$selected]);
        }

        // Waste/Cleanliness tasks
        if (preg_match('/ordure|dechet|debris|insalubre|nettoy|poubelle|encombrant|amas|detritus/u', $haystack)) {
            $wasteTasks = [
                'Contrôler la zone après enlèvement pour éviter la récidive',
                'Vérifier les conditions d\'hygiène et sanitaires',
                'Organiser le tri et le recyclage des matériaux',
                'Nettoyer les traces et résidus après intervention',
                'Installer une signalisation si besoin pour éviter le rebut',
            ];
            $selected = array_rand(array_flip($wasteTasks), min(2, count($wasteTasks)));
            $extra = array_merge($extra, is_array($selected) ? $selected : [$selected]);
        }

        // Emergency/Safety tasks
        if (preg_match('/urgence|danger|risque|accident|blessure|securite|urgent/u', $haystack)) {
            $safeTasks = [
                'Prioriser l\'intervention en mode urgence et informer les équipes',
                'Établir un périmètre de sécurité autour de la zone',
                'Vérifier qu\'aucun piéton ne sera exposé à un danger',
                'Documenter tous les risques observés',
            ];
            $selected = array_rand(array_flip($safeTasks), min(2, count($safeTasks)));
            $extra = array_merge($extra, is_array($selected) ? $selected : [$selected]);
        }

        // Transport/Traffic tasks
        if (preg_match('/tram|bus|transport|circulation|trafic|carrefour|feu/u', $haystack)) {
            $transportTasks = [
                'Coordonner avec les services de transport et circulation',
                'Mettre en place des itinéraires alternatifs si besoin',
                'Vérifier la fluidité du trafic pendant l\'intervention',
                'Restaurer les marquages et signalisations de circulation',
            ];
            $selected = array_rand(array_flip($transportTasks), min(2, count($transportTasks)));
            $extra = array_merge($extra, is_array($selected) ? $selected : [$selected]);
        }

        // Combine base + extra
        $labels = array_merge($baseLabels, $extra);
        $labels = array_values(array_filter(array_map('trim', $labels), static fn($l) => $l !== ''));
        $labels = array_values(array_unique($labels));

        // Variable length based on hash of content
        $seedText = mb_strtolower(trim($type . '|' . $titre . '|' . $description));
        $targetCount = 5 + ((int)(abs(crc32($seedText)) % 6));

        // Diverse generic pool with many variants
        $genericPool = [
            'Préparer les équipements de sécurité (casques, gilets, gants)',
            'Informer les riverains des impacts temporaires',
            'Coordonner l\'ordre de passage des équipes sur site',
            'Contrôler la qualité finale avec un responsable terrain',
            'Mettre à jour le rapport d\'intervention dans le système',
            'Obtenir les autorisations et permis nécessaires',
            'Vérifier la disponibilité des matériaux et ressources',
            'Briefer l\'équipe sur les procédures de sécurité',
            'Photodocumenter l\'avant et l\'après intervention',
            'Collecter les signatures de validation du client/responsable',
            'Planifier les étapes clés et les jalons',
            'Vérifier les conditions météorologiques',
            'Évaluer les sous-traitants ou partenaires requis',
            'Tester tous les équipements avant utilisation',
            'Nettoyer et ranger les lieux après intervention',
        ];

        // Mix generic with what we have
        $available = array_filter($genericPool, static fn($g) => !in_array($g, $labels, true));
        
        while (count($labels) < $targetCount && !empty($available)) {
            $idx = array_rand($available);
            $labels[] = $available[$idx];
            unset($available[$idx]);
            $available = array_values($available);
        }

        if (count($labels) > $targetCount) {
            $labels = array_slice($labels, 0, $targetCount);
        }

        return array_map(static function ($label) {
            return [
                'label' => $label,
                'done' => false,
            ];
        }, $labels);
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

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO interventions (titre, description, type, tasks_json, latitude, longitude, statut, progression, date_intervention, signalement_id)
             VALUES (:titre, :description, :type, :tasks_json, :latitude, :longitude, :statut, :progression, :date_intervention, :signalement_id)'
        );

        $ok = $stmt->execute([
            ':titre' => $data['titre'],
            ':description' => $data['description'],
            ':type' => $data['type'],
            ':tasks_json' => $data['tasks_json'] !== '' ? $data['tasks_json'] : null,
            ':latitude' => $data['latitude'],
            ':longitude' => $data['longitude'],
            ':statut' => $data['statut'],
            ':progression' => $data['progression'],
            ':date_intervention' => $data['date_intervention'] !== '' ? $data['date_intervention'] : null,
            ':signalement_id' => $data['signalement_id'] ?? null,
        ]);

        return $ok ? (int)$this->pdo->lastInsertId() : 0;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE interventions
             SET titre = :titre,
                 description = :description,
                 type = :type,
                 tasks_json = :tasks_json,
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
            ':tasks_json' => $data['tasks_json'] !== '' ? $data['tasks_json'] : null,
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
        $sql = 'SELECT id, titre, description, type, tasks_json, latitude, longitude, statut, progression, date_intervention, created_at
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
