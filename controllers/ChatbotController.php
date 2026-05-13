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
        
        // Charger la clé API
        require_once __DIR__ . '/../config/config.php';
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
        $quick_suggestions = $this->getServiceSuggestions();
        include __DIR__ . '/../views/chatbot/widget.php';
    }

    /**
     * Construit les suggestions depuis services_en_ligne + questions générales
     */
    private function getServiceSuggestions(): array {
        $suggestions = [];
        try {
            $sql = "SELECT nom FROM services_en_ligne ORDER BY nom ASC LIMIT 4";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($services as $s) {
                $suggestions[] = "📄 " . $s['nom'] . " — documents requis ?";
            }
        } catch (Exception $e) {
            // fallback
        }
        $suggestions[] = "🕐 Quels sont les horaires de la mairie ?";
        $suggestions[] = "📞 Comment contacter la mairie ?";
        if (empty($suggestions)) {
            return $this->model->getSuggestions();
        }
        return $suggestions;
    }

    /**
     * Envoie un message et retourne la réponse IA
     */
    public function sendMessage() {
        // Discard any PHP warnings/notices buffered before this call
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ini_set('display_errors', '0');
        header('Content-Type: application/json; charset=utf-8');

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
Tu connais tous les services municipaux en ligne, les démarches administratives, horaires, contacts et événements.
Quand un utilisateur demande un service, indique-lui les documents requis et comment soumettre une demande via la plateforme (bouton 'Soumettre une demande').
Réponds en français, avec des émojis, de façon amicale et concise (max 3-4 phrases).

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
     * Construit le contexte (services_en_ligne depuis BDD + événements)
     */
    private function getFullContext() {
        $txt = "";

        // Services en ligne depuis la base de données
        $txt .= "📌 **SERVICES EN LIGNE DISPONIBLES** :\n";
        try {
            $sql = "SELECT nom, description, documents_requis FROM services_en_ligne ORDER BY nom ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($services) {
                foreach ($services as $s) {
                    $txt .= "• **{$s['nom']}** : {$s['description']}";
                    if (!empty($s['documents_requis'])) {
                        $txt .= " (Documents requis : {$s['documents_requis']})";
                    }
                    $txt .= "\n";
                }
            } else {
                $txt .= "Aucun service en ligne disponible pour le moment.\n";
            }
        } catch (Exception $e) {
            $txt .= "Impossible de charger les services.\n";
        }

        // Infos générales
        $txt .= "\n📋 **INFORMATIONS GÉNÉRALES** :\n";
        $txt .= "• Horaires : Lundi-Vendredi 8h30-17h, Samedi 9h-12h.\n";
        $txt .= "• Contact : Tél : 01 23 45 67 89 | Email : contact@smartmunicipality.com\n";
        $txt .= "• Pour soumettre une demande, l'utilisateur clique sur 'Soumettre une demande' dans l'onglet Services en ligne.\n";

        // Événements depuis la base
        $txt .= "\n🎉 **ÉVÉNEMENTS À VENIR** :\n";
        try {
            $sql = "SELECT titre, lieu, date_evenement FROM evenements ORDER BY date_evenement DESC LIMIT 6";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($events) {
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