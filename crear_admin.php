<?php
// abfs/public/crear_admin.php
require_once '../config/db.php';

$nombre = 'yoger';
$cedula = '12345678';
$password = '123456';
$rol = 'presidencia';

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE cedula = ?");
    $stmt->execute([$cedula]);

    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, password = ?, rol = ? WHERE cedula = ?");
        $stmt->execute([$nombre, $hash, $rol, $cedula]);
        echo "Administrador actualizado con éxito.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, cedula, password, rol) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $cedula, $hash, $rol]);
        echo "Administrador creado con éxito.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
