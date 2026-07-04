// Preview App Logic
window.appVistaPrevia = {
    rotation: 0,
    inverted: false,
    
    open: function() {
        window.openDockModal('preview-modal');
    },
    
    rotate: function() {
        this.rotation = (this.rotation + 90) % 360;
        this.updateFilters();
    },
    
    invert: function() {
        this.inverted = !this.inverted;
        this.updateFilters();
    },
    
    reset: function() {
        this.rotation = 0;
        this.inverted = false;
        this.updateFilters();
    },
    
    updateFilters: function() {
        const img = document.getElementById('preview-img-el');
        if (!img) return;
        
        let filters = '';
        if (this.inverted) filters += 'invert(1)';
        
        img.style.transform = `rotate(${this.rotation}deg)`;
        img.style.filter = filters;
    }
};
