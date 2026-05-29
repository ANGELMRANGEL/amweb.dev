<?php
require 'db.php';
$socios = $db->getSocios();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Táctica Pro iPad Pro v2</title>
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
            --safe-top: env(safe-area-inset-top);
            --safe-bottom: env(safe-area-inset-bottom);
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: #000;
            color: white;
            margin: 0;
            padding: 0;
            overflow: hidden;
            display: flex;
            height: 100dvh;
            width: 100vw;
        }

        .glass {
            background: var(--ios-glass);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-right: 0.5px solid rgba(255, 255, 255, 0.1);
            border-top: 0.5px solid rgba(255, 255, 255, 0.1);
        }

        /* Sidebar */
        .sidebar {
            position: absolute;
            left: -240px;
            top: 0;
            width: 240px;
            height: 100vh;
            z-index: 3000;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            padding: calc(20px + var(--safe-top)) 20px 20px;
        }

        .sidebar.open {
            transform: translateX(241px);
        }

        /* Campo de Juego Elástico con área segura */
        .main-stage {
            flex: 1;
            position: relative;
            background: radial-gradient(circle at center, #1a3a16 0%, #000 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 10px;
            padding-top: calc(14vw + var(--safe-top));
            /* Reservar espacio para la botonera arriba */
            padding-bottom: calc(45px + var(--safe-bottom));
            /* Reservar espacio para el slider abajo */
        }

        .field {
            width: 100%;
            height: 100%;
            background-color: #1e3d1a;
            background-image:
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 50%, transparent 50%),
                linear-gradient(0deg, #234d1e 50%, #1e3d1a 50%);
            background-size: 80px 100%, 100% 80px;
            position: relative;
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.8), inset 0 0 50px rgba(0, 0, 0, 0.4);
            overflow: hidden;
        }

        .field-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 80%;
            max-width: 800px;
            transform: translate3d(-50%, -50%, 0);
            opacity: 0.05;
            filter: grayscale(100%) blur(0);
            pointer-events: none;
            z-index: 5;
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
            -webkit-perspective: 1000;
            perspective: 1000;
            will-change: transform, opacity;
        }

        /* Barra de Herramientas Dinámica por Ancho (XL) */
        .tab-bar {
            position: absolute;
            top: calc(10px + var(--safe-top));
            left: 50%;
            transform: translateX(-50%);
            width: 95%;
            max-width: 900px;
            height: 12vw;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2vw;
            border-radius: 3vw;
            z-index: 2100;
            border: 0.5px solid rgba(255, 255, 255, 0.1);
        }

        .tab-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #8e8e93;
            cursor: pointer;
            flex: 1;
            min-width: 0;
            transition: all 0.2s;
        }

        .tab-item i,
        .tab-item svg {
            width: 4vw !important;
            height: 4vw !important;
        }

        .tab-item span {
            font-size: 1.4vw !important;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            text-align: center;
        }

        .tab-item.active {
            color: var(--ios-accent);
        }

        .tab-item.active-def {
            color: #0A84FF;
            text-shadow: 0 0 10px rgba(10, 132, 255, 0.5);
        }

        .tab-item.active-ofe {
            color: var(--ios-accent);
            text-shadow: 0 0 10px rgba(255, 59, 48, 0.5);
        }

        .player-disk {
            width: 11vw;
            height: 11vw;
            background: url('player.png') no-repeat center center;
            background-size: contain;
            position: absolute;
            z-index: 100;
            pointer-events: none; /* No seleccionables */
            touch-action: none;
            filter: drop-shadow(0 0.5vw 0.8vw rgba(0, 0, 0, 0.6));
        }

        .player-disk.gk-disk {
            background-image: url('portero.png');
            background-color: transparent;
            border: none;
            border-radius: 0;
            width: 11vw;
            height: 11vw;
            box-shadow: none;
        }

        /* Tooltip Balon */
        .ball-tooltip {
            position: absolute;
            background: var(--ios-accent);
            color: white;
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 800;
            white-space: nowrap;
            z-index: 3000;
            pointer-events: none;
            transform: translate(-50%, -130%);
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            animation: bounce 1.5s infinite;
        }

        .ball-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 8px solid transparent;
            border-top-color: var(--ios-accent);
        }

        @keyframes bounce {
            0%, 100% { transform: translate(-50%, -130%); }
            50% { transform: translate(-50%, -145%); }
        }

        .ball-disk {
            width: 7vw;
            height: 7vw;
            background: radial-gradient(circle at 30% 30%, #fff, #ccc);
            border-radius: 50%;
            position: absolute;
            z-index: 150;
            cursor: grab;
            touch-action: none;
            box-shadow: 0 0.5vw 1.2vw rgba(0, 0, 0, 0.5);
            border: 0.2vw solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .ball-disk::after {
            content: '';
            width: 100%;
            height: 100%;
            background-image:
                radial-gradient(circle at 50% 50%, #000 20%, transparent 25%),
                radial-gradient(circle at 0% 0%, #000 20%, transparent 25%),
                radial-gradient(circle at 100% 0%, #000 20%, transparent 25%),
                radial-gradient(circle at 0% 100%, #000 20%, transparent 25%),
                radial-gradient(circle at 100% 100%, #000 20%, transparent 25%);
            background-size: 15px 15px;
            opacity: 0.8;
        }

        .player-name-tag {
            position: absolute;
            top: 110%;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.2vw 1vw;
            border-radius: 0.5vw;
            font-size: 3vw;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
            backdrop-filter: blur(4px);
            color: white;
            pointer-events: none;
        }

        .ghost-pos {
            position: absolute;
            width: 4.5vw;
            height: 4.5vw;
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 10;
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
        }

        .toast {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -40%) scale(0.9);
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(25px) saturate(180%);
            -webkit-backdrop-filter: blur(25px) saturate(180%);
            padding: 24px 48px;
            border-radius: 50px;
            font-size: 20px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #fff;
            z-index: 5000;
            opacity: 0;
            transition: all 0.6s cubic-bezier(0.23, 1, 0.32, 1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            pointer-events: none;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.6);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            line-height: 1.4;
        }

        .toast.show {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }

        .tactics-flyout {
            position: absolute;
            top: 100px;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            background: var(--ios-card);
            padding: 20px;
            border-radius: 20px;
            display: none;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            z-index: 400;
            opacity: 0;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            width: 90vw;
            max-width: 600px;
        }

        .tactics-flyout.open {
            display: grid;
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        /* Estilos para Mini-Pizarras */
        .mini-board {
            width: 100%;
            aspect-ratio: 4/5;
            background: #2d5a27;
            border-radius: 8px;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 8px;
            pointer-events: none;
        }

        .mini-dot {
            width: 4px;
            height: 4px;
            background: white;
            border-radius: 50%;
            position: absolute;
            transform: translate(-50%, -50%);
        }

        .tactic-btn {
            background: rgba(255, 255, 255, 0.05);
            padding: 10px;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .tactic-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .tactic-name {
            font-size: 10px;
            font-weight: 800;
            color: white;
            text-transform: uppercase;
        }

        /* Slider de Intensidad Minimalista */
        .intensity-panel {
            position: absolute;
            bottom: calc(10px + var(--safe-bottom));
            /* Un poco más arriba para que se vea completo */
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            z-index: 2200;
            padding: 0;
            background: none;
            border: none;
            display: flex;
            align-items: center;
            height: 20px;
            /* Asegurar espacio para el thumb */
        }

        .intensity-slider {
            -webkit-appearance: none;
            width: 100%;
            height: 4px;
            /* Un poco más gruesa para que sea más fácil de tocar */
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            outline: none;
        }

        .intensity-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 22px;
            height: 22px;
            background: #fff;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.6);
            border: 2px solid var(--ios-accent);
        }

        .intensity-labels {
            display: none;
            /* Sin etiquetas como ha pedido el usuario */
        }

        #tactical-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 50;
            pointer-events: none;
            touch-action: none;
        }

        #tactical-canvas.drawing {
            pointer-events: auto;
            z-index: 2000;
            /* Encima de jugadores al rayar */
        }
    </style>
</head>

<body>

    <div class="main-stage">
        <div id="toast" class="toast">Actualizado</div>

        <div class="field" id="football-field">
            <!-- Marcado elástico -->
            <div class="absolute inset-0 opacity-10 pointer-events-none"
                style="background-image: repeating-linear-gradient(rgba(255,255,255,0.2) 0 1px, transparent 1px 10%);">
            </div>
            <div class="absolute top-[50%] w-full h-[1px] bg-white/40"></div>
            <div
                class="absolute top-[50%] left-[50%] w-24 h-24 border-2 border-white/40 rounded-full translate-x-[-50%] translate-y-[-50%]">
            </div>

            <!-- Áreas Porcentuales -->
            <div class="absolute top-0 left-1/2 w-1/3 h-[12%] border-2 border-white/40 border-t-0 -translate-x-1/2">
            </div>
            <div class="absolute bottom-0 left-1/2 w-1/3 h-[12%] border-2 border-white/40 border-b-0 -translate-x-1/2">
            </div>

            <!-- Escudo Marca de Agua -->
            <img src="logo.png" class="field-watermark">

            <div id="ghost-layer"></div>
            <canvas id="tactical-canvas"></canvas>
        </div>

        <div id="flyout-tactics" class="tactics-flyout glass">
            <!-- 4-4-2 -->
            <button
                onclick="setFormation('4-4-2', [[50,92],[20,75],[40,75],[60,75],[80,75],[20,50],[40,50],[60,50],[80,50],[40,25],[60,25]])"
                class="tactic-btn">
                <div class="mini-board">
                    <div class="mini-dot" style="left:50%; top:92%;"></div>
                    <div class="mini-dot" style="left:20%; top:75%;"></div>
                    <div class="mini-dot" style="left:40%; top:75%;"></div>
                    <div class="mini-dot" style="left:60%; top:75%;"></div>
                    <div class="mini-dot" style="left:80%; top:75%;"></div>
                    <div class="mini-dot" style="left:20%; top:50%;"></div>
                    <div class="mini-dot" style="left:40%; top:50%;"></div>
                    <div class="mini-dot" style="left:60%; top:50%;"></div>
                    <div class="mini-dot" style="left:80%; top:50%;"></div>
                    <div class="mini-dot" style="left:40%; top:25%;"></div>
                    <div class="mini-dot" style="left:60%; top:25%;"></div>
                </div>
                <span class="tactic-name">4-4-2</span>
            </button>

            <!-- 4-3-3 -->
            <button
                onclick="setFormation('4-3-3', [[50,92],[20,75],[40,75],[60,75],[80,75],[30,50],[50,50],[70,50],[20,25],[50,15],[80,25]])"
                class="tactic-btn">
                <div class="mini-board">
                    <div class="mini-dot" style="left:50%; top:92%;"></div>
                    <div class="mini-dot" style="left:20%; top:75%;"></div>
                    <div class="mini-dot" style="left:40%; top:75%;"></div>
                    <div class="mini-dot" style="left:60%; top:75%;"></div>
                    <div class="mini-dot" style="left:80%; top:75%;"></div>
                    <div class="mini-dot" style="left:30%; top:50%;"></div>
                    <div class="mini-dot" style="left:50%; top:50%;"></div>
                    <div class="mini-dot" style="left:70%; top:50%;"></div>
                    <div class="mini-dot" style="left:20%; top:25%;"></div>
                    <div class="mini-dot" style="left:50%; top:15%;"></div>
                    <div class="mini-dot" style="left:80%; top:25%;"></div>
                </div>
                <span class="tactic-name">4-3-3</span>
            </button>

            <!-- 4-2-3-1 -->
            <button
                onclick="setFormation('4-2-3-1', [[50,92],[20,75],[40,75],[60,75],[80,75],[40,60],[60,60],[20,40],[50,40],[80,40],[50,20]])"
                class="tactic-btn">
                <div class="mini-board">
                    <div class="mini-dot" style="left:50%; top:92%;"></div>
                    <div class="mini-dot" style="left:20%; top:75%;"></div>
                    <div class="mini-dot" style="left:40%; top:75%;"></div>
                    <div class="mini-dot" style="left:60%; top:75%;"></div>
                    <div class="mini-dot" style="left:80%; top:75%;"></div>
                    <div class="mini-dot" style="left:40%; top:60%;"></div>
                    <div class="mini-dot" style="left:60%; top:60%;"></div>
                    <div class="mini-dot" style="left:20%; top:40%;"></div>
                    <div class="mini-dot" style="left:50%; top:40%;"></div>
                    <div class="mini-dot" style="left:80%; top:40%;"></div>
                    <div class="mini-dot" style="left:50%; top:20%;"></div>
                </div>
                <span class="tactic-name">4-2-3-1</span>
            </button>

            <!-- 3-5-2 -->
            <button
                onclick="setFormation('3-5-2', [[50,92],[30,75],[50,75],[70,75],[15,50],[35,50],[50,50],[65,50],[85,50],[40,25],[60,25]])"
                class="tactic-btn">
                <div class="mini-board">
                    <div class="mini-dot" style="left:50%; top:92%;"></div>
                    <div class="mini-dot" style="left:30%; top:75%;"></div>
                    <div class="mini-dot" style="left:50%; top:75%;"></div>
                    <div class="mini-dot" style="left:70%; top:75%;"></div>
                    <div class="mini-dot" style="left:15%; top:50%;"></div>
                    <div class="mini-dot" style="left:35%; top:50%;"></div>
                    <div class="mini-dot" style="left:50%; top:50%;"></div>
                    <div class="mini-dot" style="left:65%; top:50%;"></div>
                    <div class="mini-dot" style="left:85%; top:50%;"></div>
                    <div class="mini-dot" style="left:40%; top:25%;"></div>
                    <div class="mini-dot" style="left:60%; top:25%;"></div>
                </div>
                <span class="tactic-name">3-5-2</span>
            </button>

            <!-- 5-3-2 -->
            <button
                onclick="setFormation('5-3-2', [[50,92],[15,75],[30,75],[50,75],[70,75],[85,75],[30,50],[50,50],[70,50],[40,25],[60,25]])"
                class="tactic-btn">
                <div class="mini-board">
                    <div class="mini-dot" style="left:50%; top:92%;"></div>
                    <div class="mini-dot" style="left:15%; top:75%;"></div>
                    <div class="mini-dot" style="left:30%; top:75%;"></div>
                    <div class="mini-dot" style="left:50%; top:75%;"></div>
                    <div class="mini-dot" style="left:70%; top:75%;"></div>
                    <div class="mini-dot" style="left:85%; top:75%;"></div>
                    <div class="mini-dot" style="left:30%; top:50%;"></div>
                    <div class="mini-dot" style="left:50%; top:50%;"></div>
                    <div class="mini-dot" style="left:70%; top:50%;"></div>
                    <div class="mini-dot" style="left:40%; top:25%;"></div>
                    <div class="mini-dot" style="left:60%; top:25%;"></div>
                </div>
                <span class="tactic-name">5-3-2</span>
            </button>

            <!-- 4-1-4-1 -->
            <button
                onclick="setFormation('4-1-4-1', [[50,92],[20,75],[40,75],[60,75],[80,75],[50,60],[20,45],[40,45],[60,45],[80,45],[50,20]])"
                class="tactic-btn">
                <div class="mini-board">
                    <div class="mini-dot" style="left:50%; top:92%;"></div>
                    <div class="mini-dot" style="left:20%; top:75%;"></div>
                    <div class="mini-dot" style="left:40%; top:75%;"></div>
                    <div class="mini-dot" style="left:60%; top:75%;"></div>
                    <div class="mini-dot" style="left:80%; top:75%;"></div>
                    <div class="mini-dot" style="left:50%; top:60%;"></div>
                    <div class="mini-dot" style="left:20%; top:45%;"></div>
                    <div class="mini-dot" style="left:40%; top:45%;"></div>
                    <div class="mini-dot" style="left:60%; top:45%;"></div>
                    <div class="mini-dot" style="left:80%; top:45%;"></div>
                    <div class="mini-dot" style="left:50%; top:20%;"></div>
                </div>
                <span class="tactic-name">4-1-4-1</span>
            </button>

            <!-- 3-4-3 -->
            <button
                onclick="setFormation('3-4-3', [[50,92],[30,75],[50,75],[70,75],[20,50],[40,50],[60,50],[80,50],[20,25],[50,15],[80,25]])"
                class="tactic-btn">
                <div class="mini-board">
                    <div class="mini-dot" style="left:50%; top:92%;"></div>
                    <div class="mini-dot" style="left:30%; top:75%;"></div>
                    <div class="mini-dot" style="left:50%; top:75%;"></div>
                    <div class="mini-dot" style="left:70%; top:75%;"></div>
                    <div class="mini-dot" style="left:20%; top:50%;"></div>
                    <div class="mini-dot" style="left:40%; top:50%;"></div>
                    <div class="mini-dot" style="left:60%; top:50%;"></div>
                    <div class="mini-dot" style="left:80%; top:50%;"></div>
                    <div class="mini-dot" style="left:20%; top:25%;"></div>
                    <div class="mini-dot" style="left:50%; top:15%;"></div>
                    <div class="mini-dot" style="left:80%; top:25%;"></div>
                </div>
                <span class="tactic-name">3-4-3</span>
            </button>

            <!-- 4-4-2 R -->
            <button
                onclick="setFormation('4-4-2 D', [[50,92],[20,75],[40,75],[60,75],[80,75],[50,65],[30,50],[70,50],[50,35],[40,20],[60,20]])"
                class="tactic-btn">
                <div class="mini-board">
                    <div class="mini-dot" style="left:50%; top:92%;"></div>
                    <div class="mini-dot" style="left:20%; top:75%;"></div>
                    <div class="mini-dot" style="left:40%; top:75%;"></div>
                    <div class="mini-dot" style="left:60%; top:75%;"></div>
                    <div class="mini-dot" style="left:80%; top:75%;"></div>
                    <div class="mini-dot" style="left:50%; top:65%;"></div>
                    <div class="mini-dot" style="left:30%; top:50%;"></div>
                    <div class="mini-dot" style="left:70%; top:50%;"></div>
                    <div class="mini-dot" style="left:50%; top:35%;"></div>
                    <div class="mini-dot" style="left:40%; top:20%;"></div>
                    <div class="mini-dot" style="left:60%; top:20%;"></div>
                </div>
                <span class="tactic-name">4-4-2 R</span>
            </button>

            <!-- 5-4-1 -->
            <button
                onclick="setFormation('5-4-1', [[50,92],[15,75],[30,75],[50,75],[70,75],[85,75],[20,50],[40,50],[60,50],[80,50],[50,20]])"
                class="tactic-btn">
                <div class="mini-board">
                    <div class="mini-dot" style="left:50%; top:92%;"></div>
                    <div class="mini-dot" style="left:15%; top:75%;"></div>
                    <div class="mini-dot" style="left:30%; top:75%;"></div>
                    <div class="mini-dot" style="left:50%; top:75%;"></div>
                    <div class="mini-dot" style="left:70%; top:75%;"></div>
                    <div class="mini-dot" style="left:85%; top:75%;"></div>
                    <div class="mini-dot" style="left:20%; top:50%;"></div>
                    <div class="mini-dot" style="left:40%; top:50%;"></div>
                    <div class="mini-dot" style="left:60%; top:50%;"></div>
                    <div class="mini-dot" style="left:80%; top:50%;"></div>
                    <div class="mini-dot" style="left:50%; top:20%;"></div>
                </div>
                <span class="tactic-name">5-4-1</span>
            </button>
        </div>

        <div class="intensity-panel">
            <input type="range" min="0" max="100" value="50" class="intensity-slider" id="intensity-slider"
                oninput="updateTeamIntensitySlider(this.value)">
        </div>

        <nav class="tab-bar glass">
            <div class="tab-item" onclick="toggleFlyout('tactics')">
                <i data-lucide="layout"></i>
                <span class="text-[10px] uppercase font-bold mt-1">Tácticas</span>
            </div>
            <div class="tab-item" onclick="toggleMagnet('DEF')" id="tab-def">
                <i data-lucide="shield-check"></i>
                <span class="text-[10px] uppercase font-bold mt-1">Defensivo</span>
            </div>
            <div class="tab-item" onclick="toggleMagnet('OFE')" id="tab-ofe">
                <i data-lucide="zap"></i>
                <span class="text-[10px] uppercase font-bold mt-1">Ofensivo</span>
            </div>
            <div class="tab-item" id="tab-ball" onclick="addBall()">
                <i data-lucide="circle-dot"></i>
                <span class="text-[10px] uppercase font-bold mt-1">Balón</span>
            </div>
            <div class="tab-item" onclick="window.location.href='index.php'">
                <i data-lucide="log-out"></i>
                <span class="text-[10px] uppercase font-bold mt-1">Salir</span>
            </div>
        </nav>
    </div>

    <script>
        function showToast(text) {
            const toast = document.getElementById('toast');
            toast.innerHTML = text; // Cambiado a innerHTML para soportar <br>
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2000);
        }
        function toggleSidebar() { document.getElementById('sidebar-squad').classList.toggle('open'); }
        function toggleFlyout(id) { document.getElementById('flyout-' + id).classList.toggle('open'); }

        const canvas = document.getElementById('tactical-canvas');
        const ctx = canvas.getContext('2d');
        const field = document.getElementById('football-field');
        let isDrawing = false;
        let drawingEnabled = false;
        let currentFormationCoords = [];
        
        // Estado Físico Táctico
        let currentIntensityValue = 50; 
        let currentBallShiftX = 0;
        let tacticalMode = 'OFF'; // 'OFF', 'DEF', 'OFE'

        // Historial de Dibujo
        let drawingHistory = [];
        let redoStack = [];
        let currentStroke = [];

        function resizeCanvas() {
            canvas.width = field.clientWidth;
            canvas.height = field.clientHeight;
            redrawCanvas(); // Redibujar tras redimensionar
        }
        window.addEventListener('resize', resizeCanvas);
        setTimeout(resizeCanvas, 100);

        function toggleTool(tool) {
            if (tool === 'pencil') {
                drawingEnabled = !drawingEnabled;
                canvas.classList.toggle('drawing', drawingEnabled);
                document.getElementById('tab-pencil').classList.toggle('active', drawingEnabled);
                showToast(drawingEnabled ? 'Pincel ON' : 'Pincel OFF');
            }
        }

        function clearBoard() {
            if (!confirm("¿Borrar dibujo y jugadores?")) return;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            drawingHistory = [];
            redoStack = [];
            currentIntensityValue = 50;
            currentBallShiftX = 0;
            document.getElementById('intensity-slider').value = 50;
            if (tacticalMode !== 'OFF') toggleMagnet(tacticalMode);
            
            document.querySelectorAll('.player-disk, .ball-disk').forEach(d => {
                if (d.id && d.id.startsWith('onfield-')) {
                    const id = d.id.replace('onfield-', '');
                    const orig = document.getElementById('player-' + id);
                    if (orig) orig.style.opacity = '1';
                }
                d.remove();
            });
        }

        function toggleMagnet(mode) {
            if (tacticalMode === mode) {
                tacticalMode = 'OFF';
            } else {
                tacticalMode = mode;
            }
            
            // UI Updates
            document.getElementById('tab-def').classList.remove('active-def');
            document.getElementById('tab-ofe').classList.remove('active-ofe');
            
            if (tacticalMode === 'DEF') {
                document.getElementById('tab-def').classList.add('active-def');
                showToast('Modo Defensivo<br><span class="text-sm opacity-80 font-semibold">Mueve el balón</span>');
            } else if (tacticalMode === 'OFE') {
                document.getElementById('tab-ofe').classList.add('active-ofe');
                showToast('Modo Ofensivo<br><span class="text-sm opacity-80 font-semibold">Mueve el balón</span>');
            } else {
                showToast('Modo Manual');
            }

            if (tacticalMode === 'OFF') {
                currentBallShiftX = 0;
                currentIntensityValue = parseInt(document.getElementById('intensity-slider').value);
                renderTeamPositions();
            } else {
                triggerBallMagnetUpdate();
            }
        }

        function triggerBallMagnetUpdate() {
            const ball = document.querySelector('.ball-disk');
            if (!ball) return;
            const rect = field.getBoundingClientRect();
            const centerX = ball.offsetLeft + ball.offsetWidth / 2;
            const centerY = ball.offsetTop + ball.offsetHeight / 2;
            let ballPctX = (centerX / rect.width) * 100;
            let ballPctY = (centerY / rect.height) * 100;
            
            currentBallShiftX = ballPctX - 50;
            let rawBallIntensityY = (50 - ballPctY) / 50; // -1 to 1 
            
            if (tacticalMode === 'OFE') {
                let val = 50 + (rawBallIntensityY * 50); 
                currentIntensityValue = Math.max(0, Math.min(100, val));
            } else if (tacticalMode === 'DEF') {
                let val = 50 + (rawBallIntensityY * 50) - 20; 
                currentIntensityValue = Math.max(0, Math.min(60, val)); 
            }
            
            document.getElementById('intensity-slider').value = currentIntensityValue;
            renderTeamPositions();
        }

        function startDrawing(x, y) {
            if (!drawingEnabled) return;
            isDrawing = true;
            currentStroke = [[x, y]];

            ctx.beginPath();
            ctx.moveTo(x, y);
            setupDrawingStyle();
        }

        function setupDrawingStyle() {
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 4;
            ctx.setLineDash([8, 8]);
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }

        function doDrawing(x, y) {
            if (isDrawing) {
                currentStroke.push([x, y]);
                ctx.lineTo(x, y);
                ctx.stroke();
            }
        }

        function endDrawing() {
            if (isDrawing) {
                isDrawing = false;
                drawingHistory.push(currentStroke);
                redoStack = [];
                currentStroke = [];
            }
        }

        function undo() {
            if (drawingHistory.length === 0) return;
            redoStack.push(drawingHistory.pop());
            redrawCanvas();
            showToast('Deshacer');
        }

        function redo() {
            if (redoStack.length === 0) return;
            drawingHistory.push(redoStack.pop());
            redrawCanvas();
            showToast('Rehacer');
        }

        function redrawCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            setupDrawingStyle();
            drawingHistory.forEach(stroke => {
                if (stroke.length < 2) return;
                ctx.beginPath();
                ctx.moveTo(stroke[0][0], stroke[0][1]);
                for (let i = 1; i < stroke.length; i++) {
                    ctx.lineTo(stroke[i][0], stroke[i][1]);
                }
                ctx.stroke();
            });
        }

        canvas.onmousedown = (e) => startDrawing(e.offsetX, e.offsetY);
        canvas.onmousemove = (e) => doDrawing(e.offsetX, e.offsetY);
        canvas.onmouseup = endDrawing;
        canvas.onmouseleave = endDrawing;

        // Soporte Touch Mejorado
        canvas.addEventListener('touchstart', (e) => {
            if (!drawingEnabled) return;
            e.preventDefault();
            const t = e.touches[0];
            const r = canvas.getBoundingClientRect();
            startDrawing(t.clientX - r.left, t.clientY - r.top);
        }, { passive: false });

        canvas.addEventListener('touchmove', (e) => {
            if (!drawingEnabled) return;
            e.preventDefault();
            const t = e.touches[0];
            const r = canvas.getBoundingClientRect();
            doDrawing(t.clientX - r.left, t.clientY - r.top);
        }, { passive: false });

        canvas.addEventListener('touchend', (e) => {
            e.preventDefault();
            endDrawing();
        }, { passive: false });

        function addBall() {
            const ball = document.createElement('div');
            ball.className = 'ball-disk';
            field.appendChild(ball);
            ball.style.left = '50%';
            ball.style.top = '55%';
            ball.style.transform = 'translate(-50%, -50%)';
            initDraggable(ball, 'ball');
            showToast('Balón en juego');
        }

        function categorizeLine(y) {
            if (y > 85) return 'GK';
            if (y > 60) return 'DF';
            if (y > 35) return 'MF';
            return 'FW';
        }

        function addPlayerManual(id, name) {
            if (document.getElementById('onfield-' + id)) return;

            const playersCount = document.querySelectorAll('.player-disk').length;
            if (playersCount >= 11) {
                showToast('Máximo 11 jugadores');
                return;
            }

            let posX = 50;
            let posY = 50;

            if (currentFormationCoords.length > 0 && playersCount < currentFormationCoords.length) {
                posX = currentFormationCoords[playersCount][0];
                posY = currentFormationCoords[playersCount][1];
            }

            const disk = document.createElement('div');
            disk.id = 'onfield-' + id;
            disk.className = 'player-disk';
            disk.dataset.baseX = posX;
            disk.dataset.baseY = posY;
            disk.dataset.line = categorizeLine(posY);
            disk.innerHTML = `<div class="player-name-tag">${name}</div>`;
            field.appendChild(disk);

            const orig = document.getElementById('player-' + id);
            if (orig) orig.style.opacity = '0.3';

            applyTacticalPosition(disk, posX, posY);
            initDraggable(disk, id);

            // Re-apply current intensity
            updateTeamIntensity(document.getElementById('intensity-slider').value);
        }

        function applyTacticalPosition(el, x, y) {
            el.style.left = x + '%';
            el.style.top = y + '%';
            el.style.transform = 'translate(-50%, -50%)';
        }

        function initDraggable(el, id) {
            let startX, startY, origX, origY, isDraggingLocal = false;

            const onStart = (e) => {
                if (drawingEnabled) return;
                isDraggingLocal = true;
                const t = e.touches ? e.touches[0] : e;
                startX = t.clientX; startY = t.clientY;
                origX = el.offsetLeft; origY = el.offsetTop;
                el.style.zIndex = 3000;

                if (e.type === 'touchstart') {
                    // Importante: No preventDefault aquí si queremos que el click funcione,
                    // pero para iPad es mejor usarlo y manejar el click nosotros si es necesario.
                    // e.preventDefault(); 
                }
                e.stopPropagation();
            };

            const onMove = (e) => {
                if (!isDraggingLocal || drawingEnabled) return;
                const t = e.touches ? e.touches[0] : e;
                const dx = t.clientX - startX;
                const dy = t.clientY - startY;

                if (Math.abs(dx) > 5 || Math.abs(dy) > 5) el.dataset.dragged = "true";

                el.style.left = (origX + dx) + 'px';
                el.style.top = (origY + dy) + 'px';

                if (e.cancelable) e.preventDefault();
                
                // MODO IMÁN: Si movemos el balón y algún modo está activo, actualizamos equipo
                if (id === 'ball' && tacticalMode !== 'OFF') {
                    triggerBallMagnetUpdate();
                }
            };

            const onEnd = () => {
                if (isDraggingLocal) {
                    isDraggingLocal = false;
                    el.style.zIndex = 1000;

                    if (id !== 'ball') {
                        // Guardar nueva posición base en % (centrado) pero aplicando
                        // matemáticas inversas si estamos en modo compresión/expansión.
                        // Esto permite al usuario modificar su formación táctica en CUALQUIER momento.
                        const rect = field.getBoundingClientRect();
                        const centerX = el.offsetLeft + el.offsetWidth / 2;
                        const centerY = el.offsetTop + el.offsetHeight / 2;
                        let rawX = (centerX / rect.width) * 100;
                        let rawY = (centerY / rect.height) * 100;
                        
                        savePlayerBasePosition(el, rawX, rawY);
                    }
                }
            };

            el.addEventListener('mousedown', onStart);
            window.addEventListener('mousemove', onMove);
            window.addEventListener('mouseup', onEnd);

            el.addEventListener('touchstart', onStart, { passive: false });
            window.addEventListener('touchmove', onMove, { passive: false });
            window.addEventListener('touchend', onEnd);

            el.onclick = () => {
                if (drawingEnabled) return;
                if (el.dataset.dragged === "true") {
                    delete el.dataset.dragged;
                    return;
                }
                if (confirm("¿Quitar?")) {
                    el.remove();
                    const orig = document.getElementById('player-' + id);
                    if (orig) orig.style.opacity = '1';
                }
            };
        }

        function setFormation(name, coords) {
            showToast(name);
            currentFormationCoords = coords;
            document.getElementById('flyout-tactics').classList.remove('open');
            
            const layer = document.getElementById('ghost-layer');
            layer.innerHTML = '';

            // Limpiar discos existentes
            document.querySelectorAll('.player-disk').forEach(el => el.remove());

            // Auto-Generar discos en las posiciones
            coords.forEach((c, index) => {
                const disk = document.createElement('div');
                disk.id = 'onfield-tactical-' + index;
                disk.className = 'player-disk' + (index === 0 ? ' gk-disk' : '');
                disk.dataset.baseX = c[0];
                disk.dataset.baseY = c[1];
                disk.dataset.line = categorizeLine(c[1]);
                
                field.appendChild(disk);
                applyTacticalPosition(disk, c[0], c[1]);
            });

            // Asegurar Balon y Tooltip
            let ball = document.querySelector('.ball-disk');
            if (!ball) {
                addBall();
                ball = document.querySelector('.ball-disk');
            }
            
            // Tooltip informativo
            const oldTooltip = document.querySelector('.ball-tooltip');
            if (oldTooltip) oldTooltip.remove();
            
            const tooltip = document.createElement('div');
            tooltip.className = 'ball-tooltip';
            tooltip.innerText = '¡MUEVE EL BALÓN!';
            ball.appendChild(tooltip);

            const removeTooltip = () => {
                tooltip.remove();
                window.removeEventListener('click', removeTooltip);
                window.removeEventListener('touchstart', removeTooltip);
            };
            setTimeout(() => {
                window.addEventListener('click', removeTooltip);
                window.addEventListener('touchstart', removeTooltip);
            }, 10);

            updateTeamIntensitySlider(document.getElementById('intensity-slider').value);

            // Forzar modo Defensivo al seleccionar formación
            if (tacticalMode !== 'DEF') {
                toggleMagnet('DEF');
            } else {
                triggerBallMagnetUpdate();
            }
        }

        // Manejador del Input Manual del Slider
        function updateTeamIntensitySlider(value) {
            if (tacticalMode !== 'OFF') {
                toggleMagnet(tacticalMode);
            }
            currentIntensityValue = value;
            renderTeamPositions();
        }

        function savePlayerBasePosition(el, rawX, rawY) {
            let widthMultiplier = 1.0;
            let shiftMultiplier = 0.30;
            let effectiveWidthMult = 1.0;

            if (tacticalMode === 'OFE') {
                widthMultiplier = 1.15;
                shiftMultiplier = 0.15;
                effectiveWidthMult = widthMultiplier;
            } else if (tacticalMode === 'DEF') {
                widthMultiplier = 0.85;
                shiftMultiplier = 0.45;
                effectiveWidthMult = widthMultiplier;
                
                // Revertir Cierre de Espalda Proporcional
                const pinchFactor = Math.abs(currentBallShiftX) / 50;
                let tempX = rawX - (currentBallShiftX * shiftMultiplier);
                
                if (currentBallShiftX < -15 && tempX > 50) {
                    effectiveWidthMult -= 0.15 * pinchFactor;
                } else if (currentBallShiftX > 15 && tempX < 50) {
                    effectiveWidthMult -= 0.15 * pinchFactor;
                }
            }

            // Deshacer la ecuación matemática exacta
            let baseApproxX = ((rawX - (currentBallShiftX * shiftMultiplier) - 50) / effectiveWidthMult) + 50;

            el.dataset.baseX = Math.max(2, Math.min(98, baseApproxX)).toFixed(1);

            // Revertir Y (Ingeniería Inversa del LERP Táctico)
            const intensity = (currentIntensityValue - 50) / 50; 
            
            if (intensity === 0) {
                el.dataset.baseY = Math.max(2, Math.min(98, rawY)).toFixed(1);
                el.dataset.line = categorizeLine(parseFloat(el.dataset.baseY));
                return;
            }

            const basePoints = [0, 25, 50, 75, 100];
            const offPoints = [5, 15, 35, 55, 85]; 
            const defPoints = [45, 70, 88, 96, 99]; 
            const currentTargets = basePoints.map((p, i) => {
                if (intensity > 0) return p + (offPoints[i] - p) * intensity;
                else return p + (defPoints[i] - p) * Math.abs(intensity);
            });

            let recoveredY = rawY;
            for (let i = 0; i < currentTargets.length - 1; i++) {
                if ((rawY >= currentTargets[i] && rawY <= currentTargets[i + 1]) ||
                    (rawY <= currentTargets[i] && rawY >= currentTargets[i + 1])) {
                    
                    const t = (rawY - currentTargets[i]) / (currentTargets[i + 1] - currentTargets[i]);
                    recoveredY = basePoints[i] + t * (basePoints[i + 1] - basePoints[i]);
                    break;
                }
            }

            el.dataset.baseY = Math.max(2, Math.min(98, recoveredY)).toFixed(1);
            el.dataset.line = categorizeLine(parseFloat(el.dataset.baseY));
        }

        function renderTeamPositions() {
            const intensity = (currentIntensityValue - 50) / 50; 
            const players = document.querySelectorAll('.player-disk');

            const basePoints = [0, 25, 50, 75, 100];
            const offPoints = [5, 15, 35, 55, 85]; 
            const defPoints = [45, 70, 88, 96, 99]; 

            const currentTargets = basePoints.map((p, i) => {
                if (intensity >= 0) {
                    return p + (offPoints[i] - p) * intensity;
                } else {
                    return p + (defPoints[i] - p) * Math.abs(intensity);
                }
            });
            
            let widthMultiplier = 1.0; 
            let shiftMultiplier = 0.30;
            
            if (tacticalMode === 'OFE') {
                widthMultiplier = 1.15; 
                shiftMultiplier = 0.15; 
            } else if (tacticalMode === 'DEF') {
                widthMultiplier = 0.85; 
                shiftMultiplier = 0.45; 
            }

            players.forEach(p => {
                const baseY = parseFloat(p.dataset.baseY);
                const baseX = parseFloat(p.dataset.baseX);
                
                // --- Eje Y ---
                let targetY = baseY;
                for (let i = 0; i < basePoints.length - 1; i++) {
                    if (baseY >= basePoints[i] && baseY <= basePoints[i + 1]) {
                        const t = (baseY - basePoints[i]) / (basePoints[i + 1] - basePoints[i]);
                        targetY = currentTargets[i] + t * (currentTargets[i + 1] - currentTargets[i]);
                        break;
                    }
                }

                // --- Eje X ---
                let expandedX = 50 + (baseX - 50) * widthMultiplier;
                let targetX = expandedX + (currentBallShiftX * shiftMultiplier);

                // Cierre de Espalda Proporcional (Inteligencia Defensiva)
                if (tacticalMode === 'DEF') {
                    const pinchFactor = Math.abs(currentBallShiftX) / 50; // 0 to 1
                    
                    // Si el balón está a la izquierda (shift < -15), el lado derecho cierra en base a su distancia
                    if (currentBallShiftX < -15 && baseX > 50) {
                        targetX -= (baseX - 50) * 0.15 * pinchFactor;
                    }
                    // Si el balón está a la derecha, el lado izquierdo cierra
                    else if (currentBallShiftX > 15 && baseX < 50) {
                        targetX += (50 - baseX) * 0.15 * pinchFactor;
                    }
                }
                
                targetY = Math.max(2, Math.min(97, targetY));
                targetX = Math.max(2, Math.min(98, targetX));
                
                p.style.top = targetY + '%';
                p.style.left = targetX + '%';
                p.style.transition = 'top 0.4s cubic-bezier(0.4, 0, 0.2, 1), left 0.4s';
            });
        }

        // Inicialización diferida para evitar ReferenceErrors
        lucide.createIcons();
        toggleFlyout('tactics');
    </script>
</body>

</html>