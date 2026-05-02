<?php
require_once "models/chatbot.php";
require_once "config/database.php";

class ChatbotController {

    private $model;

    public function __construct() {
        $database = new Database();
        $db = $database->connect();
        $this->model = new Chatbot($db);
    }

    public function widget() {
        $quick_suggestions = $this->getSuggestionsList();
        include "views/chatbot/widget.php";
    }

    public function sendMessage() {
        header('Content-Type: application/json');
        
        if(isset($_POST['message']) && !empty(trim($_POST['message']))) {
            $message = trim($_POST['message']);
            $response = $this->getResponse($message);
            
            echo json_encode([
                'success' => true,
                'response' => $response,
                'timestamp' => date('H:i')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'response' => "Veuillez saisir un message"
            ]);
        }
        exit;
    }

    public function getSuggestions() {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'suggestions' => $this->getSuggestionsList()
        ]);
        exit;
    }
    
    // Toutes les réponses sont ici
    private function getResponse($message) {
        $message = strtolower(trim($message));
        
        // Acte de mariage
        if(strpos($message, 'mariage') !== false || strpos($message, 'acte mariage') !== false) {
            return "💍 **Acte de Mariage**\n\n📄 Obtenable gratuitement :\n• En ligne sur notre site\n• En mairie sur présentation d'une pièce d'identité\n\n⏱️ Délai : Immédiat en ligne";
        }
        
        // Carte d'identité
        if(strpos($message, 'carte') !== false || strpos($message, 'identite') !== false) {
            return "📋 **Carte d'Identité**\n\n📄 Documents : extrait naissance, justificatif domicile, 2 photos, timbre 25€\n⏱️ Délai : 2-3 semaines";
        }
        
        // Passeport
        if(strpos($message, 'passeport') !== false) {
            return "🛂 **Passeport**\n\n📄 Documents : CNI, justificatif domicile, 2 photos, timbre 86€\n⏱️ Délai : 2-4 semaines";
        }
        
        // Naissance
        if(strpos($message, 'naissance') !== false) {
            return "👶 **Extrait de naissance** : Gratuit en ligne ou en mairie";
        }
        
        // Permis
        if(strpos($message, 'permis') !== false) {
            return "🚗 **Permis de conduire** : Inscription sur ants.gouv.fr";
        }
        
        // Horaires
        if(strpos($message, 'horaire') !== false) {
            return "🕐 **Horaires** : Lun-Ven 8h30-17h, Sam 9h-12h";
        }
        
        // Contact
        if(strpos($message, 'contact') !== false || strpos($message, 'telephone') !== false) {
            return "📞 **Contact** : 01 23 45 67 89 / contact@smartmunicipality.com";
        }
        
        // Bonjour
        if(strpos($message, 'bonjour') !== false || strpos($message, 'salut') !== false) {
            return "Bonjour ! Comment puis-je vous aider ?";
        }
        
        // Merci
        if(strpos($message, 'merci') !== false) {
            return "Avec plaisir ! 😊";
        }
        
        // Au revoir
        if(strpos($message, 'au revoir') !== false) {
            return "Au revoir ! 👋";
        }
        
        // Réponse par défaut
        return "Je n'ai pas compris.\n\n💡 Questions possibles :\n• Acte de mariage\n• Carte d'identité\n• Passeport\n• Extrait de naissance\n• Permis de conduire\n• Horaires\n• Contact";
    }
    
    private function getSuggestionsList() {
        return [
            "Acte de mariage",
            "Carte d'identité",
            "Passeport",
            "Extrait de naissance",
            "Permis de conduire",
            "Horaires",
            "Contact"
        ];
    }
    
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