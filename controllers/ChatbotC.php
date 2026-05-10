<?php
require_once __DIR__ . '/../models/Chatbot.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/ParticipationC.php';
require_once __DIR__ . '/EvenementC.php';
require_once __DIR__ . '/CategorieEvenementC.php';

class ChatbotC {
    
    private $chatbot;
    private $participationC;
    private $evenementC;
    private $categorieC;
    private $apiKey;
    private $apiUrl;
    private $grokModel;
    private $provider;
    private $lastAiError = null;
    
    // Mots-clés pour l'analyse NLP (fallback)
    private $dateKeywords = [
        'aujourd\'hui' => 'today',
        "aujourd'hui" => 'today',
        'ce soir' => 'today',
        'maintenant' => 'today',
        'demain' => 'tomorrow',
        'cette semaine' => 'week',
        'ce weekend' => 'weekend',
        'ce week-end' => 'weekend',
        'ce mois' => 'month'
    ];
    
    private $categoryKeywords = [
        'culture' => ['culture', 'concert', 'musique', 'théâtre', 'exposition', 'art', 'cinéma', 'spectacle', 'festival'],
        'sport' => ['sport', 'football', 'foot', 'basket', 'tennis', 'match', 'tournoi', 'course', 'athlétisme', 'vélo'],
        'environnement' => ['environnement', 'nature', 'écologie', 'jardinage', 'nettoyage', 'plante', 'recyclage', 'vert'],
        'social' => ['social', 'rencontre', 'atelier', 'solidaire', 'association', 'bénévolat', 'partage'],
        'technologie' => ['technologie', 'tech', 'innovation', 'robotique', 'hackathon', 'numérique', 'informatique']
    ];
    
    public function __construct() {
        $this->chatbot = new Chatbot();
        $this->participationC = new ParticipationC();
        $this->evenementC = new EvenementC();
        $this->categorieC = new CategorieEvenementC();
        
        // Configuration IA (env > constantes PHP)
        $this->apiKey = getenv('GROK_API_KEY') ?: (defined('GROK_API_KEY') ? GROK_API_KEY : '');
        $this->provider = (strpos($this->apiKey, 'gsk_') === 0) ? 'groq' : 'xai';

        if ($this->provider === 'groq') {
            $this->grokModel = getenv('GROQ_MODEL') ?: (defined('GROQ_MODEL') ? GROQ_MODEL : 'llama-3.1-8b-instant');
            $this->apiUrl = "https://api.groq.com/openai/v1/chat/completions";
        } else {
            $this->grokModel = getenv('GROK_MODEL') ?: (defined('GROK_MODEL') ? GROK_MODEL : 'grok-3-mini');
            $this->apiUrl = "https://api.x.ai/v1/chat/completions";
        }
    }
    
    /**
     * ANALYSER L'INTENTION DU MESSAGE (NLP maison)
     */
    public function analyserIntention($message) {
        $message = strtolower(trim($message));
        
        // Voir mes événements
        if (preg_match('/(mes|mes événements|mes inscriptions|mes participations|mes activités)/i', $message)) {
            return ['action' => 'my_events', 'params' => []];
        }
        
        // Inscription
        if (preg_match('/inscrire|s\'inscrire|participer|je veux participer/i', $message)) {
            if (preg_match('/(\d+)/', $message, $matches)) {
                return ['action' => 'subscribe', 'params' => ['event_id' => $matches[1]]];
            }
            return ['action' => 'ask_event_id', 'params' => []];
        }
        
        // Numéro simple (inscription rapide)
        if (preg_match('/^(\d+)$/', $message, $matches)) {
            return ['action' => 'subscribe', 'params' => ['event_id' => $matches[1]]];
        }
        
        // Aide
        if (preg_match('/aide|help|bonjour|salut|coucou|hello/i', $message) && strlen($message) < 20) {
            return ['action' => 'help', 'params' => []];
        }
        
        // Détecter la date
        $date = null;
        foreach ($this->dateKeywords as $key => $value) {
            if (strpos($message, $key) !== false) {
                $date = $value;
                break;
            }
        }
        
        // Détecter une date spécifique (ex: "15 mai")
        if (!$date && preg_match('/(\d{1,2})\s+(janvier|février|mars|avril|mai|juin|juillet|août|septembre|octobre|novembre|décembre)/i', $message, $matches)) {
            $mois = [
                'janvier' => '01', 'février' => '02', 'mars' => '03', 'avril' => '04',
                'mai' => '05', 'juin' => '06', 'juillet' => '07', 'août' => '08',
                'septembre' => '09', 'octobre' => '10', 'novembre' => '11', 'décembre' => '12'
            ];
            $moisNum = $mois[strtolower($matches[2])];
            $annee = date('Y');
            $dateSpecifique = date('Y-m-d', strtotime("$annee-$moisNum-{$matches[1]}"));
            if ($dateSpecifique < date('Y-m-d')) {
                $dateSpecifique = date('Y-m-d', strtotime("+" . ($annee+1) . "-$moisNum-{$matches[1]}"));
            }
            return ['action' => 'search', 'params' => ['specific_date' => $dateSpecifique]];
        }
        
        // Détecter la catégorie
        $category = null;
        foreach ($this->categoryKeywords as $cat => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $category = $cat;
                    break 2;
                }
            }
        }
        
        // Détecter un mot-clé de recherche général
        $searchTerm = null;
        $stopWords = ['pour', 'avec', 'dans', 'sur', 'les', 'des', 'une', 'est', 'que', 'qui', 'quoi', 'comment', 'pourquoi', 'ou', 'où'];
        $words = explode(' ', $message);
        foreach ($words as $word) {
            if (strlen($word) > 3 && !in_array($word, $stopWords) && !isset($this->dateKeywords[$word]) && !$this->isCategory($word)) {
                $searchTerm = $word;
                break;
            }
        }
        
        $isEventQuery = preg_match('/\b(événement|événements|event|events|activité|activités|sortie|programme|agenda|inscription|participer)\b/u', $message);
        if ($date || $category || ($searchTerm && $isEventQuery)) {
            return [
                'action' => 'search',
                'params' => [
                    'date' => $date,
                    'category' => $category,
                    'search' => $searchTerm
                ]
            ];
        }
        
        // Si aucune intention détectée, utiliser Gemini ou réponse par défaut
        return ['action' => 'chat', 'params' => []];
    }
    
    private function isCategory($word) {
        foreach ($this->categoryKeywords as $keywords) {
            if (in_array($word, $keywords)) return true;
        }
        return false;
    }
    
    /**
     * RECHERCHER DES ÉVÉNEMENTS
     */
    public function rechercherEvenements($criteria) {
        $db = config::getConnexion();
        $sql = "
            SELECT e.*, c.nom as categorie_nom,
                   COALESCE(SUM(CASE WHEN p.statut_validation = 'valide' THEN p.nombre_participants ELSE 0 END), 0) as places_prises
            FROM evenements e
            LEFT JOIN categorie_evenement c ON e.categorie_id = c.id
            LEFT JOIN participations p ON e.id = p.event_id
            WHERE e.date_evenement >= CURDATE()
        ";
        $params = [];
        
        if (isset($criteria['specific_date'])) {
            $sql .= " AND e.date_evenement = :specific_date";
            $params['specific_date'] = $criteria['specific_date'];
        } elseif (isset($criteria['date']) && $criteria['date']) {
            switch ($criteria['date']) {
                case 'today':
                    $sql .= " AND e.date_evenement = CURDATE()";
                    break;
                case 'tomorrow':
                    $sql .= " AND e.date_evenement = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
                    break;
                case 'week':
                    $sql .= " AND e.date_evenement BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
                    break;
                case 'weekend':
                    $sql .= " AND DAYOFWEEK(e.date_evenement) IN (1, 7)";
                    break;
                case 'month':
                    $sql .= " AND e.date_evenement BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
                    break;
            }
        }
        
        if (isset($criteria['category']) && $criteria['category']) {
            $sql .= " AND c.nom LIKE :category";
            $params['category'] = '%' . $criteria['category'] . '%';
        }
        
        if (isset($criteria['search']) && $criteria['search']) {
            $sql .= " AND (e.titre LIKE :search OR e.description LIKE :search OR e.lieu LIKE :search)";
            $params['search'] = '%' . $criteria['search'] . '%';
        }
        
        $sql .= " GROUP BY e.id ORDER BY e.date_evenement ASC LIMIT 10";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * RÉCUPÉRER UN ÉVÉNEMENT PAR ID
     */
    public function getEventById($id) {
        $db = config::getConnexion();
        $query = "
            SELECT e.*, c.nom as categorie_nom,
                   COALESCE(SUM(CASE WHEN p.statut_validation = 'valide' THEN p.nombre_participants ELSE 0 END), 0) as places_prises
            FROM evenements e
            LEFT JOIN categorie_evenement c ON e.categorie_id = c.id
            LEFT JOIN participations p ON e.id = p.event_id
            WHERE e.id = :id
            GROUP BY e.id
        ";
        $stmt = $db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * RÉCUPÉRER LES PARTICIPATIONS D'UN UTILISATEUR
     */
    public function getUserParticipations($userId) {
        $db = config::getConnexion();
        $query = "
            SELECT p.*, e.titre, e.lieu, e.date_evenement, e.heure, c.nom as categorie_nom,
                   p.statut_validation
            FROM participations p
            JOIN evenements e ON p.event_id = e.id
            LEFT JOIN categorie_evenement c ON e.categorie_id = c.id
            WHERE p.user_id = :user_id
            ORDER BY e.date_evenement DESC
        ";
        $stmt = $db->prepare($query);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * VÉRIFIER SI L'UTILISATEUR EST INSCRIT
     */
    public function isUserRegistered($userId, $eventId) {
        $db = config::getConnexion();
        $stmt = $db->prepare("SELECT COUNT(*) FROM participations WHERE user_id = :user_id AND event_id = :event_id");
        $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * INSCRIRE UN UTILISATEUR
     */
    public function inscrireUtilisateur($userId, $eventId, $nbParticipants = 1) {
        $db = config::getConnexion();
        $event = $this->getEventById($eventId);
        
        if (!$event) {
            return ['success' => false, 'message' => "❌ Événement non trouvé."];
        }
        
        if ($this->isUserRegistered($userId, $eventId)) {
            return ['success' => false, 'message' => "✅ Vous êtes déjà inscrit à **{$event['titre']}** !"];
        }
        
        $placesRestantes = $event['max_participants'] - $event['places_prises'];
        if ($placesRestantes <= 0) {
            return ['success' => false, 'message' => "❌ L'événement **{$event['titre']}** est complet !"];
        }
        
        $stmt = $db->prepare("
            INSERT INTO participations (user_id, event_id, date_participation, statut, statut_validation, nombre_participants)
            VALUES (:user_id, :event_id, NOW(), 'inscrit', 'en_attente', :nombre_participants)
        ");
        $result = $stmt->execute([
            'user_id' => $userId,
            'event_id' => $eventId,
            'nombre_participants' => $nbParticipants
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => "✅ Inscription réussie à **{$event['titre']}** !"];
        }
        
        return ['success' => false, 'message' => "❌ Erreur lors de l'inscription."];
    }
    
    /**
     * GÉNÉRER UNE RÉPONSE AVEC GROK
     */
    public function genererReponseAvecGemini($message) {
        $this->lastAiError = null;

        if (empty($this->apiKey)) {
            $this->lastAiError = "Clé API manquante.";
            return null;
        }

        $eventsContext = $this->getEventsContextForGemini();
        
        $systemPrompt = "Tu es un assistant pour Smart Municipality. Réponds en français, de façon amicale et concise. Utilise des emojis. Voici les événements disponibles:\n$eventsContext";
        
        $data = [
            'model' => $this->grokModel,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message]
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
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($response === false) {
            $this->lastAiError = "Erreur cURL: " . $curlError;
            return null;
        }

        $result = json_decode($response, true);

        if ($httpCode == 200) {
            if (isset($result['choices'][0]['message']['content'])) {
                return $result['choices'][0]['message']['content'];
            }
            $this->lastAiError = "Réponse IA invalide (structure inattendue).";
            return null;
        }

        $apiError = 'Erreur inconnue';
        if (isset($result['error'])) {
            if (is_array($result['error'])) {
                $apiError = $result['error']['message'] ?? json_encode($result['error']);
            } elseif (is_string($result['error'])) {
                $apiError = $result['error'];
            }
        } elseif (is_array($result) && isset($result['message']) && is_string($result['message'])) {
            $apiError = $result['message'];
        }

        $this->lastAiError = "HTTP $httpCode ({$this->provider}) - $apiError";
        return null;
    }
    
    /**
     * Récupérer le contexte des événements pour grok
     */
    private function getEventsContextForGemini() {
        $events = $this->rechercherEvenements([]);
        $context = "";
        foreach (array_slice($events, 0, 10) as $e) {
            $placesRestantes = $e['max_participants'] - $e['places_prises'];
            $context .= "- ID:{$e['id']} | {$e['titre']} | {$e['lieu']} | " . date('d/m/Y', strtotime($e['date_evenement'])) . " | Places: $placesRestantes\n";
        }
        return $context;
    }
    
    /**
     * FORMER LA RÉPONSE DES ÉVÉNEMENTS
     */
    public function formaterReponseEvenements($events) {
        if (empty($events)) {
            return "😊 Je n'ai trouvé aucun événement correspondant.\n\n📝 Suggestions :\n• Tapez \"événements\"\n• \"sport\"\n• \"ce weekend\"\n• \"culture\"";
        }
        
        $response = "🎉 Voici les événements trouvés :\n\n";
        foreach ($events as $index => $e) {
            $placesRestantes = $e['max_participants'] - $e['places_prises'];
            $response .= "**" . ($index + 1) . "️⃣ {$e['titre']}**\n";
            $response .= "   📍 {$e['lieu']}\n";
            $response .= "   📅 " . date('d/m/Y', strtotime($e['date_evenement'])) . " à {$e['heure']}\n";
            $response .= "   🏷️ {$e['categorie_nom']}\n";
            $response .= "   👥 {$placesRestantes}/{$e['max_participants']} places\n\n";
        }
        $response .= "💡 Tapez le numéro pour vous inscrire !";
        return $response;
    }
    
    /**
     * MESSAGE D'AIDE
     */
    public function getHelpMessage() {
        return "🤖 **Assistant Smart Municipality**\n\n"
             . "Je peux vous aider à :\n"
             . "• 📅 **Trouver des événements** : \"événements aujourd'hui\", \"sport\"\n"
             . "• ✅ **Vous inscrire** : tapez le numéro de l'événement\n"
             . "• 📋 **Voir vos inscriptions** : \"mes événements\"\n\n"
             . "🔍 **Exemples :**\n"
             . "• \"Événements ce weekend\"\n"
             . "• \"Culture\"\n"
             . "• \"15 mai\"\n\n"
             . "💡 Que puis-je faire pour vous ?";
    }
    
    /**
     * GÉRER L'INSCRIPTION PAR NUMÉRO
     */
    private function handleSubscriptionByNumber($number, $userId, $lastEvents) {
        $index = intval($number) - 1;
        if (isset($lastEvents[$index])) {
            $event = $lastEvents[$index];
            return $this->inscrireUtilisateur($userId, $event['id']);
        }
        return ['success' => false, 'message' => "❌ Numéro invalide."];
    }
    
    /**
     * TRAITER LE MESSAGE PRINCIPAL
     */
    public function traiterMessage($message, $userId = null) {
        $message = trim($message);
        $lastEvents = isset($_SESSION['chatbot_last_events']) ? $_SESSION['chatbot_last_events'] : [];
        
        // Vérifier si c'est un numéro pour s'inscrire
        if (preg_match('/^(\d+)$/', $message, $matches) && !empty($lastEvents)) {
            $result = $this->handleSubscriptionByNumber($matches[1], $userId, $lastEvents);
            return ['success' => $result['success'], 'message' => $result['message']];
        }
        
        // Analyser l'intention
        $intention = $this->analyserIntention($message);
        
        // Traiter selon l'intention
        switch ($intention['action']) {
            case 'my_events':
                if (!$userId) {
                    return ['success' => false, 'message' => "🔐 Connectez-vous pour voir vos inscriptions.\n\n👉 [Se connecter](views/auth/login.php)"];
                }
                $events = $this->getUserParticipations($userId);
                if (empty($events)) {
                    return ['success' => true, 'message' => "📋 Vous n'êtes inscrit à aucun événement.\n\n👉 [Découvrir](index.php)"];
                }
                $response = "📋 **Vos inscriptions :**\n\n";
                foreach ($events as $e) {
                    $statusIcon = $e['statut_validation'] == 'valide' ? '✅' : ($e['statut_validation'] == 'en_attente' ? '⏳' : '❌');
                    $statusText = $e['statut_validation'] == 'valide' ? 'Validé' : ($e['statut_validation'] == 'en_attente' ? 'En attente' : 'Refusé');
                    $response .= "{$statusIcon} **{$e['titre']}**\n";
                    $response .= "   📍 {$e['lieu']} | 📅 " . date('d/m/Y', strtotime($e['date_evenement'])) . "\n";
                    $response .= "   📌 Statut: {$statusText}\n\n";
                }
                $response .= "👉 [Voir toutes mes participations](views/participation/mes_participations.php)";
                return ['success' => true, 'message' => $response];
                
            case 'subscribe':
                if (!$userId) {
                    return ['success' => false, 'message' => "🔐 Connectez-vous pour vous inscrire.\n\n👉 [Se connecter](views/auth/login.php)"];
                }
                if (isset($intention['params']['event_id'])) {
                    $result = $this->inscrireUtilisateur($userId, $intention['params']['event_id']);
                    return ['success' => $result['success'], 'message' => $result['message']];
                }
                return ['success' => false, 'message' => "📝 Quel événement ? Donnez l'ID ou le numéro."];
                
            case 'ask_event_id':
                if (empty($lastEvents)) {
                    $events = $this->rechercherEvenements([]);
                    $_SESSION['chatbot_last_events'] = $events;
                    $response = $this->formaterReponseEvenements($events);
                    return ['success' => true, 'message' => $response, 'events' => $events];
                }
                return ['success' => false, 'message' => "📝 Tapez le numéro de l'événement pour vous inscrire."];
                
            case 'search':
                $events = $this->rechercherEvenements($intention['params']);
                $_SESSION['chatbot_last_events'] = $events;
                $response = $this->formaterReponseEvenements($events);
                return ['success' => true, 'message' => $response, 'events' => $events];
                
            case 'help':
                return ['success' => true, 'message' => $this->getHelpMessage()];
                
            default:
                // Essayer Gemini
                $geminiResponse = $this->genererReponseAvecGemini($message);
                if ($geminiResponse) {
                    return ['success' => true, 'message' => $geminiResponse];
                }
                if (defined('CHATBOT_DEBUG') && CHATBOT_DEBUG && !empty($this->lastAiError)) {
                    return ['success' => false, 'message' => "Erreur IA: " . $this->lastAiError];
                }
                return ['success' => true, 'message' => $this->getHelpMessage()];
        }
    }
}
?>
