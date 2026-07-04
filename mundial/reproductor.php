<?php
$url = "https://latamvidzfy.org/dsportsar.php";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");

$response = curl_exec($ch);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $header_size);
$body = substr($response, $header_size);
curl_close($ch);

// Limpiar cabeceras de seguridad
$headers = explode("\r\n", $header);
foreach ($headers as $line) {
    if (preg_match('/^(X-Frame-Options|Content-Security-Policy|Frame-Options)/i', $line)) continue;
    header($line);
}

// Inyectamos todo el control directamente en el cuerpo del documento
$control_total = "
<script>
    // 1. Bloqueo total de ventanas emergentes (Anti-PopUp)
    (function() {
        var open = window.open;
        window.open = function() {
            console.log('Intento de publicidad bloqueado');
            return { focus: function(){}, close: function(){} };
        };
    })();

    // 2. Forzar todos los enlaces a abrirse en la misma pestaña
    document.addEventListener('click', function(e) {
        var el = e.target.closest('a');
        if(el) el.target = '_self';
    }, true);

    // 3. Autoplay forzado tras interacción
    window.addEventListener('DOMContentLoaded', function() {
        var video = document.querySelector('video');
        if(video) {
            video.autoplay = true;
            video.play().catch(function() {
                console.log('Esperando interacción para autoplay');
            });
        }
    });
</script>
";

// Inyectamos el control justo antes del cierre del body
$body = str_ireplace('</body>', $control_total . '</body>', $body);

echo $body;
?>