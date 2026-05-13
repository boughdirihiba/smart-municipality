// assets/js/chatbot.js
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('chatbotToggle');
    const container = document.getElementById('chatbotContainer');
    const minimize = document.getElementById('chatbotMinimize');
    const closeBtn = document.getElementById('chatbotClose');
    const sendBtn = document.getElementById('chatbotSend');
    const input = document.getElementById('chatbotInput');
    const messagesDiv = document.getElementById('chatbotMessages');
    const typingDiv = document.getElementById('chatbotTyping');

    function openChat() { container.style.display = 'flex'; }
    function closeChat() { container.style.display = 'none'; }

    if (toggle) toggle.addEventListener('click', openChat);
    if (minimize) minimize.addEventListener('click', closeChat);
    if (closeBtn) closeBtn.addEventListener('click', closeChat);

    function addMessage(text, isUser) {
        const div = document.createElement('div');
        div.className = 'message ' + (isUser ? 'user' : 'bot');
        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        div.innerHTML = `
            <div class="message-avatar">${isUser ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>'}</div>
            <div class="message-bubble"><div class="message-content">${text}</div><span class="message-time">${time}</span></div>
        `;
        messagesDiv.appendChild(div);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    async function sendMessage() {
        const message = input.value.trim();
        if (!message) return;
        addMessage(message, true);
        input.value = '';
        typingDiv.style.display = 'flex';

        try {
            // Utilisation de x-www-form-urlencoded au lieu de JSON
            const response = await fetch('chatbot_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'message=' + encodeURIComponent(message)
            });
            const data = await response.json();
            typingDiv.style.display = 'none';
            if (data.success) {
                addMessage(data.response, false);
            } else {
                addMessage('❌ ' + data.response, false);
            }
        } catch (err) {
            typingDiv.style.display = 'none';
            addMessage('❌ Connexion au serveur échouée.', false);
        }
    }

    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    document.querySelectorAll('.suggestion-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            input.value = chip.dataset.suggestion;
            sendMessage();
        });
    });
});