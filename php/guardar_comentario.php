<?php

require_once 'conexion.php';

header('Content-Type: text/plain; charset=utf-8');

$usuario_id = intval($_POST['usuario_id'] ?? 0);
$obra_id = intval($_POST['obra_id'] ?? 0);
$comentario = trim($_POST['comentario'] ?? '');

if ($usuario_id <= 0 || $obra_id <= 0 || empty($comentario)) {
    echo 'ERROR|Datos incompletos';
    exit();
}

// Comprobar que el usuario existe
$stmt = $conexion->prepare("
    SELECT id
    FROM usuarios
    WHERE id = ?
");

$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    $stmt->close();
    echo 'ERROR|Usuario no válido';
    exit();
}

$stmt->close();

// Comprobar si YA comentó esta obra desde VR
$stmt = $conexion->prepare("
    SELECT COUNT(*)
    FROM comentarios
    WHERE obra_id = ?
    AND usuario_id = ?
    AND origen = 'vr'
");

$stmt->bind_param("ii", $obra_id, $usuario_id);
$stmt->execute();
$stmt->bind_result($ya_comento);
$stmt->fetch();
$stmt->close();

if ($ya_comento > 0) {
    echo 'ERROR|Ya has comentado esta obra desde VR';
    exit();
}

// Guardar comentario VR
$stmt = $conexion->prepare("
    INSERT INTO comentarios
    (obra_id, usuario_id, comentario, origen)
    VALUES (?, ?, ?, 'vr')
");

$stmt->bind_param(
    "iis",
    $obra_id,
    $usuario_id,
    $comentario
);

if (!$stmt->execute()) {
    echo 'ERROR|No se pudo guardar el comentario';
    $stmt->close();
    exit();
}

$stmt->close();

// Contar obras NORMALES de Antonio Nieto comentadas desde VR
$artistaAntonio = 1;

$stmt = $conexion->prepare("
    SELECT COUNT(DISTINCT c.obra_id)
    FROM comentarios c
    JOIN obras o ON c.obra_id = o.id
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

echo 'OK|' . $comentadas_vr;

?>