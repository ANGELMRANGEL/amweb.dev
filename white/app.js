/**
 * Sketchboard Pro - ES5 Optimized Engine
 * Designed for Safari 9 (iPad Mini 1)
 */

(function() {
    'use strict';

    // --- Core State ---
    var canvas = document.getElementById('main-canvas');
    var ctx = canvas.getContext('2d');
    var container = document.getElementById('app-container');
    
    var currentTool = 'pencil';
    var currentColor = '#FFFFFF';
    var shapes = [];
    var redoStack = [];
    var isDrawing = false;
    var currentShape = null;
    
    var savedDesigns = []; // Arreglo de diseños guardados
    
    // --- Initialization ---
    function init() {
        resizeCanvas();
        setupEventListeners();
        loadLastSession();
        loadDesignsFromStorage();
        render();
    }

    function resizeCanvas() {
        canvas.width = container.offsetWidth;
        canvas.height = container.offsetHeight;
        render();
    }

    function setupEventListeners() {
        // Touch & Mouse
        canvas.addEventListener('touchstart', handleStart);
        canvas.addEventListener('touchmove', handleMove);
        canvas.addEventListener('touchend', handleEnd);
        canvas.addEventListener('mousedown', handleStart);
        window.addEventListener('mousemove', handleMove);
        window.addEventListener('mouseup', handleEnd);
        
        // Tools
        var toolBtns = document.querySelectorAll('.tool-btn');
        for (var i = 0; i < toolBtns.length; i++) {
            toolBtns[i].addEventListener('click', function(e) {
                setTool(e.currentTarget.id.replace('tool-', ''));
            });
        }
        
        // Colors
        var swatches = document.querySelectorAll('.color-swatch');
        for (var j = 0; j < swatches.length; j++) {
            swatches[j].addEventListener('click', function(e) {
                setColor(e.target.getAttribute('data-color'));
            });
        }
        
        // Action Buttons
        document.getElementById('btn-undo').addEventListener('click', undo);
        document.getElementById('btn-redo').addEventListener('click', redo);
        document.getElementById('btn-clear').addEventListener('click', clearAll);
        document.getElementById('btn-save').addEventListener('click', saveCurrentDesign);
        document.getElementById('btn-gallery').addEventListener('click', openGallery);
        document.getElementById('btn-close-gallery').addEventListener('click', closeGallery);
        
        // Global touch prevent
        document.body.addEventListener('touchmove', function(e) {
            if (e.target === canvas) e.preventDefault();
        }, false);
        
        window.addEventListener('resize', resizeCanvas);
    }

    // --- State Handlers ---
    function setTool(tool) {
        currentTool = tool;
        var btns = document.querySelectorAll('.tool-btn');
        for (var i = 0; i < btns.length; i++) {
            btns[i].classList.remove('active');
            if (btns[i].id === 'tool-' + tool) btns[i].classList.add('active');
        }
    }

    function setColor(color) {
        currentColor = color;
        var swatches = document.querySelectorAll('.color-swatch');
        for (var i = 0; i < swatches.length; i++) {
            swatches[i].classList.remove('active');
            if (swatches[i].getAttribute('data-color') === color) swatches[i].classList.add('active');
        }
    }

    // --- Drawing Engine ---
    function getCoords(e) {
        var rect = canvas.getBoundingClientRect();
        var x, y;
        if (e.touches && e.touches.length > 0) {
            x = e.touches[0].clientX - rect.left;
            y = e.touches[0].clientY - rect.top;
        } else {
            x = e.clientX - rect.left;
            y = e.clientY - rect.top;
        }
        return { x: x, y: y };
    }

    function handleStart(e) {
        if (!isGalleryClosed()) return;
        if (e.type === 'touchstart') e.preventDefault();
        var coords = getCoords(e);
        isDrawing = true;
        
        if (currentTool === 'eraser') {
            eraseAt(coords.x, coords.y);
            return;
        }
        
        currentShape = {
            type: currentTool,
            color: currentColor,
            points: [coords],
            startX: coords.x,
            startY: coords.y,
            endX: coords.x,
            endY: coords.y
        };
        redoStack = [];
    }

    function handleMove(e) {
        if (!isDrawing) return;
        var coords = getCoords(e);
        
        if (currentTool === 'eraser') {
            eraseAt(coords.x, coords.y);
            return;
        }
        
        if (currentTool === 'pencil') {
            currentShape.points.push(coords);
        } else {
            currentShape.endX = coords.x;
            currentShape.endY = coords.y;
        }
        render();
        drawShape(ctx, currentShape);
    }

    function handleEnd(e) {
        if (!isDrawing) return;
        isDrawing = false;
        if (currentTool !== 'eraser' && currentShape) {
            shapes.push(currentShape);
            saveLastSession();
        }
        currentShape = null;
        render();
    }

    function drawShape(context, shape) {
        context.beginPath();
        context.strokeStyle = shape.color;
        context.lineWidth = (shape.type === 'pencil' ? 3 : 4);
        context.lineJoin = 'round';
        context.lineCap = 'round';
        
        if (shape.type === 'pencil') {
            if (shape.points.length < 2) return;
            context.moveTo(shape.points[0].x, shape.points[0].y);
            for (var i = 1; i < shape.points.length; i++) {
                context.lineTo(shape.points[i].x, shape.points[i].y);
            }
        } else if (shape.type === 'line') {
            context.moveTo(shape.startX, shape.startY);
            context.lineTo(shape.endX, shape.endY);
        } else if (shape.type === 'arrow') {
            drawArrow(context, shape.startX, shape.startY, shape.endX, shape.endY);
        } else if (shape.type === 'rect') {
            context.strokeRect(shape.startX, shape.startY, shape.endX - shape.startX, shape.endY - shape.startY);
        } else if (shape.type === 'circle') {
            var radius = Math.sqrt(Math.pow(shape.endX - shape.startX, 2) + Math.pow(shape.endY - shape.startY, 2));
            context.arc(shape.startX, shape.startY, radius, 0, 2 * Math.PI);
        }
        context.stroke();
    }

    function drawArrow(context, fromx, fromy, tox, toy) {
        var headlen = 15;
        var dx = tox - fromx;
        var dy = toy - fromy;
        var angle = Math.atan2(dy, dx);
        context.moveTo(fromx, fromy);
        context.lineTo(tox, toy);
        context.lineTo(tox - headlen * Math.cos(angle - Math.PI / 6), toy - headlen * Math.sin(angle - Math.PI / 6));
        context.moveTo(tox, toy);
        context.lineTo(tox - headlen * Math.cos(angle + Math.PI / 6), toy - headlen * Math.sin(angle + Math.PI / 6));
    }

    function render() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        for (var i = 0; i < shapes.length; i++) {
            drawShape(ctx, shapes[i]);
        }
    }

    function eraseAt(x, y) {
        var threshold = 20;
        var found = -1;
        for (var i = shapes.length - 1; i >= 0; i--) {
            if (isPointNearShape(x, y, shapes[i], threshold)) {
                found = i;
                break;
            }
        }
        if (found !== -1) {
            shapes.splice(found, 1);
            saveLastSession();
            render();
        }
    }

    function isPointNearShape(x, y, shape, threshold) {
        if (shape.type === 'pencil') {
            for (var i = 0; i < shape.points.length; i++) {
                var p = shape.points[i];
                if (Math.sqrt(Math.pow(x - p.x, 2) + Math.pow(y - p.y, 2)) < threshold) return true;
            }
        } else if (shape.type === 'line' || shape.type === 'arrow') {
            return distToSegment({x: x, y: y}, {x: shape.startX, y: shape.startY}, {x: shape.endX, y: shape.endY}) < threshold;
        } else if (shape.type === 'rect') {
            var x1 = Math.min(shape.startX, shape.endX), x2 = Math.max(shape.startX, shape.endX);
            var y1 = Math.min(shape.startY, shape.endY), y2 = Math.max(shape.startY, shape.endY);
            return (Math.abs(x - x1) < threshold || Math.abs(x - x2) < threshold) && y >= y1 && y <= y2 ||
                   (Math.abs(y - y1) < threshold || Math.abs(y - y2) < threshold) && x >= x1 && x <= x2;
        } else if (shape.type === 'circle') {
            var radius = Math.sqrt(Math.pow(shape.endX - shape.startX, 2) + Math.pow(shape.endY - shape.startY, 2));
            var dist = Math.sqrt(Math.pow(x - shape.startX, 2) + Math.pow(y - shape.startY, 2));
            return Math.abs(dist - radius) < threshold;
        }
        return false;
    }

    function distToSegment(p, v, w) {
        var l2 = Math.pow(v.x - w.x, 2) + Math.pow(v.y - w.y, 2);
        if (l2 === 0) return Math.sqrt(Math.pow(p.x - v.x, 2) + Math.pow(p.y - v.y, 2));
        var t = ((p.x - v.x) * (w.x - v.x) + (p.y - v.y) * (w.y - v.y)) / l2;
        t = Math.max(0, Math.min(1, t));
        return Math.sqrt(Math.pow(p.x - (v.x + t * (w.x - v.x)), 2) + Math.pow(p.y - (v.y + t * (w.y - v.y)), 2));
    }

    // --- History & Persistence ---
    function undo() {
        if (shapes.length > 0) {
            redoStack.push(shapes.pop());
            saveLastSession();
            render();
        }
    }

    function redo() {
        if (redoStack.length > 0) {
            shapes.push(redoStack.pop());
            saveLastSession();
            render();
        }
    }

    function clearAll() {
        setTimeout(function() {
            if (window.confirm('¿Borrar todo el pizarrón?')) {
                shapes.length = 0;
                redoStack.length = 0;
                saveLastSession();
                render();
                // Explicit deep clear
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        }, 50);
    }

    // --- Multi-Design Gallery Logic ---

    function saveCurrentDesign() {
        if (shapes.length === 0) {
            setTimeout(function() { alert('El pizarrón está vacío.'); }, 50);
            return;
        }
        
        var design = {
            id: new Date().getTime(),
            date: new Date().toLocaleString(),
            shapes: JSON.parse(JSON.stringify(shapes)),
            thumb: canvas.toDataURL('image/png', 0.3)
        };
        
        savedDesigns.unshift(design);
        localStorage.setItem('sketchboard_gallery', JSON.stringify(savedDesigns));
        setTimeout(function() { alert('¡Diseño guardado en la galería!'); }, 50);
    }

    function openGallery() {
        renderGallery();
        document.getElementById('gallery-overlay').classList.remove('hidden');
    }

    function closeGallery() {
        document.getElementById('gallery-overlay').classList.add('hidden');
    }

    function isGalleryClosed() {
        return document.getElementById('gallery-overlay').classList.contains('hidden');
    }

    function renderGallery() {
        var list = document.getElementById('gallery-list');
        list.innerHTML = '';
        
        if (savedDesigns.length === 0) {
            list.innerHTML = '<p style="width:100%; text-align:center; color:#666; margin-top:50px;">No hay diseños guardados.</p>';
            return;
        }
        
        for (var i = 0; i < savedDesigns.length; i++) {
            var item = savedDesigns[i];
            var div = document.createElement('div');
            div.className = 'gallery-item';
            div.innerHTML = [
                '<div class="gallery-thumb" style="background-image:url(', item.thumb, ')"></div>',
                '<div class="gallery-info">',
                    '<span class="gallery-date">', item.date, '</span>',
                    '<button class="gallery-delete" data-id="', item.id, '">',
                        '<svg class="icon-svg" viewBox="0 0 24 24" style="width:16px; height:16px;"><path d="M10 5h4a2 2 0 1 0-4 0M8.5 5a3.5 3.5 0 1 1 7 0h5.75a.75.75 0 0 1 0 1.5h-1.32l-1.17 12.111A3.75 3.75 0 0 1 15.026 22H8.974a3.75 3.75 0 0 1-3.733-3.389L4.07 6.5H2.75a.75.75 0 0 1 0-1.5zM10 9.75a.75.75 0 0 0-1.5 0v7.5a.75.75 0 0 0 1.5 0zm4.25 0a.75.75 0 0 1 .75.75v7.5a.75.75 0 0 1-1.5 0v-7.5a.75.75 0 0 1 .75-.75m-7.516 9.467a2.25 2.25 0 0 0 2.24 2.033h6.052a2.25 2.25 0 0 0 2.24-2.033L18.424 6.5H5.576z"/></svg>',
                    '</button>',
                '</div>'
            ].join('');
            
            // Thumbnail click to load
            div.querySelector('.gallery-thumb').addEventListener('click', (function(design) {
                return function() { loadDesign(design); };
            })(item));
            
            // Delete click
            div.querySelector('.gallery-delete').addEventListener('click', (function(id) {
                return function(e) { 
                    e.stopPropagation();
                    deleteDesign(id); 
                };
            })(item.id));
            
            list.appendChild(div);
        }
    }

    function loadDesign(design) {
        setTimeout(function() {
            if (confirm('¿Cargar este diseño? Se perderá el trabajo actual no guardado.')) {
                shapes.length = 0;
                var newShapes = design.shapes;
                for (var i = 0; i < newShapes.length; i++) {
                    shapes.push(newShapes[i]);
                }
                redoStack.length = 0;
                saveLastSession();
                render();
                closeGallery();
            }
        }, 50);
    }

    function deleteDesign(id) {
        setTimeout(function() {
            if (confirm('¿Eliminar este diseño de la galería?')) {
                for (var i = 0; i < savedDesigns.length; i++) {
                    if (savedDesigns[i].id === id) {
                        savedDesigns.splice(i, 1);
                        break;
                    }
                }
                localStorage.setItem('sketchboard_gallery', JSON.stringify(savedDesigns));
                renderGallery();
            }
        }, 50);
    }

    function saveLastSession() {
        localStorage.setItem('sketchboard_last_session', JSON.stringify(shapes));
    }

    function loadLastSession() {
        var last = localStorage.getItem('sketchboard_last_session');
        if (last) {
            var data = JSON.parse(last);
            shapes.length = 0;
            for (var i = 0; i < data.length; i++) {
                shapes.push(data[i]);
            }
        }
    }

    function loadDesignsFromStorage() {
        var gallery = localStorage.getItem('sketchboard_gallery');
        if (gallery) {
            var data = JSON.parse(gallery);
            savedDesigns.length = 0;
            for (var i = 0; i < data.length; i++) {
                savedDesigns.push(data[i]);
            }
        }
    }

    init();
})();
