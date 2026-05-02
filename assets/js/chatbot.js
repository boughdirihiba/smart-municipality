(function() {
    let isOpen = false;
    
    const toggleBtn = document.getElementById('chatbotToggle');
    const container = document.getElementById('chatbotContainer');
    const closeBtn = document.getElementById('chatbotClose');
    const minimizeBtn = document.getElementById('chatbotMinimize');
    const messagesContainer = document.getElementById('chatbotMessages');
    const input = document.getElementById('chatbotInput');
    const sendBtn = document.getElementById('chatbotSend');
    const typingIndicator = document.getElementById('chatbotTyping');
    const suggestionsContainer = document.getElementById('chatbotSuggestions');
    const notification = document.getElementById('chatbotNotification');
    
    if(container) container.style.display = 'none';
    
    // Ouvrir/Fermer
    if(toggleBtn) {
        toggleBtn.onclick = function() {
            if(isOpen) {
                closeChat();
            } else {
                openChat();
            }
        };
    }
    
    function openChat() {
        container.style.display = 'flex';
        isOpen = true;
        if(input) setTimeout(() => input.focus(), 300);
        scrollToBottom();
        if(notification) notification.style.display = 'none';
    }
    
    function closeChat() {
        container.style.display = 'none';
        isOpen = false;
    }
    
    if(closeBtn) closeBtn.onclick = closeChat;
    if(minimizeBtn) minimizeBtn.onclick = closeChat;
    
    // Auto-resize textarea
    if(input) {
        input.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
    }
    
    async function sendMessage(message) {
        if(!message || message.trim() === '') return;
        
        addMessage(message, 'user');
        if(input) input.value = '';
        if(sendBtn) sendBtn.disabled = true;
        
        // Afficher l'indicateur de frappe
        if(typingIndicator) typingIndicator.style.display = 'flex';
        scrollToBottom();
        
        try {
            const response = await fetch('index.php?action=chatbot_send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'message=' + encodeURIComponent(message)
            });
            
            const data = await response.json();
            if(typingIndicator) typingIndicator.style.display = 'none';
            
            if(data.success) {
                addMessage(data.response, 'bot');
            } else {
                addMessage("❌ " + data.response, 'bot');
            }
        } catch(error) {
            console.error(error);
            if(typingIndicator) typingIndicator.style.display = 'none';
            addMessage("❌ Désolé, je n'arrive pas à contacter le serveur. Vérifiez votre connexion.", 'bot');
        }
        
        if(sendBtn) sendBtn.disabled = false;
        scrollToBottom();
    }
    
    function addMessage(text, sender) {
        if(!messagesContainer) return;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}`;
        const time = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        
        const avatarHtml = sender === 'bot' 
            ? '<div class="message-avatar"><img src="assets/images/logo.png" alt="Smart" onerror="this.src=\'https://via.placeholder.com/32\'"></div>'
            : '<div class="message-avatar"><i class="fas fa-user" style="background:#052E16; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white;"></i></div>';
        
        messageDiv.innerHTML = `
            ${avatarHtml}
            <div class="message-bubble">
                <div class="message-content">${formatMessage(text)}</div>
                <span class="message-time">${time}</span>
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
    }
    
    function formatMessage(text) {
        return text.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    }
    
    function scrollToBottom() {
        if(messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }
    
    // Événements d'envoi
    if(sendBtn) sendBtn.onclick = () => sendMessage(input?.value);
    if(input) input.onkeypress = (e) => {
        if(e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage(input.value);
        }
    };
    
    // Suggestions
    if(suggestionsContainer) {
        suggestionsContainer.onclick = (e) => {
            const chip = e.target.closest('.suggestion-chip');
            if(chip && chip.dataset.suggestion) {
                sendMessage(chip.dataset.suggestion);
            }
        };
    }
    
    // Réduire la notification
    if(notification) {
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }
})();