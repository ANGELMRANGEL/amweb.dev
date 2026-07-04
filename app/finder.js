// Finder App Logic
window.appFinder = {
    open: function() {
        if (typeof window.closeAllApps === 'function') {
            window.closeAllApps();
        }
        const finderEl = document.querySelector('.finder-window');
        if (finderEl) {
            if (window.updateHeroState) {
                window.updateHeroState('finder');
            }
            finderEl.style.display = 'flex';
            document.querySelector('.hero').scrollIntoView({ behavior: 'smooth', block: 'center' });
            finderEl.style.transition = 'transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            finderEl.style.transform = 'scale(1.03)';
            setTimeout(() => {
                finderEl.style.transform = 'none';
            }, 300);
        }
    },
    close: function() {
        const finderEl = document.querySelector('.finder-window');
        if (finderEl) {
            finderEl.style.display = 'none';
        }
        if (window.updateHeroState) {
            window.updateHeroState('terminal');
        }
        const terminalEl = document.querySelector('.terminal-wrap');
        if (terminalEl) {
            terminalEl.style.display = 'block';
            terminalEl.style.opacity = '1';
        }
    }
};
