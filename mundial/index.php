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
    <title>Mundial Portal</title>
    <link rel="manifest" href="manifest.json?v=<?php echo $v; ?>">
    <meta name="theme-color" content="#000000">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Mundial">
    <link rel="apple-touch-icon" sizes="180x180" href="amweb.png?v=<?php echo $v; ?>">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #000000;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #ffffff;
        }
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
            width: 100%;
            border: none;
        }
        .btn-home:focus {
            background: #ffffff !important;
            color: #000000 !important;
            box-shadow: 0 0 0 5px #00bcd4, 0 4px 25px rgba(0, 188, 212, 0.6) !important;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div style="width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 40px; background: #000000;">
        <img src="amweb.png?v=<?php echo $v; ?>" style="max-width: 180px; height: auto;" alt="Logo">
        <div style="display: flex; flex-direction: column; gap: 20px; width: 80%; max-width: 320px;">
            <button id="btn-dgo" class="btn-home" style="color: #000000; background: #ffffff; box-shadow: 0 4px 20px rgba(255, 255, 255, 0.15);">DGO</button>
            <button id="btn-dgoplus" class="btn-home" style="color: #ffffff; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2);">DGO+</button>
        </div>
    </div>

    <script>
        // Registrar Service Worker y forzar actualización
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js').catch(() => { });
            });

            let refreshing = false;
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                if (!refreshing) {
                    window.location.reload();
                    refreshing = true;
                }
            });
        }

        // Navegación
        const btnDgo = document.getElementById('btn-dgo');
        const btnDgoPlus = document.getElementById('btn-dgoplus');
        const focusable = [btnDgo, btnDgoPlus];
        let focusIndex = 0;

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
            window.location.href = 'dgo.php';
        });
        btnDgoPlus.addEventListener('click', () => {
            window.location.href = 'dgoplus.php';
        });
    </script>
</body>
</html>