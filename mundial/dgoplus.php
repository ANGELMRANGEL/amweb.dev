<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-Type: text/html; charset=utf-8');
$v = '34';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>DGO+ - Mundial</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #000000;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
    </style>
</head>
<body>
    <!-- Bloqueador de clics -->
    <div id="blocker" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 2000000; pointer-events: auto; background: transparent;"></div>

    <!-- Contenedor del Iframe -->
    <div id="iframe-container" style="width: 100vw; height: 100vh; background: #000000; overflow: hidden; position: fixed; top: 0; left: 0; z-index: 1000001; opacity: 1; pointer-events: auto;">
        <iframe id="stream-iframe" src="https://latamvidzfy.org/dsportsplus.php" style="width: 100%; height: 100%; border: none;" allow="autoplay; encrypted-media; fullscreen" allowfullscreen></iframe>
    </div>

    <script>
        // Desactivar window.open
        window.open = function () {
            throw new Error('Popups deshabilitados');
        };

        // Escuchar botón atrás del control remoto o teclado (Soporta múltiples Smart TVs)
        function handleKeyDown(e) {
            const backKeys = [
                'Backspace', 'Escape', 'GoBack', 'Back', 'Cancel', 
                8, 27, 461, 10009, 220
            ];
            if (backKeys.includes(e.key) || backKeys.includes(e.keyCode)) {
                e.preventDefault();
                window.location.replace('index.php');
            }
        }
        document.addEventListener('keydown', handleKeyDown);
    </script>
</body>
</html>
