// Calculator App Logic
window.appCalculadora = {
    open: function() {
        window.openDockModal('calc-modal');
    },
    calcVal: '',
    press: function(char) {
        const display = document.getElementById('calc-display');
        if (!display) return;
        
        if (char === 'C') {
            this.calcVal = '';
            display.textContent = '0';
        } else if (char === '+/-') {
            if (this.calcVal) {
                if (this.calcVal.startsWith('-')) {
                    this.calcVal = this.calcVal.substring(1);
                } else {
                    this.calcVal = '-' + this.calcVal;
                }
                display.textContent = this.calcVal;
            }
        } else if (char === '%') {
            if (this.calcVal) {
                try {
                    this.calcVal = (parseFloat(this.calcVal) / 100) + '';
                    display.textContent = this.calcVal;
                } catch(e) {
                    display.textContent = 'Error';
                    this.calcVal = '';
                }
            }
        } else if (char === '=') {
            try {
                const clean = this.calcVal.replace(/[^0-9\+\-\*\/\.]/g, '');
                this.calcVal = Function('"use strict";return (' + clean + ')')() + '';
                display.textContent = this.calcVal;
            } catch(e) {
                display.textContent = 'Error';
                this.calcVal = '';
            }
        } else {
            if (display.textContent === '0' && !isNaN(char)) {
                this.calcVal = char;
            } else {
                this.calcVal += char;
            }
            display.textContent = this.calcVal;
        }
    }
};
