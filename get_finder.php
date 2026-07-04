<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/db.php';

try {
    $stmt = $db->query("SELECT * FROM finder_items ORDER BY parent_id, order_index ASC, name ASC");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $grouped = [];
    foreach ($items as $item) {
        $parentId = $item['parent_id'];
        if (!isset($grouped[$parentId])) {
            $grouped[$parentId] = [];
        }
        
        $grouped[$parentId][] = [
            'id' => (int)$item['id'],
            'name' => $item['name'],
            'type' => $item['type'],
            'tech' => $item['tech'],
            'link' => $item['link'],
            'isFolder' => (bool)$item['is_folder'],
            'folderId' => $item['folder_id']
        ];
    }
    
    echo json_encode($grouped);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
