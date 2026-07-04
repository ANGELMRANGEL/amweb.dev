# Resumen de Cambios y Estado - PWA Mundial

Este documento resume las pruebas realizadas y el estado actual de la PWA para compatibilidad con iOS/Android y la mitigación de anuncios.

## 1. Compatibilidad iOS / Android (PWA)
- Se corrigieron etiquetas HTML mal cerradas en `index.php`.
- Se agregaron las metaetiquetas obligatorias para iOS (pantalla completa, barra translúcida y título).
- Se especificó el tamaño recomendado `180x180` para el icono de inicio en iOS (`apple-touch-icon`).
- Se agregó `viewport-fit=cover` al viewport para que la aplicación ocupe toda la pantalla detrás de la muesca (notch) en dispositivos modernos.
- Se agregó el evento `activate` en `sw.js` para limpiar automáticamente cachés obsoletas.
- Se implementó una variable de versión dinámica en PHP (`$v = time()`) para evitar que el navegador mantenga en caché recursos viejos.

## 2. Bloqueo de Publicidad sin romper el Reproductor
La página embebida (`https://latamvidzfy.org/dsportsar.php`) cuenta con un script anti-sandbox que detecta si el `iframe` está restringido (mediante un test de carga de objeto PDF que falla bajo sandbox) y redirige a una pantalla de bloqueo. Por ende, no es posible usar el atributo `sandbox` de HTML5.

### Solución Aplicada (CSP en iframe):
- Agregamos la cabecera de seguridad **CSP (Content Security Policy)** directamente en el atributo del `iframe`:
  ```html
  csp="script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.bitmovin.com https://*.bitmovin.com;"
  ```
- **Resultado:** Bloquea por completo la carga de scripts externos de publicidad (como `acscdn.com/script/aclib.js`) antes de que el navegador los descargue, eliminando los popups de publicidad sin activar la detección anti-sandbox de la web.

## 3. Solución de Autoplay (Safari / Chrome)
Safari en iOS prohíbe el inicio automático de videos con sonido a menos que ocurra una interacción (toque físico) del usuario directamente dentro de la zona del reproductor.

### Solución Aplicada (Superposición Transparente):
1. **Iframe cargado al inicio:** El `iframe` se inicializa cargando la transmisión directamente en el HTML.
2. **Capa Invisible:** El contenedor del `iframe` se coloca encima del botón "PLAY" decorativo con una opacidad del 0.1% (`opacity: 0.001`), haciéndolo invisible pero completamente interactivo.
3. **El Clic:** Cuando el usuario presiona el botón visual de "PLAY", físicamente está tocando dentro de la pantalla del reproductor. Esto satisface la política de Safari y activa el autoplay con sonido.
4. **Foco y Bloqueo:** El script detecta el foco inmediato en el `iframe`, hace visible el reproductor (`opacity: 1`), oculta el overlay de fondo y activa el bloqueador de pantalla (`#blocker` con `pointer-events: auto`) tras 500 ms para evitar que toques futuros desencadenen otros comportamientos indeseados.

---

## Estado Actual (Versión del Service Worker: `mundial-v9`)
1. Los archivos actualizados en el servidor son `index.php` y `sw.js`.
2. Para probar, es indispensable cerrar la pestaña de la PWA, limpiar la caché del navegador del móvil (o entrar en modo incógnito) para forzar la descarga de la última versión del Service Worker.
