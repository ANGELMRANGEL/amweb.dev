// Terminal App Logic
window.appTerminal = {
    open: function() {
        if (typeof window.closeAllApps === 'function') {
            window.closeAllApps();
        }
        const terminalEl = document.querySelector('.terminal-wrap');
        if (terminalEl) {
            terminalEl.style.display = 'block';
            terminalEl.style.opacity = '1';
        }
        document.querySelector('.hero').scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (typeof window.restartAnimations === 'function') {
            window.restartAnimations();
        }
    },
    close: function() {
        const terminalEl = document.querySelector('.terminal-wrap');
        if (terminalEl) {
            terminalEl.style.display = 'none';
        }
    }
};
