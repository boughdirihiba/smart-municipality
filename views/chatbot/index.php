<?php
session_start();
require_once __DIR__ . '/../../controllers/CategorieEvenementC.php';
require_once __DIR__ . '/../../controllers/EvenementC.php';

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$userName = isset($_SESSION['prenom']) ? $_SESSION['prenom'] . ' ' . $_SESSION['nom'] : 'Invité';
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'citoyen';

$categorieC = new CategorieEvenementC();
$evenementC = new EvenementC();
$categories = $categorieC->afficherCategories();
$tousEvenements = $evenementC->afficherEvenementsAVenir();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistant IA - Smart Municipality</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a5e2a;
            --primary-dark: #0d3b1a;
            --primary-light: #2e7d32;
            --secondary: #4caf50;
            --gradient: linear-gradient(135deg, #1a5e2a, #4caf50);
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
            --shadow-md: 0 5px 15px rgba(0,0,0,0.05);
            --radius: 12px;
            --radius-lg: 20px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f0;
            min-height: 100vh;
        }
        
        /* ========== NAVBAR MODERNE ========== */
        .navbar {
            background: white;
            box-shadow: var(--shadow-sm);
            padding: 0.75rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .nav-brand img {
            height: 35px;
            border-radius: 10px;
        }
        .nav-brand-text {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--primary);
        }
        .nav-brand-text span {
            color: var(--secondary);
        }
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 10px;
        }
        .mobile-toggle span {
            display: block;
            width: 25px;
            height: 2px;
            background: var(--primary);
            margin: 5px 0;
            transition: 0.3s;
        }
        .nav-links {
            display: flex;
            list-style: none;
            gap: 1.5rem;
            margin: 0;
            padding: 0;
        }
        .nav-links li a {
            text-decoration: none;
            color: #4a5568;
            font-weight: 500;
            transition: all 0.2s;
            padding: 0.5rem 0;
            position: relative;
        }
        .nav-links li a:hover {
            color: var(--primary);
        }
        .nav-links li a.active {
            color: var(--primary);
        }
        .nav-links li a.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary);
        }
        .nav-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .nav-search {
            display: flex;
            align-items: center;
            background: #f5f5f5;
            border-radius: 30px;
            padding: 6px 15px;
            gap: 8px;
        }
        .nav-search-icon {
            color: #999;
            font-size: 1rem;
        }
        .nav-search input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 0.8rem;
            width: 180px;
        }
        
        /* Boutons utilisateur */
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-avatar {
            width: 35px;
            height: 35px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        .btn-login {
            background: var(--gradient);
            border: none;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.8rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(26,94,42,0.3);
            color: white;
        }
        .btn-dashboard {
            background: transparent;
            border: 2px solid var(--primary);
            padding: 6px 18px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.75rem;
            color: var(--primary);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-dashboard:hover {
            background: var(--gradient);
            border-color: transparent;
            color: white;
            transform: translateY(-2px);
        }
        .btn-logout {
            background: transparent;
            border: 2px solid #dc2626;
            padding: 6px 18px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.75rem;
            color: #dc2626;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-logout:hover {
            background: #dc2626;
            color: white;
            transform: translateY(-2px);
        }
        
        /* ========== CHATBOT CONTAINER ========== */
        .chatbot-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .chatbot-main {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }
        
        /* Sidebar gauche - Catégories */
        .chatbot-sidebar {
            flex: 1;
            min-width: 260px;
            background: white;
            border-radius: 24px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
            height: fit-content;
            position: sticky;
            top: 90px;
        }
        .sidebar-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e8f5e9;
        }
        .categories-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .category-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            color: #555;
        }
        .category-item:hover {
            background: #e8f5e9;
            transform: translateX(5px);
        }
        .category-item i {
            width: 24px;
            color: var(--primary);
        }
        .category-item span {
            font-size: 0.85rem;
        }
        
        /* Zone de chat principale */
        .chatbot-chat {
            flex: 3;
            min-width: 300px;
            background: white;
            border-radius: 24px;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            height: calc(100vh - 140px);
            overflow: hidden;
        }
        
        /* Header du chat */
        .chat-header {
            background: var(--gradient);
            padding: 18px 25px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chat-header h2 {
            font-size: 1.2rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .ai-badge {
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
        }
        .clear-chat {
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .clear-chat:hover {
            background: rgba(255,255,255,0.25);
        }
        
        /* Messages */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #fafdfa;
        }
        
        .message {
            display: flex;
            margin-bottom: 20px;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .message.user { justify-content: flex-end; }
        .message.bot { justify-content: flex-start; }
        
        .message-content {
            max-width: 75%;
            padding: 12px 18px;
            border-radius: 20px;
            white-space: pre-wrap;
            line-height: 1.5;
            font-size: 0.9rem;
        }
        .message.user .message-content {
            background: var(--gradient);
            color: white;
            border-bottom-right-radius: 5px;
        }
        .message.bot .message-content {
            background: #f0f4f0;
            color: #333;
            border-bottom-left-radius: 5px;
        }
        
        .message-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            background: #e8f5e9;
            color: var(--primary);
            font-size: 1rem;
        }
        .message.user .message-avatar {
            margin-right: 0;
            margin-left: 12px;
            background: var(--primary);
            color: white;
        }
        
        .message-content a {
            color: var(--primary);
            text-decoration: underline;
        }
        .message.user .message-content a {
            color: #ffeb3b;
        }
        .message-content strong {
            color: var(--primary-dark);
        }
        .message.user .message-content strong {
            color: #ffeb3b;
        }
        
        /* Input Area */
        .chat-input-area {
            background: white;
            padding: 15px 20px;
            display: flex;
            gap: 12px;
            border-top: 1px solid #e9ecef;
        }
        .chat-input {
            flex: 1;
            border: 2px solid #e9ecef;
            border-radius: 30px;
            padding: 12px 18px;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            resize: none;
            outline: none;
            transition: all 0.2s;
        }
        .chat-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26,94,42,0.1);
        }
        .send-btn {
            background: var(--gradient);
            border: none;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            color: white;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(26,94,42,0.3);
        }
        
        /* Suggestions */
        .suggestions {
            padding: 12px 20px;
            background: #f8faf8;
            border-top: 1px solid #e9ecef;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .suggestion-btn {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 30px;
            padding: 6px 14px;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .suggestion-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
        }
        
        /* Typing Indicator */
        .typing-indicator {
            display: flex;
            gap: 6px;
            padding: 10px 15px;
            background: #f0f4f0;
            border-radius: 20px;
            width: fit-content;
        }
        .typing-indicator span {
            width: 8px;
            height: 8px;
            background: #888;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }
        .typing-indicator span:nth-child(1) { animation-delay: 0s; }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
            30% { transform: translateY(-8px); opacity: 1; }
        }
        
        /* Footer */
        .footer {
            background: white;
            text-align: center;
            padding: 1rem;
            margin-top: 2rem;
            color: #666;
            font-size: 0.7rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        
        @media (max-width: 992px) {
            .chatbot-main { flex-direction: column; }
            .chatbot-sidebar { position: static; }
            .navbar { padding: 0.75rem 1rem; }
            .mobile-toggle { display: block; }
            .nav-links {
                display: none;
                width: 100%;
                flex-direction: column;
                gap: 0;
                margin-top: 1rem;
            }
            .nav-links.open { display: flex; }
            .nav-links li a { display: block; padding: 10px 0; }
            .nav-right { margin-top: 1rem; width: 100%; justify-content: space-between; }
        }
    </style>
</head>
<body class="role-<?php echo $userRole; ?>">

    <!-- ========== NAVBAR MODERNE ========== -->
    <nav class="navbar" id="navbar">
        <a class="nav-brand" href="../../index.php">
            <img src="../../logo.jpeg" alt="Logo Smart Municipality">
            <span class="nav-brand-text">Smart <span>Municipality</span></span>
        </a>
        <button class="mobile-toggle" type="button" aria-label="Ouvrir le menu" onclick="document.querySelector('.nav-links').classList.toggle('open')">
            <span></span><span></span><span></span>
        </button>
        <ul class="nav-links">
            <li><a href="../../index.php">Événements</a></li>
            <li><a href="#">Assistant IA</a></li>
            <li><a href="#">Carte</a></li>
            <li><a href="#">Blog</a></li>
            <li><a href="#">Services</a></li>
        </ul>
        <div class="nav-right">
            <div class="nav-search">
                <span class="nav-search-icon">⌕</span>
                <input type="text" id="globalSearch" placeholder="Rechercher...">
            </div>
            <div class="user-info">
                <?php if ($isLoggedIn): ?>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['prenom'] ?? 'U', 0, 1)); ?>
                    </div>
                    <span class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($userName); ?></span>
                    <?php if ($isAdmin): ?>
                    <a href="../dashboard/admin.php" class="btn-dashboard">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                    <?php endif; ?>
                    <a href="../../logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                <?php else: ?>
                    <a href="../auth/login.php" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="chatbot-wrapper">
        <div class="chatbot-main">
            <!-- Sidebar gauche - Catégories et statistiques -->
            <div class="chatbot-sidebar">
                <div class="sidebar-title">
                    <i class="fas fa-tags"></i>
                    <span>Catégories</span>
                </div>
                <div class="categories-list">
                    <div class="category-item" data-category="all">
                        <i class="fas fa-asterisk"></i>
                        <span>Tous les événements</span>
                    </div>
                    <?php foreach($categories as $cat): 
                        $nbEvenements = count(array_filter($tousEvenements, function($e) use ($cat) {
                            return $e['categorie_id'] == $cat['id'];
                        }));
                    ?>
                    <div class="category-item" data-category="<?php echo strtolower($cat['nom']); ?>">
                        <?php if($cat['nom'] == 'Culture'): ?>
                            <i class="fas fa-music"></i>
                        <?php elseif($cat['nom'] == 'Sport'): ?>
                            <i class="fas fa-futbol"></i>
                        <?php elseif($cat['nom'] == 'Environnement'): ?>
                            <i class="fas fa-leaf"></i>
                        <?php elseif($cat['nom'] == 'Social'): ?>
                            <i class="fas fa-handshake"></i>
                        <?php else: ?>
                            <i class="fas fa-tag"></i>
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($cat['nom']); ?> (<?php echo $nbEvenements; ?>)</span>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="sidebar-title mt-4">
                    <i class="fas fa-info-circle"></i>
                    <span>Informations</span>
                </div>
                <div style="padding: 0 5px;">
                    <p style="font-size: 0.7rem; color: #666; line-height: 1.5;">
                        💡 <strong>Conseils :</strong><br>
                        • Tapez "événements" pour voir la liste<br>
                        • "sport" pour filtrer par catégorie<br>
                        • "ce weekend" pour les événements à venir<br>
                        • Tapez le numéro pour vous inscrire
                    </p>
                </div>
                
                <?php if ($isLoggedIn): ?>
                <div class="sidebar-title mt-4">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Mes inscriptions</span>
                </div>
                <div style="padding: 0 5px;">
                    <a href="../participation/mes_participations.php" style="font-size: 0.75rem; color: var(--primary); text-decoration: none;">
                        <i class="fas fa-arrow-right me-1"></i> Voir toutes mes participations
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Zone de chat principale -->
            <div class="chatbot-chat">
                <div class="chat-header">
                    <h2>
                        <i class="fas fa-robot"></i>
                        Assistant IA Smart Municipality
                        <span class="ai-badge">Powered by Gemini</span>
                    </h2>
                    <button class="clear-chat" id="clearChatBtn">
                        <i class="fas fa-trash-alt me-1"></i> Effacer
                    </button>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <div class="message bot">
                        <div class="message-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="message-content">
                            👋 Bonjour ! Je suis votre **assistant intelligent** 🤖<br><br>
                            Je peux vous aider à :<br>
                            • 📅 **Trouver des événements** (aujourd'hui, ce weekend, cette semaine)<br>
                            • 🏷️ **Filtrer par catégorie** (sport, culture, environnement...)<br>
                            • ✅ **Vous inscrire** à un événement (tapez le numéro)<br>
                            • 📋 **Voir vos inscriptions** ("mes événements")<br><br>
                            
                            <strong>🔍 Exemples :</strong><br>
                            • "Événements aujourd'hui"<br>
                            • "Sport"<br>
                            • "Ce weekend"<br>
                            • "Mes événements"<br><br>
                            
                            💡 Tapez le numéro d'un événement pour vous inscrire !
                        </div>
                    </div>
                </div>
                
                <div class="suggestions">
                    <button class="suggestion-btn" data-message="Événements aujourd'hui">📅 Aujourd'hui</button>
                    <button class="suggestion-btn" data-message="Sport">⚽ Sport</button>
                    <button class="suggestion-btn" data-message="Culture">🎭 Culture</button>
                    <button class="suggestion-btn" data-message="Environnement">🌿 Environnement</button>
                    <button class="suggestion-btn" data-message="Ce weekend">🎉 Ce weekend</button>
                    <button class="suggestion-btn" data-message="Cette semaine">📆 Cette semaine</button>
                    <button class="suggestion-btn" data-message="Mes inscriptions">📋 Mes événements</button>
                </div>
                
                <div class="chat-input-area">
                    <textarea class="chat-input" id="chatInput" placeholder="Posez votre question ici..." rows="1"></textarea>
                    <button class="send-btn" id="sendBtn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p class="mb-0">&copy; 2024 Smart Municipality - Assistant intelligent</p>
        </div>
    </footer>

    <script>
        const chatMessages = document.getElementById('chatMessages');
        const chatInput = document.getElementById('chatInput');
        const sendBtn = document.getElementById('sendBtn');
        const clearChatBtn = document.getElementById('clearChatBtn');
        let isWaiting = false;
        
        // Auto-resize textarea
        chatInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
        
        // Envoyer un message
        function sendMessage(message) {
            if (!message.trim() || isWaiting) return;
            
            addMessage(message, 'user');
            chatInput.value = '';
            chatInput.style.height = 'auto';
            isWaiting = true;
            showTypingIndicator();
            
            fetch('../../controllers/ChatbotAjax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: message })
            })
            .then(response => response.json())
            .then(data => {
                removeTypingIndicator();
                addMessage(data.message, 'bot');
                scrollToBottom();
                isWaiting = false;
            })
            .catch(error => {
                removeTypingIndicator();
                addMessage("❌ Désolé, une erreur s'est produite. Veuillez réessayer.", 'bot');
                scrollToBottom();
                isWaiting = false;
            });
        }
        
        // Ajouter un message
        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            
            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.innerHTML = sender === 'user' ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>';
            
            const content = document.createElement('div');
            content.className = 'message-content';
            
            let formattedText = text;
            formattedText = formattedText.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
            formattedText = formattedText.replace(/\n/g, '<br>');
            const linkRegex = /\[([^\]]+)\]\(([^)]+)\)/g;
            formattedText = formattedText.replace(linkRegex, '<a href="$2" target="_blank" style="color: #1a5e2a; text-decoration: underline;">$1</a>');
            
            content.innerHTML = formattedText;
            
            if (sender === 'user') {
                messageDiv.appendChild(content);
                messageDiv.appendChild(avatar);
            } else {
                messageDiv.appendChild(avatar);
                messageDiv.appendChild(content);
            }
            
            chatMessages.appendChild(messageDiv);
            scrollToBottom();
        }
        
        // Indicateur de frappe
        let typingIndicator = null;
        function showTypingIndicator() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot';
            typingDiv.id = 'typingIndicator';
            typingDiv.innerHTML = `
                <div class="message-avatar"><i class="fas fa-robot"></i></div>
                <div class="typing-indicator">
                    <span></span><span></span><span></span>
                </div>
            `;
            chatMessages.appendChild(typingDiv);
            scrollToBottom();
        }
        
        function removeTypingIndicator() {
            const indicator = document.getElementById('typingIndicator');
            if (indicator) indicator.remove();
        }
        
        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Effacer le chat
        clearChatBtn.addEventListener('click', function() {
            const messages = chatMessages.querySelectorAll('.message');
            messages.forEach(msg => msg.remove());
            // Ajouter le message de bienvenue
            const welcomeMsg = document.createElement('div');
            welcomeMsg.className = 'message bot';
            welcomeMsg.innerHTML = `
                <div class="message-avatar"><i class="fas fa-robot"></i></div>
                <div class="message-content">
                    👋 Conversation effacée ! Comment puis-je vous aider ?
                </div>
            `;
            chatMessages.appendChild(welcomeMsg);
            scrollToBottom();
        });
        
        // Cliquer sur une catégorie
        document.querySelectorAll('.category-item').forEach(item => {
            item.addEventListener('click', function() {
                const category = this.dataset.category;
                if (category === 'all') {
                    sendMessage('Événements');
                } else {
                    sendMessage(category);
                }
            });
        });
        
        // Événements
        sendBtn.addEventListener('click', () => sendMessage(chatInput.value));
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage(chatInput.value);
            }
        });
        
        // Suggestions
        document.querySelectorAll('.suggestion-btn').forEach(btn => {
            btn.addEventListener('click', () => sendMessage(btn.dataset.message));
        });
        
        // Focus sur l'input
        chatInput.focus();
        
        // Scroll to bottom on load
        setTimeout(scrollToBottom, 100);
    </script>
    
</body>
</html>
