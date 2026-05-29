<?php
// config.php
date_default_timezone_set('America/Caracas');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Credenciales (Idealmente en .env)
define('DB_HOST', 'localhost');
define('DB_NAME', 'amweb_legacy');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHAR', 'utf8mb4');

// Configuración de Seguridad
$ADMIN_PIN = "2026"; 

/**
 * Obtiene la conexión PDO
 */
function getPDO() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHAR;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (\PDOException $e) {
        // En producción deberías ocultar esto, pero para debuguear lo mostramos
        die("Error DB: " . $e->getMessage());
    }
}

// Inicializar PDO para uso global (legacy support)
$pdo = getPDO();

/**
 * Función para inicializar tablas si no existen (solo se llama manual o en setup)
 */
function initDatabaseStructure($pdo) {
    // Tabla SOCIOS
    $pdo->exec("CREATE TABLE IF NOT EXISTS socios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) UNIQUE NOT NULL,
        telefono VARCHAR(20),
        cuota DECIMAL(10,2) DEFAULT 0
    ) ENGINE=InnoDB");

    // Verificar columna telefono en socios
    $colsTel = $pdo->query("SHOW COLUMNS FROM socios LIKE 'telefono'")->fetchAll();
    if (empty($colsTel)) {
        $pdo->exec("ALTER TABLE socios ADD COLUMN telefono VARCHAR(20) AFTER nombre");
    }

    // Verificar columna cuota en socios
    $cols = $pdo->query("SHOW COLUMNS FROM socios LIKE 'cuota'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE socios ADD COLUMN cuota DECIMAL(10,2) DEFAULT 0");
    }

    // Tabla PAGOS (Ingresos)
    $pdo->exec("CREATE TABLE IF NOT EXISTS pagos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        socio_id INT,
        monto DECIMAL(10,2) NOT NULL,
        categoria VARCHAR(50) DEFAULT 'Aporte',
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        nota VARCHAR(255),
        FOREIGN KEY (socio_id) REFERENCES socios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    // Tabla GASTOS (Erogaciones)
    $pdo->exec("CREATE TABLE IF NOT EXISTS gastos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        descripcion VARCHAR(255) NOT NULL,
        monto DECIMAL(10,2) NOT NULL,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Tabla AJUSTES (Configuración Global)
    $pdo->exec("CREATE TABLE IF NOT EXISTS ajustes (
        clave VARCHAR(50) PRIMARY KEY,
        valor VARCHAR(255)
    ) ENGINE=InnoDB");

    // Insertar cuota inicial si no existe
    $pdo->exec("INSERT IGNORE INTO ajustes (clave, valor) VALUES ('cuota_minima', '0')");
}

// Opcional: Ejecutar init si se pasa un parámetro o si faltan tablas
// if (isset($_GET['setup_db'])) initDatabaseStructure($pdo);
?>