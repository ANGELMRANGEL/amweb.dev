(function() {
    const app = document.getElementById('app');
    app.innerHTML = `
        <style>
            .btn-home {
                padding: 18px;
                font-size: 16px;
                font-weight: 800;
                border-radius: 50px;
                cursor: pointer;
                text-transform: uppercase;
                letter-spacing: 1.5px;
                outline: none;
                transition: all 0.3s ease;
                -webkit-tap-highlight-color: transparent;
            }
            .btn-home:focus {
                background: #ffffff !important;
                color: #000000 !important;
                box-shadow: 0 0 0 5px #00bcd4, 0 4px 25px rgba(0, 188, 212, 0.6) !important;
                transform: scale(1.05);
            }
        </style>
        <div style="width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 40px; background: #000000;">
            <img src="amweb.png?v=${window.appVersion}" style="max-width: 180px; height: auto;" alt="Logo">
            <div style="display: flex; flex-direction: column; gap: 20px; width: 80%; max-width: 320px;">
                <button id="btn-dgo" class="btn-home" style="color: #000000; background: #ffffff; border: none; box-shadow: 0 4px 20px rgba(255, 255, 255, 0.15);">DGO</button>
                <button id="btn-dgoplus" class="btn-home" style="color: #ffffff; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2);">DGO+</button>
            </div>
        </div>
    `;

    const btnDgo = document.getElementById('btn-dgo');
    const btnDgoPlus = document.getElementById('btn-dgoplus');
    const focusable = [btnDgo, btnDgoPlus];
    let focusIndex = 0;

    // Auto focus initial element
    setTimeout(() => {
        focusable[focusIndex].focus();
    }, 100);

    function handleKeyDown(e) {
        if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
            e.preventDefault();
            focusIndex = (focusIndex + 1) % focusable.length;
            focusable[focusIndex].focus();
        } else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
            e.preventDefault();
            focusIndex = (focusIndex - 1 + focusable.length) % focusable.length;
            focusable[focusIndex].focus();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            focusable[focusIndex].click();
        }
    }

    document.addEventListener('keydown', handleKeyDown);

    btnDgo.addEventListener('click', () => {
        document.removeEventListener('keydown', handleKeyDown);
        window.navigateTo('dgo');
    });

    btnDgoPlus.addEventListener('click', () => {
        document.removeEventListener('keydown', handleKeyDown);
        window.navigateTo('dgoplus');
    });
})();
