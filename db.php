<?php
try {
    $db = new PDO('sqlite:' . __DIR__ . '/finder.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Recrear tabla para sincronizar los nuevos datos
    $db->exec("DROP TABLE IF EXISTS finder_items");
    $db->exec("CREATE TABLE finder_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        parent_id TEXT NOT NULL,
        name TEXT NOT NULL,
        type TEXT NOT NULL,
        tech TEXT NOT NULL,
        link TEXT,
        is_folder INTEGER DEFAULT 0,
        folder_id TEXT,
        order_index INTEGER DEFAULT 0
    )");

    $initialData = [
        // Categorías raíz
        ['root', 'CRM', 'Folder', 'Directorio', null, 1, 'crm', 1],
        ['root', 'MENUS DIGITALES', 'Folder', 'Directorio', null, 1, 'menus_digitales', 2],
        ['root', 'WEBAPP ADMIN', 'Folder', 'Directorio', null, 1, 'webapp_admin', 3],
        ['root', 'RESERVACIONES ONLINE', 'Folder', 'Directorio', null, 1, 'reservaciones_online', 4],
        ['root', 'CREADOR DE MENUS PARA RESTAURANTS', 'Folder', 'Directorio', null, 1, 'creador_menus', 5],
        ['root', 'TIENDA ONLINE', 'Folder', 'Directorio', null, 1, 'tienda_online', 6],
        ['root', 'INTEGRACION CON AI', 'Folder', 'Directorio', null, 1, 'integracion_ai', 7],
        ['root', 'WEB DE CURSOS', 'Folder', 'Directorio', null, 1, 'cursos', 8],
        
        // CRM
        ['crm', 'Pastores Alvarez', 'Web App', 'PHP / MySQL / PWA / App', 'https://pastoresalvarez.com', 0, null, 1],
        ['crm', 'Plataforma Sion', 'Web App', 'PHP / MySQL', 'https://plataformasion.org', 0, null, 2],
        ['crm', 'La Sala Boxing', 'PWA (Deporte)', 'PHP / MySQL / PWA', 'https://lasalaboxing.app', 0, null, 3],
        ['crm', 'Escuela Don Divino', 'Web App', 'PHP / MySQL', 'https://escueladondivino.com', 0, null, 4],
        ['crm', 'Realtor Dream Home', 'Web App', 'PHP / MySQL', 'https://amweb.dev/dh/', 0, null, 5],
        ['crm', 'Arias Blanco FS', 'Web App', 'PHP / SQLite', 'https://amweb.dev/abfs/', 0, null, 6],
        
        // MENUS DIGITALES
        ['menus_digitales', 'Nuestra Bodega', 'Módulo', 'PHP / MySQL / PWA', 'https://lafelicitta.com/vinos/', 0, null, 1],
        ['menus_digitales', 'LunchClub', 'Módulo', 'PHP / MySQL', 'https://lafelicitta.com/lunchclub/', 0, null, 2],
        ['menus_digitales', 'Piacere', 'Módulo', 'PHP / Python / Matplotlib / JS', 'https://lafelicitta.com/piacere/', 0, null, 3],
        ['menus_digitales', 'PizzaON', 'Módulo', 'PHP / MySQL / PWA', 'https://lafelicitta.com/pizzaon/', 0, null, 4],
        ['menus_digitales', 'Menu Kids', 'Módulo', 'HTML5 Canvas / Vanilla JS / PHP / PWA', 'https://lafelicitta.com/kids/', 0, null, 5],
        ['menus_digitales', 'La Felicitta Club', 'Módulo', 'PHP / MySQL / PWA', 'https://lafelicitta.com/lfclub/', 0, null, 6],
        ['menus_digitales', 'Administrativo LF', 'Módulo', 'PHP / MySQL', 'https://lafelicitta.com/admin/', 0, null, 7],
        
        // WEBAPP ADMIN
        ['webapp_admin', 'Masterbook', 'Módulo', 'PHP / MySQL', 'https://lafelicitta.com/masterbook/', 0, null, 1],
        
        // RESERVACIONES ONLINE
        ['reservaciones_online', 'Reservas', 'Módulo', 'HTML / CSS / JS / PHP', 'https://lafelicitta.com/reservas/', 0, null, 1],
        ['reservaciones_online', 'Mundial LF WC 2026', 'Módulo', 'PHP / MySQL / PWA', 'https://mundial.lafelicitta.com', 0, null, 2],
        
        // CREADOR DE MENUS PARA RESTAURANTS
        ['creador_menus', 'LF Creator', 'Módulo', 'PHP / SQLite / JS', 'https://lafelicitta.com/creator/', 0, null, 1],
        
        // TIENDA ONLINE
        ['tienda_online', 'ZaidaCreaciones', 'Web App', 'HTML5 / CSS3 / JS / PHP / MySQL', 'https://amweb.dev/zc/', 0, null, 1],
        
        // INTEGRACION CON AI
        ['integracion_ai', 'SolwaterPRO', 'Web App', 'PHP / MySQL / Gemini API / PWA', 'https://solwaterpro.com', 0, null, 1],
        
        // WEB DE CURSOS
        ['cursos', 'JuventudReforma', 'Web App', 'PHP / MySQL / PWA', 'https://juventudreforma.com', 0, null, 1]
    ];

    $insert = $db->prepare("INSERT INTO finder_items (parent_id, name, type, tech, link, is_folder, folder_id, order_index) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($initialData as $row) {
        $insert->execute($row);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
