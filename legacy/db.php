<?php
// db.php - Database Wrapper para simplificar CRUD

require_once 'config.php';

class DB {
    public $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->checkMigrations();
    }

    private function checkMigrations() {
        try {
            // Asegurar columnas críticas
            try { $this->pdo->exec("ALTER TABLE socios ADD COLUMN telefono VARCHAR(50) AFTER nombre"); } catch (Exception $e) {}
            try { $this->pdo->exec("ALTER TABLE socios ADD COLUMN cuota DECIMAL(10,2) DEFAULT 0 AFTER telefono"); } catch (Exception $e) {}
            
            // Verificar columna categoria en pagos
            $cols = $this->pdo->query("SHOW COLUMNS FROM pagos LIKE 'categoria'")->fetchAll();
            if (empty($cols)) {
                $this->pdo->exec("ALTER TABLE pagos ADD COLUMN categoria VARCHAR(50) DEFAULT 'Aporte' AFTER monto");
            }
            // Verificar tabla ajustes
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS ajustes (clave VARCHAR(50) PRIMARY KEY, valor VARCHAR(255))");
            $this->pdo->exec("INSERT IGNORE INTO ajustes (clave, valor) VALUES ('cuota_minima', '0')");
            
            // Forzar tabla de gastos
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS gastos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                descripcion VARCHAR(255) NOT NULL,
                monto DECIMAL(10,2) NOT NULL,
                fecha DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB");
        } catch (PDOException $e) {
            // Ya no es silencioso para poder ver el error
            if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                 die("Error al actualizar base de datos: " . $e->getMessage());
            }
        }
    }

    // --- AJUSTES ---
    public function getAjuste($clave) {
        $stmt = $this->pdo->prepare("SELECT valor FROM ajustes WHERE clave = ?");
        $stmt->execute([$clave]);
        return $stmt->fetchColumn();
    }

    public function setAjuste($clave, $valor) {
        $stmt = $this->pdo->prepare("REPLACE INTO ajustes (clave, valor) VALUES (?, ?)");
        return $stmt->execute([$clave, $valor]);
    }

    public function getResumenSocios() {
        $sql = "
            SELECT s.id, s.nombre, s.telefono, COALESCE(SUM(p.monto), 0) as total_pagado
            FROM socios s
            LEFT JOIN pagos p ON s.id = p.socio_id
            GROUP BY s.id, s.nombre, s.telefono
            ORDER BY s.nombre ASC
        ";
        return $this->pdo->query($sql)->fetchAll();
    }

    // --- SOCIOS ---
    public function updateSocio($id, $nombre, $telefono) {
        $stmt = $this->pdo->prepare("UPDATE socios SET nombre = :nombre, telefono = :telefono WHERE id = :id");
        $stmt->execute([
            ':nombre' => trim($nombre),
            ':telefono' => trim($telefono),
            ':id' => $id
        ]);
        
        $affected = $stmt->rowCount();
        if ($affected === 0) {
            // Verificar si es porque los datos son idénticos o porque no existe el ID
            $check = $this->pdo->prepare("SELECT id FROM socios WHERE id = ?");
            $check->execute([$id]);
            if (!$check->fetch()) {
                die("ERROR CRÍTICO: El socio con ID $id no existe en la base de datos.");
            }
        }
        return true;
    }

    public function getSocios() {
        return $this->pdo->query("SELECT * FROM socios ORDER BY nombre ASC")->fetchAll();
    }

    public function addSocio($nombre, $telefono = '') {
        $stmt = $this->pdo->prepare("INSERT INTO socios (nombre, telefono) VALUES (?, ?)");
        return $stmt->execute([$nombre, $telefono]);
    }

    public function updateCuota($id, $monto) {
        $stmt = $this->pdo->prepare("UPDATE socios SET cuota = ? WHERE id = ?");
        return $stmt->execute([$monto, $id]);
    }

    // --- PAGOS (Ingresos) ---
    public function addPago($socio_id, $monto, $nota, $categoria = 'Aporte') {
        $stmt = $this->pdo->prepare("INSERT INTO pagos (socio_id, monto, nota, categoria) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$socio_id, $monto, $nota, $categoria]);
    }

    public function updatePago($id, $socio_id, $monto, $nota, $categoria) {
        $stmt = $this->pdo->prepare("UPDATE pagos SET socio_id = ?, monto = ?, nota = ?, categoria = ? WHERE id = ?");
        return $stmt->execute([$socio_id, $monto, $nota, $categoria, $id]);
    }

    public function deletePago($id) {
        $stmt = $this->pdo->prepare("DELETE FROM pagos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- GASTOS (Egresos) ---
    public function addGasto($descripcion, $monto) {
        $stmt = $this->pdo->prepare("INSERT INTO gastos (descripcion, monto) VALUES (?, ?)");
        $stmt->execute([$descripcion, $monto]);
        if ($stmt->rowCount() === 0) {
            throw new Exception("Error: El gasto no se insertó en la base de datos.");
        }
        return true;
    }

    public function updateGasto($id, $descripcion, $monto) {
        $stmt = $this->pdo->prepare("UPDATE gastos SET descripcion = ?, monto = ? WHERE id = ?");
        return $stmt->execute([$descripcion, $monto, $id]);
    }

    public function deleteGasto($id) {
        $stmt = $this->pdo->prepare("DELETE FROM gastos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- TOTALES Y BALANCES ---
    public function getBalance() {
        $ingresos = $this->pdo->query("SELECT COALESCE(SUM(monto),0) FROM pagos")->fetchColumn();
        $egresos = $this->pdo->query("SELECT COALESCE(SUM(monto),0) FROM gastos")->fetchColumn();
        return [
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'saldo' => $ingresos - $egresos
        ];
    }

    public function getHistorial($limit = 50) {
        $sql = "
            (SELECT 'ingreso' as tipo, p.id, p.monto, p.fecha, COALESCE(p.nota, '') as descripcion, COALESCE(s.nombre, 'Socio') as autor, p.categoria, p.socio_id 
             FROM pagos p LEFT JOIN socios s ON p.socio_id = s.id)
            UNION ALL
            (SELECT 'egreso' as tipo, id, monto, fecha, descripcion, 'ADMIN' as autor, 'Gasto' as categoria, NULL as socio_id 
             FROM gastos)
            ORDER BY fecha DESC LIMIT $limit
        ";
        return $this->pdo->query($sql)->fetchAll();
    }
}

// Instancia global para facilitar la migración
$db = new DB();
?>
