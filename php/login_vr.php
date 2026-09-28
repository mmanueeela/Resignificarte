<?php

require_once 'conexion.php';

header('Content-Type: text/plain; charset=utf-8');

$telefono = trim($_POST['telefono'] ?? '');

if (empty($telefono)) {
    echo 'ERROR|Teléfono vacío';
    exit();
}

// ======================================================
// 1. BUSCAR USUARIO
// ======================================================

$stmt = $conexion->prepare("
    SELECT u.id
    FROM usuarios u
    INNER JOIN usuarios_credenciales c ON c.usuario_id = u.id
    WHERE c.telefono = ?
    LIMIT 1
");

if (!$stmt) {
    echo 'ERROR|Servidor';
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

// ======================================================
// 2. CONTAR OBRAS NORMALES COMENTADAS (WEB + VR)
// ======================================================

$artistaAntonio = 1;

$stmt = $conexion->prepare("
    SELECT COUNT(DISTINCT c.obra_id)
    FROM comentarios c
    INNER JOIN obras o ON c.obra_id = o.id
    WHERE c.usuario_id = ?
      AND o.artista_id = ?
      AND o.es_recompensa = 0
");

if (!$stmt) {
    echo 'ERROR|Servidor';
    exit();
}

$stmt->bind_param("ii", $usuario_id, $artistaAntonio);
$stmt->execute();
$stmt->bind_result($comentarios);
$stmt->fetch();
$stmt->close();

// ======================================================
// 3. COMPROBAR OBRA FINAL (WEB + VR)
// ======================================================

$stmt = $conexion->prepare("
    SELECT COUNT(*)
    FROM comentarios c
    INNER JOIN obras o ON c.obra_id = o.id
    WHERE c.usuario_id = ?
      AND o.artista_id = ?
      AND o.es_recompensa = 1
");

if (!$stmt) {
    echo 'ERROR|Servidor';
    exit();
}

$stmt->bind_param("ii", $usuario_id, $artistaAntonio);
$stmt->execute();
$stmt->bind_result($comentarioFinal);
$stmt->fetch();
$stmt->close();

$finalComentada = ($comentarioFinal > 0) ? 1 : 0;

// ======================================================
// 4. RESPUESTA A UNITY (OK|usuario_id|comentarios|finalComentada)
// ======================================================

echo "OK|{$usuario_id}|{$comentarios}|{$finalComentada}";