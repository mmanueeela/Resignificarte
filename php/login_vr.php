<?php

require_once 'conexion.php';

header('Content-Type: text/plain; charset=utf-8');

$telefono = trim($_POST['telefono'] ?? '');

if (empty($telefono)) {
    echo 'ERROR|Teléfono vacío';
    exit();
}

// Buscar usuario por número de teléfono
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

// Contar obras normales de Antonio Nieto
// comentadas desde la experiencia VR
$artistaAntonio = 1;

$stmt = $conexion->prepare("
    SELECT COUNT(DISTINCT c.obra_id)
    FROM comentarios c
    INNER JOIN obras o
        ON c.obra_id = o.id
    WHERE c.usuario_id = ?
      AND c.origen = 'vr'
      AND o.artista_id = ?
      AND o.es_recompensa = 0
");

if (!$stmt) {
    echo 'ERROR|Prepare progreso: ' . $conexion->error;
    exit();
}

$stmt->bind_param("ii", $usuario_id, $artistaAntonio);
$stmt->execute();
$stmt->bind_result($comentariosVR);
$stmt->fetch();
$stmt->close();

// Devolver a Unity:
// OK|usuario_id|numero_comentarios_vr
echo 'OK|' . $usuario_id . '|' . $comentariosVR;

?>