<?php
require 'db.php';
$socios = $db->getSocios();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pizarra Táctica</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;700&display=swap" rel="stylesheet">    <style>
        body { font-family: 'League Spartan', sans-serif; background-color: #0b0b0b; color: #e5e5e5; }
        
        /* Cancha de Fútbol */
        .field-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: radial-gradient(circle at center, #1a3a16 0%, #0b0b0b 100%);
        }

        .field {
            background-color: #2d5a27;
            background-image: 
                linear-gradient(rgba(255,255,255,0.1) 2px, transparent 2px),
                linear-gradient(90deg, rgba(255,255,255,0.1) 2px, transparent 2px);
            background-size: 50px 50px;
            position: relative;
            border: 3px solid rgba(255,255,255,0.8);
            aspect-ratio: 2/3;
            width: 100%;
            max-width: 400px;
            border-radius: 4px;
            box-shadow: 0 0 50px rgba(0,0,0,0.5);
        }
        
        .line-center {
            position: absolute;
            top: 50%; width: 100%; height: 2px; background: rgba(255,255,255,0.5);
        }
        .circle-center {
            position: absolute;
            top: 50%; left: 50%; width: 70px; height: 70px;
            border: 2px solid rgba(255,255,255,0.5); border-radius: 50%;
            transform: translate(-50%, -50%);
        }
        .area-top {
            position: absolute; top: 0; left: 50%; width: 140px; height: 50px;
            border: 2px solid rgba(255,255,255,0.5); border-top: none; transform: translateX(-50%);
        }
        .area-bottom {
            position: absolute; bottom: 0; left: 50%; width: 140px; height: 50px;
            border: 2px solid rgba(255,255,255,0.5); border-bottom: none; transform: translateX(-50%);
        }

        /* Jugador Arrastrable */
        .player-disk {
            width: 36px; height: 36px;
            background-color: #D62B2E;
            border: 2px solid white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 10px; color: white;
            cursor: grab; position: absolute;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            z-index: 50;
            touch-action: none;
        }
        .player-name-tag {
            position: absolute; top: 100%; left: 50%; transform: translateX(-50%);
            white-space: nowrap; font-size: 10px; text-transform: uppercase;
            color: white; margin-top: 2px; pointer-events: none; font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,1);
        }

        /* Ghost Position (Magnet) */
        .ghost-pos {
            position: absolute;
            width: 36px; height: 36px;
            border: 2px dashed rgba(255,255,255,0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 10;
        }

        /* Sidebar / Bottom Bar */
        .sidebar {
            background: #111; border-right: 1px solid #222;
            display: flex; flex-direction: column;
            z-index: 100;
        }
        .player-item {
            user-select: none; -webkit-user-select: none; touch-action: pan-x;
        }
        .player-item.dragging-active { opacity: 0.5; border-color: #D62B2E; }

        .btn-tool {
            background: #222; border: 1px solid #333; color: #888;
            font-size: 9px; font-weight: bold; padding: 6px 10px; border-radius: 4px;
            transition: all 0.2s; display: flex; items-center; gap: 4px;
        }
        .btn-tool.active {
            background: #D62B2E; border-color: white; color: white;
        }

        .btn-formation {
            background: #111; border: 1px solid #222; color: #666;
            font-size: 8px; font-weight: bold; padding: 3px 6px; border-radius: 3px;
            transition: all 0.2s;
        }
        .btn-formation.active {
            background: #333; border-color: #D62B2E; color: white;
        }

        /* Canvas Overlay */
        #tactical-canvas {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 40;
            pointer-events: none; /* Por defecto ignoramos clics */
        }
        #tactical-canvas.drawing-active {
            pointer-events: auto; /* Activamos para dibujar */
            cursor: crosshair;
        }

        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; border-right: none; border-top: 1px solid #222; order: 2; }
            .player-list-scroll { display: flex; overflow-x: auto; padding: 10px; gap: 10px; -webkit-overflow-scrolling: touch; }
            .player-item { flex: 0 0 auto; width: 90px; }
            .field-container { height: 60vh; order: 1; }
        }
        @media (min-width: 769px) {
            .sidebar { width: 320px; height: 100vh; }
            .player-list-scroll { display: flex; flex-direction: column; gap: 8px; padding: 15px; overflow-y: auto; }
            .field-container { height: 100vh; }
            .player-item { touch-action: auto; }
        }
    </style>
</head>
<body class="flex flex-col md:flex-row h-screen overflow-hidden">

    <!-- Sidebar -->
    <div class="sidebar shrink-0">
        <div class="p-4 border-b border-[#222] bg-[#151515]">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Herramientas</h2>
                <div class="flex gap-2">
                    <button id="btn-pencil" onclick="toggleDrawing()" class="btn-tool" title="Dibujar">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        LÁPIZ
                    </button>
                    <button onclick="clearField()" class="text-[9px] bg-[#222] px-2 py-1 rounded hover:bg-red-900 transition border border-[#333]">BORRAR</button>
                    <a href="admin.php" class="text-[9px] bg-black px-3 py-1 rounded hover:text-white transition border border-[#333] flex items-center">SALIR</a>
                </div>
            </div>

            <div class="mb-2">
                 <p class="text-[9px] text-gray-600 uppercase font-bold mb-2 tracking-tighter">Formaciones</p>
            <!-- Selector de Formaciones -->
            <div class="flex flex-wrap gap-1" id="formation-selector">
                <button onclick="setFormation('Libre', [])" class="btn-formation active">Libre</button>
                <button onclick="setFormation('4-4-2', [[50,92],[20,78],[40,78],[60,78],[80,78],[20,55],[40,55],[60,55],[80,55],[40,32],[60,32]])" class="btn-formation">4-4-2</button>
                <button onclick="setFormation('4-3-3', [[50,92],[20,78],[40,78],[60,78],[80,78],[30,55],[50,55],[70,55],[20,32],[50,22],[80,32]])" class="btn-formation">4-3-3</button>
                <button onclick="setFormation('4-2-3-1', [[50,92],[20,78],[40,78],[60,78],[80,78],[35,62],[65,62],[25,40],[50,40],[75,40],[50,22]])" class="btn-formation">4-2-3-1</button>
                <button onclick="setFormation('4-1-4-1', [[50,92],[20,78],[40,78],[60,78],[80,78],[50,65],[20,45],[40,45],[60,45],[80,45],[50,25]])" class="btn-formation">4-1-4-1</button>
                <button onclick="setFormation('4-4-2r', [[50,92],[20,78],[40,78],[60,78],[80,78],[50,65],[30,45],[70,45],[50,35],[40,20],[60,20]])" class="btn-formation">4-4-2r</button>
                <button onclick="setFormation('3-5-2', [[50,92],[30,78],[50,78],[70,78],[15,55],[35,55],[50,55],[65,55],[85,55],[40,25],[60,25]])" class="btn-formation">3-5-2</button>
                <button onclick="setFormation('3-4-3', [[50,92],[30,78],[50,78],[70,78],[20,55],[40,55],[60,55],[80,55],[25,25],[50,20],[75,25]])" class="btn-formation">3-4-3</button>
                <button onclick="setFormation('5-3-2', [[50,92],[15,78],[32,78],[50,78],[68,78],[85,78],[30,55],[50,55],[70,55],[40,25],[60,25]])" class="btn-formation">5-3-2</button>
                <button onclick="setFormation('5-4-1', [[50,92],[15,78],[32,78],[50,78],[68,78],[85,78],[20,55],[40,55],[60,55],[80,55],[50,25]])" class="btn-formation">5-4-1</button>
            </div>
            </div>
        </div>
        
        <div class="player-list-scroll grow" id="player-list" ondragover="allowDrop(event)" ondrop="removeDisk(event)">
            <?php foreach($socios as $s): ?>
                <div 
                    draggable="true" 
                    ondragstart="drag(event)" 
                    ontouchstart="touchStart(event)"
                    ontouchend="touchEnd(event)"
                    id="player-<?= $s['id'] ?>" 
                    data-name="<?= htmlspecialchars($s['nombre']) ?>"
                    class="player-item bg-black border border-[#222] p-2 rounded-lg flex flex-col items-center gap-1 cursor-move hover:border-[#D62B2E] transition"
                >
                    <div class="w-7 h-7 rounded-full bg-[#D62B2E] border-2 border-white flex items-center justify-center text-[10px] font-bold pointer-events-none">
                        <?= strtoupper(substr($s['nombre'], 0, 1)) ?>
                    </div>
                    <span class="text-[10px] text-white text-center leading-tight w-full break-words pointer-events-none"><?= htmlspecialchars($s['nombre']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Campo de Juego -->
    <div class="field-container" id="field-container" ondragover="allowDrop(event)" ondrop="drop(event)" ontouchmove="touchMove(event)" ontouchend="touchDrop(event)">
        <div class="field" id="football-field">
            <div class="line-center"></div>
            <div class="circle-center"></div>
            <div class="area-top"></div>
            <div class="area-bottom"></div>
            <div id="ghost-layer"></div>
            <canvas id="tactical-canvas"></canvas>
        </div>
    </div>

    <script>
        let touchTimer;
        let draggedItem = null;
        let isLongPress = false;
        let currentFormation = [];
        
        // Canvas Setup
        const canvas = document.getElementById('tactical-canvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let drawingEnabled = false;

        function resizeCanvas() {
            const field = document.getElementById('football-field');
            canvas.width = field.clientWidth;
            canvas.height = field.clientHeight;
        }
        window.addEventListener('resize', resizeCanvas);
        setTimeout(resizeCanvas, 100);

        function toggleDrawing() {
            drawingEnabled = !drawingEnabled;
            document.getElementById('btn-pencil').classList.toggle('active', drawingEnabled);
            canvas.classList.toggle('drawing-active', drawingEnabled);
        }

        // Logic Drawing
        canvas.onmousedown = (e) => {
            if (!drawingEnabled) return;
            isDrawing = true;
            ctx.beginPath();
            ctx.moveTo(e.offsetX, e.offsetY);
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.setLineDash([5, 5]); // Líneas punteadas para táctica
        };
        canvas.onmousemove = (e) => {
            if (isDrawing) {
                ctx.lineTo(e.offsetX, e.offsetY);
                ctx.stroke();
            }
        };
        canvas.onmouseup = () => isDrawing = false;

        // Touch Drawing
        canvas.ontouchstart = (e) => {
            if (!drawingEnabled) return;
            e.preventDefault();
            const touch = e.touches[0];
            const rect = canvas.getBoundingClientRect();
            isDrawing = true;
            ctx.beginPath();
            ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.setLineDash([5, 5]);
        };
        canvas.ontouchmove = (e) => {
            if (isDrawing && drawingEnabled) {
                e.preventDefault();
                const touch = e.touches[0];
                const rect = canvas.getBoundingClientRect();
                ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
                ctx.stroke();
            }
        };
        canvas.ontouchend = () => isDrawing = false;


        function setFormation(name, coords) {
            currentFormation = coords;
            document.querySelectorAll('.btn-formation').forEach(b => b.classList.remove('active'));
            event.target.classList.add('active');
            
            const layer = document.getElementById('ghost-layer');
            layer.innerHTML = '';
            coords.forEach(c => {
                const g = document.createElement('div');
                g.className = 'ghost-pos';
                g.style.left = c[0] + '%';
                g.style.top = c[1] + '%';
                layer.appendChild(g);
            });
        }

        function allowDrop(ev) {
            ev.preventDefault();
        }

        function drag(ev) {
            ev.dataTransfer.setData("text", ev.target.id);
            ev.dataTransfer.setData("name", ev.target.getAttribute("data-name") || "");
        }

        function touchStart(ev) {
            if (drawingEnabled) return; // No permitir drag si estamos dibujando
            const item = ev.currentTarget;
            isLongPress = false;
            touchTimer = setTimeout(() => {
                isLongPress = true;
                draggedItem = { id: item.id, name: item.getAttribute("data-name") };
                item.classList.add('dragging-active');
                if (navigator.vibrate) navigator.vibrate(50);
            }, 500);
        }

        function touchEnd(ev) {
            clearTimeout(touchTimer);
            if (!isLongPress) ev.currentTarget.classList.remove('dragging-active');
        }

        function touchMove(ev) {
            if (isLongPress) ev.preventDefault();
        }

        function touchDrop(ev) {
            if (isLongPress && draggedItem) {
                const touch = ev.changedTouches[0];
                const field = document.getElementById('football-field');
                const rect = field.getBoundingClientRect();
                
                if (touch.clientX >= rect.left && touch.clientX <= rect.right &&
                    touch.clientY >= rect.top && touch.clientY <= rect.bottom) {
                    drop({
                        preventDefault: () => {}, clientX: touch.clientX, clientY: touch.clientY,
                        dataTransfer: { getData: (t) => t === "text" ? draggedItem.id : draggedItem.name }
                    });
                }
                document.getElementById(draggedItem.id).classList.remove('dragging-active');
                draggedItem = null; isLongPress = false;
            }
        }

        function removeDisk(ev) {
            ev.preventDefault();
            const idKey = ev.dataTransfer.getData("text");
            const existing = document.getElementById('onfield-' + idKey);
            if (existing) {
                existing.remove();
                const listItem = document.getElementById(idKey);
                if (listItem) listItem.style.display = 'flex';
            }
        }

        function drop(ev) {
            ev.preventDefault();
            const idKey = ev.dataTransfer.getData("text");
            const name = ev.dataTransfer.getData("name");
            if (!idKey || !name) return;

            const field = document.getElementById('football-field');
            const rect = field.getBoundingClientRect();
            
            let x = ev.clientX - rect.left;
            let y = ev.clientY - rect.top;

            if (currentFormation.length > 0) {
                let minDist = 40;
                let snapPos = null;
                currentFormation.forEach(c => {
                    let cx = (c[0] / 100) * rect.width;
                    let cy = (c[1] / 100) * rect.height;
                    let d = Math.sqrt(Math.pow(x - cx, 2) + Math.pow(y - cy, 2));
                    if (d < minDist) { minDist = d; snapPos = { x: c[0], y: c[1] }; }
                });
                if (snapPos) {
                    x = (snapPos.x / 100) * rect.width;
                    y = (snapPos.y / 100) * rect.height;
                }
            }

            let posX = x - 18;
            let posY = y - 18;

            let existing = document.getElementById('onfield-' + idKey);
            if (existing) {
                existing.style.left = posX + 'px';
                existing.style.top = posY + 'px';
            } else {
                const listItem = document.getElementById(idKey);
                if (listItem) listItem.style.display = 'none';

                const disk = document.createElement('div');
                disk.id = 'onfield-' + idKey;
                disk.className = 'player-disk';
                disk.style.left = posX + 'px';
                disk.style.top = posY + 'px';
                disk.innerHTML = `${name.charAt(0).toUpperCase()}<div class="player-name-tag">${name}</div>`;
                
                disk.draggable = true;
                disk.ondragstart = (e) => {
                    if (drawingEnabled) { e.preventDefault(); return; }
                    e.dataTransfer.setData("text", idKey);
                    e.dataTransfer.setData("name", name);
                };
                disk.ontouchstart = (e) => {
                    if (drawingEnabled) return;
                    e.stopPropagation(); isLongPress = true; draggedItem = { id: idKey, name: name };
                };
                disk.onclick = () => {
                    disk.remove();
                    if (listItem) listItem.style.display = 'flex';
                };
                field.appendChild(disk);
            }
        }

        function clearField() {
            // Limpiar Jugadores
            document.querySelectorAll('.player-disk').forEach(d => {
                const idKey = d.id.replace('onfield-', '');
                const listItem = document.getElementById(idKey);
                if (listItem) listItem.style.display = 'flex';
                d.remove();
            });
            // Limpiar Canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }
    </script>
</body>
</html>
