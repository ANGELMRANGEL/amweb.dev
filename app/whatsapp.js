// WhatsApp App Logic
window.appWhatsapp = {
    open: function() {
        window.openDockModal('whatsapp-modal');
        setTimeout(() => {
            const chatBody = document.getElementById('wa-chat-body');
            if (chatBody) chatBody.scrollTop = chatBody.scrollHeight;
        }, 100);
    },
    send: function(event) {
        event.preventDefault();
        const input = document.getElementById('wa-input');
        const text = input.value.trim();
        if (!text) return;
        
        const chatBody = document.getElementById('wa-chat-body');
        
        const msgDiv = document.createElement('div');
        msgDiv.className = 'wa-msg wa-msg-out';
        msgDiv.innerHTML = `
            <div class="wa-msg-bubble">
                <span class="wa-msg-text"></span>
                <span class="wa-msg-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} <span style="color:#34b7f1; margin-left:2px;">✓✓</span></span>
            </div>
        `;
        msgDiv.querySelector('.wa-msg-text').textContent = text;
        chatBody.appendChild(msgDiv);
        chatBody.scrollTop = chatBody.scrollHeight;
        
        input.value = '';
        
        const targetNumber = '584127529976';
        const url = `https://wa.me/${targetNumber}?text=${encodeURIComponent(text)}`;
        
        setTimeout(() => {
            window.open(url, '_blank');
        }, 800);
    }
};
