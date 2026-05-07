<?php
session_start();
require_once __DIR__ . '/controller/CategorieEvenementC.php';
require_once __DIR__ . '/controller/EvenementC.php';

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$userName = isset($_SESSION['prenom']) ? $_SESSION['prenom'] . ' ' . $_SESSION['nom'] : 'Invité';
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'citoyen';

$categorieC = new CategorieEvenementC();
$evenementC = new EvenementC();

$categories = $categorieC->afficherCategories();
$tousEvenements = $evenementC->afficherEvenementsAVenir();

$message = '';
$messageType = '';
if (isset($_GET['success']) && $_GET['success'] == 'inscrit') {
    $message = '✅ Votre inscription a été envoyée ! En attente de validation.';
    $messageType = 'success';
}
if (isset($_GET['error'])) {
    $message = '❌ ' . htmlspecialchars($_GET['error']);
    $messageType = 'danger';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality - Catégories</title>
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
        
        /* Hero Section */
        .hero {
            background: var(--gradient);
            padding: 50px 0;
            text-align: center;
            color: white;
        }
        .hero h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .hero p { font-size: 0.9rem; opacity: 0.9; }
        
        /* Categories Grid */
        .categories-section { padding: 40px 0; }
        .section-title { text-align: center; margin-bottom: 40px; }
        .section-title h2 { font-size: 1.6rem; font-weight: 700; color: var(--primary); margin-bottom: 10px; }
        .section-title p { color: #666; font-size: 0.85rem; }
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }
        .category-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: block;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .category-image {
            height: 160px;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .category-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .category-card:hover .category-image img {
            transform: scale(1.05);
        }
        .category-icon {
            font-size: 3rem;
            color: rgba(255,255,255,0.8);
        }
        .category-content { padding: 20px; text-align: center; }
        .category-name { font-size: 1.2rem; font-weight: 700; color: var(--primary); margin-bottom: 8px; }
        .category-description { color: #666; font-size: 0.75rem; margin-bottom: 15px; line-height: 1.4; }
        .category-stats span { font-size: 0.7rem; color: #888; }
        .category-stats i { color: var(--primary); margin-right: 5px; }
        .badge-count {
            background: #e8f5e9;
            color: var(--primary);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }
        
        /* Toast */
        .toast-message {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 10001;
            animation: slideInRight 0.3s ease;
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        /* Footer */
        .footer {
            background: white;
            text-align: center;
            padding: 1.25rem;
            margin-top: 2rem;
            color: #666;
            font-size: 0.7rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        
        @media (max-width: 768px) {
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
            .hero h1 { font-size: 1.5rem; }
            .categories-grid { grid-template-columns: 1fr; gap: 15px; }
            .category-image { height: 140px; }
        }
    </style>
</head>
<body class="role-<?php echo $userRole; ?>">

    <!-- Toast Notification -->
    <?php if ($message): ?>
    <div class="toast-message">
        <div class="alert alert-<?php echo $messageType; ?> shadow rounded-3 border-0 py-2 px-3">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- ========== NAVBAR MODERNE ========== -->
    <nav class="navbar" id="navbar">
        <a class="nav-brand" href="index.php">
            <img src="logo.jpeg" alt="Logo Smart Municipality">
            <span class="nav-brand-text">Smart <span>Municipality</span></span>
        </a>
        <button class="mobile-toggle" type="button" aria-label="Ouvrir le menu" onclick="document.querySelector('.nav-links').classList.toggle('open')">
            <span></span><span></span><span></span>
        </button>
        <ul class="nav-links">
            <li><a href="index.php#profil">Profil</a></li>
            <li><a href="#" class="active">Événements</a></li>
            <li><a href="#">Carte</a></li>
            <li><a href="#">Blog</a></li>
            <li><a href="#">Services</a></li>
            <li><a href="#">Rendez-vous</a></li>
        </ul>
        <div class="nav-right">
            <div class="nav-search">
                <span class="nav-search-icon">⌕</span>
                <input type="text" id="searchInput" placeholder="Rechercher...">
            </div>
            <div class="user-info">
                <?php if ($isLoggedIn): ?>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['prenom'] ?? 'U', 0, 1)); ?>
                    </div>
                    <span class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($userName); ?></span>
                    <?php if ($isAdmin): ?>
                    <a href="views/dashboard/admin.php" class="btn-dashboard">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                <?php else: ?>
                    <a href="views/auth/login.php" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1><i class="fas fa-th-large me-2"></i>Découvrez nos événements</h1>
            <p>Choisissez une catégorie pour voir tous les événements associés</p>
        </div>
    </section>

    <!-- Categories Grid -->
    <div class="container categories-section">
        <div class="section-title">
            <h2>Catégories d'événements</h2>
            <p>Explorez les événements par catégorie</p>
        </div>
        
        <div class="categories-grid">
            <?php foreach($categories as $cat): 
                $nbEvenements = count(array_filter($tousEvenements, function($e) use ($cat) {
                    return $e['categorie_id'] == $cat['id'];
                }));
            ?>
            <a href="categorie_evenements.php?id=<?php echo $cat['id']; ?>" class="category-card">
                <div class="category-image">
                    <?php 
                    // Vérifier si une image existe dans la base de données
                    $imagePath = !empty($cat['image_url']) ? $cat['image_url'] : null;
                    if($imagePath && file_exists($imagePath)): 
                    ?>
                        <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($cat['nom']); ?>">
                    <?php else: ?>
                        <!-- Icônes par défaut si pas d'image -->
                        <?php if($cat['nom'] == 'Culture'): ?>
                            <i class="fas fa-music category-icon"></i>
                        <?php elseif($cat['nom'] == 'Sport'): ?>
                            <i class="fas fa-futbol category-icon"></i>
                        <?php elseif($cat['nom'] == 'Environnement'): ?>
                            <i class="fas fa-leaf category-icon"></i>
                        <?php elseif($cat['nom'] == 'Social'): ?>
                            <i class="fas fa-handshake category-icon"></i>
                        <?php elseif($cat['nom'] == 'Technologie'): ?>
                            <i class="fas fa-microchip category-icon"></i>
                        <?php else: ?>
                            <i class="fas fa-tag category-icon"></i>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="category-content">
                    <h3 class="category-name"><?php echo htmlspecialchars($cat['nom']); ?></h3>
                    <p class="category-description"><?php echo substr(htmlspecialchars($cat['description']), 0, 70); ?>...</p>
                    <div class="category-stats">
                        <span><i class="fas fa-calendar-alt"></i> <?php echo $nbEvenements; ?> événements</span>
                    </div>
                    <div class="mt-3">
                        <span class="badge-count">
                            <i class="fas fa-arrow-right me-1"></i> Voir les événements
                        </span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p class="mb-0">&copy; 2024 Smart Municipality - Tous droits réservés</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide toast
        setTimeout(() => {
            const toast = document.querySelector('.toast-message');
            if (toast) {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    </script>
    <!-- Bouton flottant du chatbot -->
<div class="chatbot-float-btn" id="chatbotFloatBtn">
    <i class="fas fa-comment-dots"></i>
    <span class="chatbot-pulse"></span>
</div>

<!-- Fenêtre du chatbot -->
<div class="chatbot-window" id="chatbotWindow">
    <div class="chatbot-header">
        <div class="chatbot-header-info">
            <i class="fas fa-robot"></i>
            <div>
                <h4>Assistant IA</h4>
                <p>Smart Municipality</p>
            </div>
        </div>
        <div>
            <button class="chatbot-minimize" id="chatbotMinimize">
                <i class="fas fa-minus"></i>
            </button>
            <button class="chatbot-close" id="chatbotClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    
    <div class="chatbot-messages" id="chatbotMessages">
        <div class="message-bot">
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                👋 Bonjour ! Je suis votre assistant IA.<br><br>
                Je peux vous aider à :<br>
                • 📅 Trouver des événements<br>
                • 🏷️ Filtrer par catégorie<br>
                • ✅ Vous inscrire<br><br>
                <strong>Exemples :</strong><br>
                "Événements aujourd'hui"<br>
                "Sport"<br>
                "Mes événements"
            </div>
        </div>
    </div>
    
    <div class="chatbot-input-area">
        <textarea class="chatbot-input" id="chatbotInput" placeholder="Posez votre question..." rows="1"></textarea>
        <button class="chatbot-send" id="chatbotSend">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>

<style>
    /* ========== BOUTON FLOTTANT CHATBOT ========== */
    .chatbot-float-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #1a5e2a, #4caf50);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
        cursor: pointer;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
        z-index: 1000;
        border: none;
    }
    .chatbot-float-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 10px 30px rgba(26,94,42,0.4);
    }
    .chatbot-pulse {
        position: absolute;
        width: 100%;
        height: 100%;
        background: #4caf50;
        border-radius: 50%;
        opacity: 0.6;
        animation: pulse 1.5s infinite;
        z-index: -1;
    }
    @keyframes pulse {
        0% { transform: scale(1); opacity: 0.6; }
        100% { transform: scale(1.5); opacity: 0; }
    }
    
    /* ========== FENÊTRE CHATBOT ========== */
    .chatbot-window {
        position: fixed;
        bottom: 100px;
        right: 30px;
        width: 380px;
        height: 500px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        z-index: 1001;
        transform: scale(0);
        opacity: 0;
        transition: all 0.3s ease;
        transform-origin: bottom right;
    }
    .chatbot-window.open {
        transform: scale(1);
        opacity: 1;
    }
    
    /* Header */
    .chatbot-header {
        background: linear-gradient(135deg, #1a5e2a, #4caf50);
        padding: 15px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .chatbot-header-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .chatbot-header-info i {
        font-size: 1.5rem;
    }
    .chatbot-header-info h4 {
        margin: 0;
        font-size: 1rem;
    }
    .chatbot-header-info p {
        margin: 0;
        font-size: 0.7rem;
        opacity: 0.8;
    }
    .chatbot-header button {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.2s;
        margin-left: 5px;
    }
    .chatbot-header button:hover {
        background: rgba(255,255,255,0.3);
    }
    
    /* Messages */
    .chatbot-messages {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
        background: #f8faf8;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .message-bot, .message-user {
        display: flex;
        gap: 10px;
        animation: fadeIn 0.3s ease;
    }
    .message-user {
        justify-content: flex-end;
    }
    .message-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e8f5e9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1a5e2a;
        font-size: 0.9rem;
        flex-shrink: 0;
    }
    .message-user .message-avatar {
        background: linear-gradient(135deg, #1a5e2a, #4caf50);
        color: white;
        order: 1;
    }
    .message-content {
        max-width: 75%;
        padding: 8px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        line-height: 1.4;
        background: white;
        color: #333;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .message-user .message-content {
        background: linear-gradient(135deg, #1a5e2a, #4caf50);
        color: white;
    }
    .message-content strong {
        color: #1a5e2a;
    }
    .message-user .message-content strong {
        color: #ffeb3b;
    }
    .message-content a {
        color: #1a5e2a;
        text-decoration: underline;
    }
    
    /* Input */
    .chatbot-input-area {
        padding: 12px;
        background: white;
        border-top: 1px solid #e9ecef;
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .chatbot-input {
        flex: 1;
        border: 2px solid #e9ecef;
        border-radius: 20px;
        padding: 8px 15px;
        font-family: 'Inter', sans-serif;
        font-size: 0.8rem;
        resize: none;
        outline: none;
    }
    .chatbot-input:focus {
        border-color: #1a5e2a;
    }
    .chatbot-send {
        background: linear-gradient(135deg, #1a5e2a, #4caf50);
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        color: white;
        cursor: pointer;
        transition: all 0.2s;
    }
    .chatbot-send:hover {
        transform: scale(1.05);
    }
    
    /* Typing indicator */
    .typing-indicator {
        display: flex;
        gap: 5px;
        padding: 8px 12px;
        background: white;
        border-radius: 15px;
        width: fit-content;
    }
    .typing-indicator span {
        width: 6px;
        height: 6px;
        background: #999;
        border-radius: 50%;
        animation: typing 1.4s infinite;
    }
    @keyframes typing {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
        30% { transform: translateY(-6px); opacity: 1; }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @media (max-width: 480px) {
        .chatbot-window {
            width: calc(100vw - 40px);
            right: 20px;
            left: 20px;
            height: 60vh;
        }
        .chatbot-float-btn {
            bottom: 20px;
            right: 20px;
        }
    }
</style>

<script>
    // Éléments du chatbot
    const floatBtn = document.getElementById('chatbotFloatBtn');
    const chatbotWindow = document.getElementById('chatbotWindow');
    const minimizeBtn = document.getElementById('chatbotMinimize');
    const closeBtn = document.getElementById('chatbotClose');
    const sendBtn = document.getElementById('chatbotSend');
    const chatbotInput = document.getElementById('chatbotInput');
    const chatbotMessages = document.getElementById('chatbotMessages');
    
    let isWaiting = false;
    
    // Ouvrir/fermer
    floatBtn.addEventListener('click', () => {
        chatbotWindow.classList.add('open');
        floatBtn.style.opacity = '0';
        setTimeout(() => {
            floatBtn.style.visibility = 'hidden';
        }, 300);
        chatbotInput.focus();
    });
    
    function closeChatbot() {
        chatbotWindow.classList.remove('open');
        floatBtn.style.visibility = 'visible';
        floatBtn.style.opacity = '1';
    }
    
    minimizeBtn.addEventListener('click', closeChatbot);
    closeBtn.addEventListener('click', closeChatbot);
    
    // Auto-resize textarea
    chatbotInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 80) + 'px';
    });
    
    // Envoyer message
    function sendMessage(message) {
        if (!message.trim() || isWaiting) return;
        
        addMessage(message, 'user');
        chatbotInput.value = '';
        chatbotInput.style.height = 'auto';
        isWaiting = true;
        showTypingIndicator();
        
        fetch('controller/ChatbotAjax.php', {
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
            addMessage("❌ Erreur, veuillez réessayer.", 'bot');
            scrollToBottom();
            isWaiting = false;
        });
    }
    
    function addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message-${sender}`;
        messageDiv.style.animation = 'fadeIn 0.3s ease';
        
        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';
        avatar.innerHTML = sender === 'user' ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>';
        
        const content = document.createElement('div');
        content.className = 'message-content';
        
        let formattedText = text;
        formattedText = formattedText.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
        formattedText = formattedText.replace(/\n/g, '<br>');
        const linkRegex = /\[([^\]]+)\]\(([^)]+)\)/g;
        formattedText = formattedText.replace(linkRegex, '<a href="$2" target="_blank">$1</a>');
        
        content.innerHTML = formattedText;
        
        if (sender === 'user') {
            messageDiv.appendChild(content);
            messageDiv.appendChild(avatar);
        } else {
            messageDiv.appendChild(avatar);
            messageDiv.appendChild(content);
        }
        
        chatbotMessages.appendChild(messageDiv);
        scrollToBottom();
    }
    
    let typingIndicator = null;
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message-bot';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = `
            <div class="message-avatar"><i class="fas fa-robot"></i></div>
            <div class="typing-indicator"><span></span><span></span><span></span></div>
        `;
        chatbotMessages.appendChild(typingDiv);
        scrollToBottom();
    }
    
    function removeTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) indicator.remove();
    }
    
    function scrollToBottom() {
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }
    
    sendBtn.addEventListener('click', () => sendMessage(chatbotInput.value));
    chatbotInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage(chatbotInput.value);
        }
    });
    
    // Suggestions rapides
    const suggestions = [
        "Événements aujourd'hui",
        "Sport",
        "Culture",
        "Ce weekend"
    ];
    
    // Ajouter des suggestions après le message de bienvenue
    const suggestionsContainer = document.createElement('div');
    suggestionsContainer.style.display = 'flex';
    suggestionsContainer.style.gap = '8px';
    suggestionsContainer.style.flexWrap = 'wrap';
    suggestionsContainer.style.marginTop = '10px';
    
    suggestions.forEach(s => {
        const btn = document.createElement('button');
        btn.textContent = s;
        btn.style.background = '#e8f5e9';
        btn.style.border = 'none';
        btn.style.padding = '5px 12px';
        btn.style.borderRadius = '20px';
        btn.style.fontSize = '0.7rem';
        btn.style.cursor = 'pointer';
        btn.style.color = '#1a5e2a';
        btn.onclick = () => sendMessage(s);
        suggestionsContainer.appendChild(btn);
    });
    
    // Attendre que le message de bienvenue soit chargé
    setTimeout(() => {
        const firstMessage = document.querySelector('.message-bot .message-content');
        if (firstMessage) {
            firstMessage.appendChild(suggestionsContainer);
        }
    }, 100);
</script>
</body>
</html>