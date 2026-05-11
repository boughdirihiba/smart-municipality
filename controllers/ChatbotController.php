<?php
// controllers/ChatbotController.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/chatbot.php';

class ChatbotController {

    private $model;
    private $db;
    private $apiKey;
    private $apiUrl;
    private $modelName; // nom du modèle IA (ex: llama-3.1-8b-instant)

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->model = new Chatbot($this->db);
        
        // Charger la clé API depuis le fichier renommé
        require_once __DIR__ . '/../config/config1.php';
        $this->apiKey = GROK_API_KEY;
        
        if (strpos($this->apiKey, 'gsk_') === 0) {
            $this->modelName = GROQ_MODEL;
            $this->apiUrl = "https://api.groq.com/openai/v1/chat/completions";
        } else {
            $this->modelName = 'grok-3-mini';
            $this->apiUrl = "https://api.x.ai/v1/chat/completions";
        }
    }

    /**
     * Affiche le widget
     */
    public function widget() {
        $quick_suggestions = $this->model->getSuggestions();
        include __DIR__ . '/../views/chatbot/widget.php';
    }

    /**
     * Envoie un message et retourne la réponse IA
     */
    public function sendMessage() {
        header('Content-Type: application/json');
        
        // Lire le message (POST x-www-form-urlencoded ou JSON)
        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            $input = json_decode(file_get_contents('php://input'), true);
            $message = trim($input['message'] ?? '');
        }
        
        if (!empty($message)) {
            $response = $this->callAI($message);
            echo json_encode([
                'success' => true,
                'response' => $response,
                'timestamp' => date('H:i')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'response' => 'Message vide'
            ]);
        }
        exit;
    }

    /**
     * Appel à l'API IA avec contexte (services + événements)
     */
    private function callAI($userMessage) {
        if (empty($this->apiKey)) {
            return "🔑 Service IA non configuré. Contactez l'administrateur.";
        }
        
        $context = $this->getFullContext();
        
        $systemPrompt = "Tu es l'assistant officiel de la mairie Smart Municipality.
Tu connais tous les services municipaux, les démarches, horaires, contacts, événements.
Réponds en français, avec des émojis, de façon amicale et concise.

Voici les informations à ta disposition :
$context
";
        
        $data = [
            'model' => $this->modelName,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage]
            ],
            'temperature' => 0.7,
            'max_tokens' => 500
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false || $httpCode !== 200) {
            return "⚠️ Problème technique, réessayez plus tard.";
        }
        
        $result = json_decode($response, true);
        if (isset($result['choices'][0]['message']['content'])) {
            return $result['choices'][0]['message']['content'];
        }
        
        return "Je n'ai pas compris. Reformulez votre question.";
    }

    /**
     * Construit le contexte (services fixes + événements depuis BDD)
     */
    private function getFullContext() {
        // Services fixes
        $services = [
            "**État civil**" => "Acte de mariage (gratuit), extrait de naissance (gratuit), acte de décès.",
            "**Pièces d'identité**" => "Carte nationale d'identité (25€, délai 2-3 semaines), Passeport (86€, délai 2-4 semaines).",
            "**Permis de conduire**" => "Inscription sur ants.gouv.fr.",
            "**Horaires**" => "Lundi-Vendredi 8h30-17h, Samedi 9h-12h.",
            "**Contact**" => "Tél : 01 23 45 67 89 | Email : contact@smartmunicipality.com",
            "**Démarches en ligne**" => "https://smart-municipality.fr/services"
        ];
        $txt = "📌 **SERVICES MUNICIPAUX** :\n";
        foreach ($services as $cat => $desc) {
            $txt .= "• $cat : $desc\n";
        }
        
        // Événements depuis la base
        $txt .= "\n🎉 **ÉVÉNEMENTS À VENIR** :\n";
        try {
            $sql = "SELECT titre, lieu, date_evenement FROM evenements WHERE date_evenement >= CURDATE() ORDER BY date_evenement ASC LIMIT 6";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($events) > 0) {
                foreach ($events as $e) {
                    $dateFmt = date('d/m/Y', strtotime($e['date_evenement']));
                    $txt .= "• {$e['titre']} – le $dateFmt à {$e['lieu']}\n";
                }
            } else {
                $txt .= "Aucun événement planifié pour le moment.\n";
            }
        } catch (Exception $e) {
            $txt .= "Impossible de charger les événements.\n";
        }
        
        return $txt;
    }

    /**
     * Retourne les suggestions (utilisé par le widget)
     */
    public function getSuggestions() {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'suggestions' => $this->model->getSuggestions()
        ]);
        exit;
    }
    
    // Méthodes CRUD vides (compatibilité)
    public function getHistory() {
        header('Content-Type: application/json');
        echo json_encode([]);
        exit;
    }
    
    public function deleteHistory() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
?>