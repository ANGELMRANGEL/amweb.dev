<?php
require_once __DIR__ . '/db.php';

$message = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $id = $_POST['id'] ?? null;
        $parent_id = $_POST['parent_id'] ?? 'root';
        $name = $_POST['name'] ?? '';
        $type = $_POST['type'] ?? '';
        $tech = $_POST['tech'] ?? '';
        $link = $_POST['link'] ?? '';
        $is_folder = isset($_POST['is_folder']) ? 1 : 0;
        $folder_id = $_POST['folder_id'] ?? '';
        $order_index = (int)($_POST['order_index'] ?? 0);
        
        if (empty($name) || empty($type)) {
            $error = 'Nombre y Tipo son requeridos.';
        } else {
            try {
                if ($action === 'add') {
                    $stmt = $db->prepare("INSERT INTO finder_items (parent_id, name, type, tech, link, is_folder, folder_id, order_index) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$parent_id, $name, $type, $tech, $link, $is_folder, $folder_id, $order_index]);
                    $message = 'Elemento agregado con éxito.';
                } else {
                    $stmt = $db->prepare("UPDATE finder_items SET parent_id = ?, name = ?, type = ?, tech = ?, link = ?, is_folder = ?, folder_id = ?, order_index = ? WHERE id = ?");
                    $stmt->execute([$parent_id, $name, $type, $tech, $link, $is_folder, $folder_id, $order_index, $id]);
                    $message = 'Elemento actualizado con éxito.';
                }
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            try {
                $stmt = $db->prepare("DELETE FROM finder_items WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'Elemento eliminado con éxito.';
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }
}

// Fetch all items
try {
    $stmt = $db->query("SELECT * FROM finder_items ORDER BY parent_id, order_index ASC, name ASC");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch folders for parent select
    $folderStmt = $db->query("SELECT DISTINCT folder_id, name FROM finder_items WHERE is_folder = 1 AND folder_id IS NOT NULL AND folder_id != '' ORDER BY name ASC");
    $availableFolders = $folderStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $items = [];
    $availableFolders = [];
    $error = 'Error al cargar datos: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador del Finder — AMWeb</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b0f19;
            --bg2: #111827;
            --bg3: #1f2937;
            --accent: #3b82f6;
            --accent-dim: rgba(59, 130, 246, 0.15);
            --text: #f3f4f6;
            --text-sec: #9ca3af;
            --border: rgba(255, 255, 255, 0.08);
            --radius: 12px;
            --radius-sm: 6px;
            --success: #10b981;
            --danger: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            padding: 40px 20px;
            line-height: 1.5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 20px;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        h1 span {
            color: var(--accent);
            font-family: 'JetBrains Mono', monospace;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 500;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            border: none;
        }

        .btn-primary {
            background-color: var(--accent);
            color: #fff;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        .btn-danger {
            background-color: var(--danger);
            color: #fff;
        }

        .btn-danger:hover {
            opacity: 0.9;
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--text-sec);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--text);
        }

        .alert {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.15);
            border: 1px solid var(--success);
            color: #34d399;
        }

        .alert-danger {
            background-color: rgba(239, 68, 68, 0.15);
            border: 1px solid var(--danger);
            color: #f87171;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }

        @media (min-width: 900px) {
            .grid {
                grid-template-columns: 2fr 1fr;
            }
        }

        .card {
            background-color: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Table styles */
        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }

        th {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: var(--text-sec);
            padding: 12px;
            border-bottom: 1px solid var(--border);
            text-transform: uppercase;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            vertical-align: middle;
        }

        tr:hover {
            background-color: rgba(255, 255, 255, 0.01);
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            font-family: 'JetBrains Mono', monospace;
            background-color: var(--bg3);
            color: var(--text-sec);
        }

        .badge-folder {
            background-color: var(--accent-dim);
            color: var(--accent);
        }

        .actions-cell {
            display: flex;
            gap: 8px;
        }

        /* Form styles */
        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-sec);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px 12px;
            background-color: var(--bg3);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text);
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus {
            outline: none;
            border-color: var(--accent);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }

        input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
        }

        .tech-pill {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            background-color: rgba(255, 255, 255, 0.05);
            padding: 2px 6px;
            border-radius: 4px;
            color: var(--text-sec);
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><span>//</span> Administrador Finder</h1>
            <a href="/" class="btn btn-secondary">Ver Sitio ↗</a>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="grid">
            <!-- Items List -->
            <div class="card">
                <div class="card-title">Elementos Actuales</div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Padre (Ubicación)</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Tecnología</th>
                                <th>Destino / Click</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-sec);">No hay elementos registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td style="font-family: 'JetBrains Mono', monospace;"><?= $item['order_index'] ?></td>
                                        <td><span class="badge"><?= htmlspecialchars($item['parent_id']) ?></span></td>
                                        <td>
                                            <strong><?= htmlspecialchars($item['name']) ?></strong>
                                            <?php if ($item['is_folder']): ?>
                                                <span class="badge badge-folder">Carpeta</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['type']) ?></td>
                                        <td><span class="tech-pill"><?= htmlspecialchars($item['tech']) ?></span></td>
                                        <td style="color: var(--accent); max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?= $item['is_folder'] ? 'Abre carpeta: ' . htmlspecialchars($item['folder_id']) : htmlspecialchars($item['link'] ?? '') ?>
                                        </td>
                                        <td>
                                            <div class="actions-cell">
                                                <button class="btn btn-secondary" onclick="editItem(<?= htmlspecialchars(json_encode($item)) ?>)">Editar</button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro de eliminar este elemento?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                    <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;">Eliminar</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Form -->
            <div class="card">
                <div class="card-title" id="form-title">Agregar Elemento</div>
                <form id="item-form" method="POST">
                    <input type="hidden" name="action" id="form-action" value="add">
                    <input type="hidden" name="id" id="item-id" value="">

                    <div class="form-group">
                        <label for="parent_id">Directorio Padre / Ubicación (ej: root, lafelicitta, mojon-gemini)</label>
                        <input type="text" name="parent_id" id="item-parent_id" placeholder="ej. root o cualquier ID de carpeta" required>
                    </div>

                    <div class="form-group">
                        <label for="name">Nombre</label>
                        <input type="text" name="name" id="item-name" placeholder="ej. mi-proyecto" required>
                    </div>

                    <div class="form-group">
                        <label for="type">Tipo</label>
                        <input type="text" name="type" id="item-type" placeholder="ej. Web App, Módulo" required>
                    </div>

                    <div class="form-group">
                        <label for="tech">Tecnología / Info</label>
                        <input type="text" name="tech" id="item-tech" placeholder="ej. PHP / JS / MySQL" required>
                    </div>

                    <div class="form-group">
                        <label for="order_index">Orden (Menor número = primero)</label>
                        <input type="number" name="order_index" id="item-order_index" value="0">
                    </div>

                    <div class="form-group checkbox-group">
                        <input type="checkbox" name="is_folder" id="item-is_folder" onchange="toggleFolderFields()">
                        <label for="item-is_folder" style="margin-bottom: 0; cursor: pointer;">Es una Carpeta</label>
                    </div>

                    <div class="form-group" id="link-group">
                        <label for="link">Destino del Click (URL/Enlace)</label>
                        <input type="text" name="link" id="item-link" placeholder="ej. /proyecto/ o https://...">
                    </div>

                    <div class="form-group" id="folder-group" style="display: none;">
                        <label for="folder_id">ID de la carpeta destino</label>
                        <input type="text" name="folder_id" id="item-folder_id" placeholder="ej. lafelicitta">
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">Guardar</button>
                        <button type="button" id="cancel-btn" class="btn btn-secondary" style="display: none;" onclick="resetForm()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleFolderFields() {
            const isFolder = document.getElementById('item-is_folder').checked;
            document.getElementById('link-group').style.display = isFolder ? 'none' : 'block';
            document.getElementById('folder-group').style.display = isFolder ? 'block' : 'none';
        }

        function editItem(item) {
            document.getElementById('form-title').innerText = 'Editar Elemento';
            document.getElementById('form-action').value = 'edit';
            document.getElementById('item-id').value = item.id;
            document.getElementById('item-parent_id').value = item.parent_id;
            document.getElementById('item-name').value = item.name;
            document.getElementById('item-type').value = item.type;
            document.getElementById('item-tech').value = item.tech;
            document.getElementById('item-order_index').value = item.order_index;
            document.getElementById('item-is_folder').checked = parseInt(item.is_folder) === 1;
            document.getElementById('item-link').value = item.link || '';
            document.getElementById('item-folder_id').value = item.folder_id || '';
            
            toggleFolderFields();
            document.getElementById('cancel-btn').style.display = 'inline-block';
            
            // Scroll to form
            document.getElementById('item-form').scrollIntoView({ behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('form-title').innerText = 'Agregar Elemento';
            document.getElementById('form-action').value = 'add';
            document.getElementById('item-id').value = '';
            document.getElementById('item-form').reset();
            toggleFolderFields();
            document.getElementById('cancel-btn').style.display = 'none';
        }
    </script>
</body>
</html>
