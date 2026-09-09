<?php

require_once 'conexion.php';

header('Content-Type: text/plain; charset=utf-8');

$telefono = trim($_POST['telefono'] ?? '');

if (empty($telefono)) {
    echo 'ERROR|Teléfono vacío';
    exit();
}

$stmt = $conexion->prepare("
    SELECT u.id
    FROM usuarios u
    INNER JOIN usuarios_credenciales c
        ON c.usuario_id = u.id
    WHERE c.telefono = ?
    LIMIT 1
");

if (!$stmt) {
    echo 'ERROR|Prepare login: ' . $conexion->error;
    exit();
}

$stmt->bind_param("s", $telefono);
$stmt->execute();
$stmt->bind_result($usuario_id);

if (!$stmt->fetch()) {
    $stmt->close();
    echo 'ERROR|Usuario no encontrado';
    exit();
}

$stmt->close();

echo 'OK|' . $usuario_id . '|0';

?>