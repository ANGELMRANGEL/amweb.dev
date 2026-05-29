<?php
require 'db.php';
$socios = $db->getSocios();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Táctica Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --ios-bg: #000000;
            --ios-card: #1c1c1e;
            --ios-accent: #ff3b30;
            --ios-glass: rgba(28, 28, 30, 0.7);
            --safe-bottom: env(safe-area-inset-bottom);
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: #000;
            color: white;
            margin: 0;
            padding: 0;
            overflow: hidden;
            height: 100vh;
            width: 100vw;
        }

        /* Main App Container */
        #app-container {
            width: 100%;
            height: 100dvh;
            background: var(--ios-bg);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Glassmorphism Components */
        .glass {
            background: var(--ios-glass);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-top: 0.5px solid rgba(255,255,255,0.1);
        }

        /* Bottom Tab Bar */
        .tab-bar {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: calc(80px + var(--safe-bottom));
            display: flex;
            justify-content: space-around;
            align-items: flex-start;
            padding-top: 15px;
            z-index: 500;
        }

        .tab-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: #8e8e93;
            transition: color 0.2s;
            cursor: pointer;
        }

        .tab-item.active {
            color: var(--ios-accent);
        }

        .tab-label {
            font-size: 10px;
            font-weight: 500;
        }

        /* Soccer Field Layout */
        .field-container {
            flex: 1;
            padding: 20px 20px calc(100px + var(--safe-bottom));
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at center, #1a3a16 0%, #000 100%);
        }

        .field {
            width: 100%;
            max-width: 600px;
            aspect-ratio: 2/3;
            background: #2d5a27;
            position: relative;
            border: 2px solid rgba(255,255,255,0.5);
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.8);
            overflow: hidden;
        }

        /* Player Disk Style */
        .player-disk {
            width: 44px;
            height: 44px;
            background: url('player.png') no-repeat center center;
            background-size: contain;
            position: absolute;
            z-index: 100;
            cursor: grab;
            touch-action: none;
            -webkit-filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.6));
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.6));
        }

        .player-name-tag {
            position: absolute;
            top: 110%;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.7);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
            backdrop-filter: blur(4px);
            pointer-events: none;
            color: white;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .ghost-pos {
            position: absolute;
            width: 36px;
            height: 36px;
            border: 2px dashed rgba(255,255,255,0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 10;
        }

        /* Bottom Sheet (Squad / Tactics) */
        .bottom-sheet {
            position: absolute;
            bottom: -100%;
            width: 100%;
            height: 70%;
            background: var(--ios-card);
            border-radius: 30px 30px 0 0;
            z-index: 600;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .bottom-sheet.open {
            transform: translateY(-100%);
        }

        .sheet-handle {
            width: 40px;
            height: 5px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            margin: 0 auto 20px;
        }

        #tactical-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 50;
            pointer-events: none;
        }

        #tactical-canvas.drawing {
            pointer-events: auto;
            cursor: crosshair;
        }
        
        .toast {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }
        .toast.show { opacity: 1; }
    </style>
</head>
<body>

    <div id="app-container">
        <!-- Notification Toast (Successor to Dynamic Island) -->
        <div id="toast" class="toast">4-4-2 Active</div>

        <!-- Main Content -->
        <div class="field-container">
            <div class="field" id="football-field" ondragover="allowDrop(event)" ondrop="drop(event)">
                <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: repeating-linear-gradient(rgba(255,255,255,0.1) 0 1px, transparent 1px 50px), repeating-linear-gradient(90deg, rgba(255,255,255,0.1) 0 1px, transparent 1px 50px);"></div>
                <div class="absolute top-[50%] w-full h-[1px] bg-white/30"></div>
                <div class="absolute top-[50%] left-[50%] w-24 h-24 border border-white/30 rounded-full translate-x-[-50%] translate-y-[-50%]"></div>
                
                <div id="ghost-layer"></div>
                <canvas id="tactical-canvas"></canvas>
            </div>
        </div>

        <!-- Tab Bar -->
        <nav class="tab-bar glass">
            <div class="tab-item" onclick="openSheet('squad')">
                <i data-lucide="users"></i>
                <span class="tab-label">Squad</span>
            </div>
            <div class="tab-item" onclick="openSheet('tactics')">
                <i data-lucide="layout"></i>
                <span class="tab-label">Tactics</span>
            </div>
            <div class="tab-item" onclick="toggleTool('pencil')" id="tab-pencil">
                <i data-lucide="pencil"></i>
                <span class="tab-label">Draw</span>
            </div>
            <div class="tab-item" onclick="clearBoard()">
                <i data-lucide="refresh-cw"></i>
                <span class="tab-label">Clear</span>
            </div>
            <div class="tab-item" onclick="window.location.href='admin.php'">
                <i data-lucide="log-out"></i>
                <span class="tab-label">Exit</span>
            </div>
        </nav>

        <!-- Sheets -->
        <!-- Squad Sheet -->
        <div id="sheet-squad" class="bottom-sheet glass">
            <div class="sheet-handle" onclick="closeSheets()"></div>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Plantilla</h3>
                <button onclick="closeSheets()" class="text-xs text-blue-500 font-bold">Listo</button>
            </div>
            <div class="overflow-y-auto grid grid-cols-3 gap-3 pb-20">
                <?php foreach($socios as $s): ?>
                <div 
                    draggable="true" 
                    ondragstart="drag(event)" 
                    ontouchstart="touchStart(event)"
                    id="player-<?= $s['id'] ?>" 
                    data-name="<?= htmlspecialchars($s['nombre']) ?>"
                    class="player-item bg-white/10 p-3 rounded-2xl flex flex-col items-center gap-2 active:scale-95 transition"
                >
                    <div class="w-10 h-10 bg-[url('player.png')] bg-contain bg-no-repeat bg-center"></div>
                    <span class="text-[10px] font-medium text-center truncate w-full"><?= htmlspecialchars($s['nombre']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tactics Sheet -->
        <div id="sheet-tactics" class="bottom-sheet glass">
            <div class="sheet-handle" onclick="closeSheets()"></div>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Formaciones</h3>
                <button onclick="closeSheets()" class="text-xs text-blue-500 font-bold">Listo</button>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <button onclick="setFormation('Libre', [])" class="bg-white/10 p-4 rounded-2xl font-bold text-sm">Libre</button>
                <button onclick="setFormation('4-4-2', [[50,90],[20,75],[40,75],[60,75],[80,75],[20,50],[40,50],[60,50],[80,50],[40,25],[60,25]])" class="bg-white/10 p-4 rounded-2xl font-bold text-sm">4-4-2</button>
                <button onclick="setFormation('4-3-3', [[50,90],[20,75],[40,75],[60,75],[80,75],[30,50],[50,50],[70,50],[20,25],[50,15],[80,25]])" class="bg-white/10 p-4 rounded-2xl font-bold text-sm">4-3-3</button>
                <button onclick="setFormation('3-5-2', [[50,90],[30,75],[50,75],[70,75],[15,50],[35,50],[50,50],[65,50],[85,50],[40,20],[60,20]])" class="bg-white/10 p-4 rounded-2xl font-bold text-sm">3-5-2</button>
                <button onclick="setFormation('5-4-1', [[50,92],[15,78],[32,78],[50,78],[68,78],[85,78],[20,55],[40,55],[60,55],[80,55],[50,25]])" class="bg-white/10 p-4 rounded-2xl font-bold text-sm">5-4-1</button>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // UI Logic
        const toast = document.getElementById('toast');

        function showToast(text) {
            toast.innerText = text;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 2500);
        }

        function openSheet(id) {
            closeSheets();
            document.getElementById('sheet-' + id).classList.add('open');
        }

        function closeSheets() {
            document.querySelectorAll('.bottom-sheet').forEach(s => s.classList.remove('open'));
        }

        // Tactical Logic
        const canvas = document.getElementById('tactical-canvas');
        const ctx = canvas.getContext('2d');
        const field = document.getElementById('football-field');
        let isDrawing = false;
        let drawingEnabled = false;

        function resizeCanvas() {
            canvas.width = field.clientWidth;
            canvas.height = field.clientHeight;
        }
        window.addEventListener('resize', resizeCanvas);
        setTimeout(resizeCanvas, 100);

        function toggleTool(tool) {
            if (tool === 'pencil') {
                drawingEnabled = !drawingEnabled;
                canvas.classList.toggle('drawing', drawingEnabled);
                document.getElementById('tab-pencil').classList.toggle('active', drawingEnabled);
                showToast(drawingEnabled ? 'Drawing Mode' : 'Planning Mode');
                closeSheets();
            }
        }

        function clearBoard() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.querySelectorAll('.player-disk').forEach(d => {
                const id = d.id.replace('onfield-', '');
                const orig = document.getElementById(id);
                if(orig) orig.style.opacity = '1';
                d.remove();
            });
            showToast('Board Cleared');
        }

        // Drawing Event Handlers
        canvas.onmousedown = (e) => {
            if (!drawingEnabled) return;
            isDrawing = true;
            ctx.beginPath();
            ctx.moveTo(e.offsetX, e.offsetY);
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.setLineDash([8, 8]);
        };
        canvas.onmousemove = (e) => {
            if (isDrawing) {
                ctx.lineTo(e.offsetX, e.offsetY);
                ctx.stroke();
            }
        };
        canvas.onmouseup = () => isDrawing = false;

        // Touch Interaction (Mobile)
        canvas.ontouchstart = (e) => {
            if (!drawingEnabled) return;
            const touch = e.touches[0];
            const rect = canvas.getBoundingClientRect();
            isDrawing = true;
            ctx.beginPath();
            ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 3;
        };
        canvas.ontouchmove = (e) => {
            if (isDrawing) {
                const touch = e.touches[0];
                const rect = canvas.getBoundingClientRect();
                ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
                ctx.stroke();
            }
        };
        canvas.ontouchend = () => isDrawing = false;

        // Drag & Drop
        let draggedData = null;

        function drag(ev) {
            ev.dataTransfer.setData("text", ev.target.id);
            ev.dataTransfer.setData("name", ev.target.getAttribute("data-name"));
        }

        function touchStart(ev) {
            const item = ev.currentTarget;
            draggedData = { id: item.id, name: item.getAttribute("data-name") };
            if (navigator.vibrate) navigator.vibrate(20);
        }

        function allowDrop(ev) { ev.preventDefault(); }

        function drop(ev) {
            ev.preventDefault();
            const id = ev.dataTransfer ? ev.dataTransfer.getData("text") : draggedData.id;
            const name = ev.dataTransfer ? ev.dataTransfer.getData("name") : draggedData.name;
            if (!id || !name) return;

            const rect = field.getBoundingClientRect();
            const clientX = ev.clientX || ev.changedTouches[0].clientX;
            const clientY = ev.clientY || ev.changedTouches[0].clientY;

            createDisk(id, name, clientX - rect.left, clientY - rect.top);
        }

        function createDisk(id, name, x, y) {
            let existing = document.getElementById('onfield-' + id);
            if (!existing) {
                existing = document.createElement('div');
                existing.id = 'onfield-' + id;
                existing.className = 'player-disk';
                existing.innerHTML = `<div class="player-name-tag">${name}</div>`;
                field.appendChild(existing);
                
                const orig = document.getElementById(id);
                if(orig) orig.style.opacity = '0.3';
                
                existing.onclick = () => {
                    existing.remove();
                    if(orig) orig.style.opacity = '1';
                };
            }
            
            existing.style.left = (x - 22) + 'px';
            existing.style.top = (y - 22) + 'px';
            
            if (navigator.vibrate) navigator.vibrate(10);
            showToast('Player Added');
        }

        // Formation Logic
        function setFormation(name, coords) {
            showToast(name);
            closeSheets();
            
            const layer = document.getElementById('ghost-layer');
            layer.innerHTML = '';
            
            coords.forEach(c => {
                const g = document.createElement('div');
                g.className = 'ghost-pos';
                g.style.left = c[0] + '%';
                g.style.top = c[1] + '%';
                layer.appendChild(g);
            });

            if (coords.length === 0) showToast('Free Mode');
        }
        
    </script>
</body>
</html>
