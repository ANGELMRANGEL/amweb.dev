// Mail App Logic
window.appMail = {
    open: function() {
        window.openDockModal('mail-modal');
    },
    send: function(event) {
        event.preventDefault();
        const form = document.getElementById('mail-form');
        const submitBtn = document.getElementById('mail-send-btn');
        const statusEl = document.getElementById('mail-status');
        
        const formData = new FormData(form);
        submitBtn.disabled = true;
        statusEl.textContent = window.currentLang === 'es' ? 'Enviando...' : 'Sending...';
        statusEl.className = 'mail-status info';
        
        fetch('send_email.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusEl.textContent = window.currentLang === 'es' ? 'Mensaje enviado con éxito.' : 'Message sent successfully.';
                statusEl.className = 'mail-status success';
                form.reset();
            } else {
                statusEl.textContent = data.error || (window.currentLang === 'es' ? 'Error al enviar.' : 'Error sending email.');
                statusEl.className = 'mail-status error';
            }
        })
        .catch(err => {
            statusEl.textContent = window.currentLang === 'es' ? 'Error de conexión.' : 'Connection error.';
            statusEl.className = 'mail-status error';
        })
        .finally(() => {
            submitBtn.disabled = false;
        });
    }
};
