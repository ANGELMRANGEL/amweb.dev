(function() {
    const app = document.getElementById('app');
    app.innerHTML = `
        <style>
            #back-btn:focus {
                background: #ffffff !important;
                color: #000000 !important;
                border-color: #ffffff !important;
                box-shadow: 0 0 0 5px #00bcd4, 0 4px 25px rgba(0, 188, 212, 0.6) !important;
                transform: scale(1.05);
            }
        </style>
        <!-- Botón para volver al Home -->
        <button id="back-btn" style="position: fixed; top: 15px; left: 15px; z-index: 3000000; background: rgba(18, 18, 18, 0.75); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.2); padding: 10px 20px; border-radius: 30px; font-size: 13px; font-weight: 700; cursor: pointer; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0 4px 15px rgba(0,0,0,0.5); transition: opacity 0.5s ease, transform 0.3s ease; font-family: sans-serif; -webkit-tap-highlight-color: transparent; text-transform: uppercase; opacity: 1; pointer-events: auto;">Atrás</button>

        <div id="blocker" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 2000000; pointer-events: auto; background: transparent;"></div>

        <div id="iframe-container" style="width: 100vw; height: 100vh; background: #000000; overflow: hidden; position: fixed; top: 0; left: 0; z-index: 1000001; opacity: 1; pointer-events: auto;">
            <iframe id="stream-iframe" src="https://latamvidzfy.org/dsportsar.php" style="width: 100%; height: 100%; border: none;" allow="autoplay *; encrypted-media *; gyroscope *; picture-in-picture *; fullscreen *" allowfullscreen></iframe>
        </div>
    `;

    const backBtn = document.getElementById('back-btn');
    let timeout;
    function showBtn() {
        backBtn.style.opacity = '1';
        backBtn.style.pointerEvents = 'auto';
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            backBtn.style.opacity = '0';
            backBtn.style.pointerEvents = 'none';
        }, 3000);
    }

    // Registrar eventos
    document.addEventListener('mousemove', showBtn);
    document.addEventListener('touchstart', showBtn);
    document.addEventListener('click', showBtn);

    function handleKeyDown(e) {
        showBtn();
        backBtn.focus();
        if (e.key === 'Enter') {
            e.preventDefault();
            backBtn.click();
        }
    }
    document.addEventListener('keydown', handleKeyDown);

    // Limpieza de eventos al salir
    backBtn.addEventListener('click', () => {
        document.removeEventListener('mousemove', showBtn);
        document.removeEventListener('touchstart', showBtn);
        document.removeEventListener('click', showBtn);
        document.removeEventListener('keydown', handleKeyDown);
        clearTimeout(timeout);
        window.navigateTo('home');
    });

    // Desactivar window.open de forma global con bloqueo severo
    window.open = function () {
        throw new Error('Popups deshabilitados');
    };

    // Iniciar desvanecimiento del botón atrás al cargar
    showBtn();
    setTimeout(() => {
        backBtn.focus();
    }, 100);
})();
