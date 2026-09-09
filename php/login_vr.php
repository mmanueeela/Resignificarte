<?php

require_once 'conexion.php';

header('Content-Type: text/plain; charset=utf-8');

$telefono = trim($_POST['telefono'] ?? '');

if (empty($telefono)) {
    echo 'ERROR|Teléfono vacío';
    exit();
}

// Buscar usuario
$stmt = $conexion->prepare("
    SELECT u.id
    FROM usuarios u
    JOIN usuarios_credenciales c
        ON c.usuario_id = u.id
    WHERE c.telefono = ?
    LIMIT 1
");

$stmt->bind_param("s", $telefono);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    $stmt->close();

    echo 'ERROR|Usuario no encontrado';
    exit();
}

$usuario = $resultado->fetch_assoc();
$usuario_id = intval($usuario['id']);

$stmt->close();


// Contar obras normales de Antonio Nieto
// comentadas DESDE VR

$artistaAntonio = 1;

$stmt = $conexion->prepare("
    SELECT COUNT(DISTINCT c.obra_id)
    FROM comentarios c
    JOIN obras o
        ON c.obra_id = o.id
    WHERE c.usuario_id = ?
    AND c.origen = 'vr'
    AND o.artista_id = ?
    AND o.es_recompensa = 0
");

$stmt->bind_param(
    "ii",
    $usuario_id,
    $artistaAntonio
);

$stmt->execute();

$stmt->bind_result($comentadas_vr);
$stmt->fetch();
$stmt->close();

echo 'OK|' . $usuario_id . '|' . $comentadas_vr;

?>