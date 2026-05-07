<?php
// views/chatbot/widget.php
?>
<div class="chatbot-widget" id="chatbotWidget">
    <div class="chatbot-toggle" id="chatbotToggle">
        <div class="pulse-ring"></div>
        <i class="fas fa-comment-dots"></i>
        <span class="notification-dot" id="chatbotNotification">1</span>
    </div>
    
    <div class="chatbot-container" id="chatbotContainer" style="display: none;">
        <div class="chatbot-header">
            <div class="chatbot-header-info">
                <div class="chatbot-avatar">
                    <div class="avatar-status online"></div>
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <h3>Assistant Smart</h3>
                    <p>🟢 En ligne • Réponse immédiate</p>
                </div>
            </div>
            <div class="header-actions">
                <button class="chatbot-minimize" id="chatbotMinimize"><i class="fas fa-minus"></i></button>
                <button class="chatbot-close" id="chatbotClose"><i class="fas fa-times"></i></button>
            </div>
        </div>
        
        <div class="chatbot-messages" id="chatbotMessages">
            <div class="message bot">
                <div class="message-avatar">
                    <img src="assets/images/logo.png" alt="Smart" onerror="this.src='https://via.placeholder.com/32'">
                </div>
                <div class="message-bubble">
                    <div class="message-content">
                        <p>✨ <strong>Bonjour !</strong> Je suis l'assistant intelligent de <strong>Smart Municipality</strong>.</p>
                        <p>Je peux vous aider pour : actes d'état civil, carte d'identité, passeport, horaires, événements, etc.</p>
                        <p>Comment puis-je vous aider ? 🤗</p>
                    </div>
                    <span class="message-time"><?php echo date('H:i'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="chatbot-suggestions" id="chatbotSuggestions">
            <span class="suggestions-title">💡 Suggestions :</span>
            <div class="suggestions-list">
                <?php foreach($quick_suggestions as $suggestion): ?>
                    <button class="suggestion-chip" data-suggestion="<?php echo htmlspecialchars($suggestion); ?>">
                        <?php echo htmlspecialchars($suggestion); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="chatbot-input-area">
            <div class="input-container">
                <textarea id="chatbotInput" placeholder="Écrivez votre message..." rows="1" maxlength="500"></textarea>
                <div class="input-actions">
                    <button class="send-btn" id="chatbotSend">
                        <i class="fas fa-paper-plane"></i>
                        <span>Envoyer</span>
                    </button>
                </div>
            </div>
            <div class="chatbot-typing" id="chatbotTyping" style="display: none;">
                <div class="typing-dots"><span></span><span></span><span></span></div>
                <span>L'assistant réfléchit...</span>
            </div>
        </div>
    </div>
</div>