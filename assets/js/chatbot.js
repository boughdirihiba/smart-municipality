// assets/js/chatbot.js
$(document).ready(function() {
    const widget = $('#chatbotWidget');
    const container = $('#chatbotContainer');
    const messagesDiv = $('#chatbotMessages');
    const input = $('#chatbotInput');
    const sendBtn = $('#chatbotSend');
    const typingIndicator = $('#chatbotTyping');
    
    // URLs des endpoints (à adapter selon ton routage)
    const SEND_URL = 'index.php?action=sendMessage';      // ou 'chatbot/send'
    const SUGGESTIONS_URL = 'index.php?action=getSuggestions';
    
    // Ouvrir/fermer le widget
    $('#chatbotToggle').on('click', function() {
        container.slideToggle(300);
        if (container.is(':visible')) {
            $('#chatbotNotification').fadeOut();
        }
    });
    
    $('#chatbotClose, #chatbotMinimize').on('click', function() {
        container.slideUp(300);
    });
    
    // Envoi du message
    function sendMessage(message) {
        if (!message.trim()) return;
        
        // Afficher le message utilisateur
        addMessage(message, 'user');
        input.val('');
        autoResizeTextarea();
        
        // Indicateur de frappe
        typingIndicator.show();
        scrollToBottom();
        
        $.ajax({
            url: SEND_URL,
            type: 'POST',
            data: { message: message },
            dataType: 'json',
            timeout: 30000,
            success: function(response) {
                typingIndicator.hide();
                if (response.success) {
                    addMessage(response.response, 'bot');
                } else {
                    addMessage("❌ " + response.response, 'bot');
                }
            },
            error: function(xhr, status, error) {
                typingIndicator.hide();
                console.error("AJAX Error:", status, error);
                let errorMsg = "❌ Connexion au serveur échouée.";
                if (status === 'timeout') errorMsg = "⏱️ Délai dépassé, réessayez.";
                else if (status === 'parsererror') errorMsg = "⚠️ Réponse invalide du serveur.";
                addMessage(errorMsg, 'bot');
            }
        });
    }
    
    // Ajouter un message dans le chat
    function addMessage(text, sender) {
        const time = new Date().toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' });
        const messageHtml = `
            <div class="message ${sender}">
                <div class="message-avatar">
                    ${sender === 'bot' ? '<i class="fas fa-robot"></i>' : '<i class="fas fa-user"></i>'}
                </div>
                <div class="message-bubble">
                    <div class="message-content"><p>${escapeHtml(text)}</p></div>
                    <span class="message-time">${time}</span>
                </div>
            </div>
        `;
        messagesDiv.append(messageHtml);
        scrollToBottom();
    }
    
    // Échapper le HTML pour éviter XSS
    function escapeHtml(str) {
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        }).replace(/[\n\r]/g, '<br>');
    }
    
    // Auto-resize du textarea
    function autoResizeTextarea() {
        input.css('height', 'auto').css('height', Math.min(input[0].scrollHeight, 120) + 'px');
    }
    
    function scrollToBottom() {
        messagesDiv.scrollTop(messagesDiv[0].scrollHeight);
    }
    
    // Événements
    sendBtn.on('click', function() {
        sendMessage(input.val());
    });
    
    input.on('keypress', function(e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            sendMessage(input.val());
        }
    });
    
    input.on('input', autoResizeTextarea);
    
    // Suggestions
    $(document).on('click', '.suggestion-chip', function() {
        const suggestion = $(this).data('suggestion');
        if (suggestion) sendMessage(suggestion);
    });
    
    // Charger les suggestions dynamiquement (optionnel)
    function loadSuggestions() {
        $.getJSON(SUGGESTIONS_URL, function(data) {
            if (data.success && data.suggestions.length) {
                const list = $('.suggestions-list');
                list.empty();
                data.suggestions.forEach(sug => {
                    list.append(`<button class="suggestion-chip" data-suggestion="${escapeHtml(sug)}">${escapeHtml(sug)}</button>`);
                });
            }
        }).fail(function() {
            console.warn("Impossible de charger les suggestions");
        });
    }
    loadSuggestions();
});