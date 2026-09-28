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

// ======================================================
// 1. COMPROBAR USUARIO
// ======================================================

$stmt = $conexion->prepare("
    SELECT id
    FROM usuarios
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    echo 'ERROR|Servidor';
    exit();
}

$stmt->bind_param("i", $usuario_id);

if (!$stmt->execute()) {
    $stmt->close();
    echo 'ERROR|Servidor';
    exit();
}

$stmt->bind_result($usuarioEncontrado);

if (!$stmt->fetch()) {
    $stmt->close();
    echo 'ERROR|Usuario no válido';
    exit();
}
$stmt->close();

// ======================================================
// 2. COMPROBAR SI YA COMENTÓ LA OBRA (WEB + VR)
// ======================================================

$stmt = $conexion->prepare("
    SELECT COUNT(*)
    FROM comentarios
    WHERE obra_id = ?
      AND usuario_id = ?
");

if (!$stmt) {
    echo 'ERROR|Servidor';
    exit();
}

$stmt->bind_param("ii", $obra_id, $usuario_id);
$stmt->execute();
$stmt->bind_result($ya_comento);
$stmt->fetch();
$stmt->close();

if ($ya_comento > 0) {
    echo 'ERROR|Ya has comentado esta obra';
    exit();
}

// ======================================================
// 3. COMPROBAR QUE LA OBRA EXISTE
// ======================================================

$stmt = $conexion->prepare("
    SELECT id
    FROM obras
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    echo 'ERROR|Servidor';
    exit();
}

$stmt->bind_param("i", $obra_id);
$stmt->execute();
$stmt->bind_result($obraEncontrada);

if (!$stmt->fetch()) {
    $stmt->close();
    echo 'ERROR|La obra no existe';
    exit();
}
$stmt->close();

// ======================================================
// 4. INSERTAR COMENTARIO (origen = 'vr')
// ======================================================

$stmt = $conexion->prepare("
    INSERT INTO comentarios (obra_id, usuario_id, comentario, origen)
    VALUES (?, ?, ?, 'vr')
");

if (!$stmt) {
    echo 'ERROR|Servidor';
    exit();
}

$stmt->bind_param("iis", $obra_id, $usuario_id, $comentario);

if (!$stmt->execute()) {
    $stmt->close();
    echo 'ERROR|No se pudo guardar el comentario';
    exit();
}
$stmt->close();

// ======================================================
// 5. CONTAR OBRAS NORMALES COMENTADAS (WEB + VR)
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
$stmt->bind_result($comentadas);
$stmt->fetch();
$stmt->close();

// ======================================================
// 6. COMPROBAR SI COMENTÓ LA OBRA FINAL (WEB + VR)
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
$stmt->bind_result($comentario_final);
$stmt->fetch();
$stmt->close();

$final_comentada = ($comentario_final > 0) ? 1 : 0;

// ======================================================
// 7. RESPUESTA A UNITY (OK|comentadas|final_comentada)
// ======================================================

echo "OK|{$comentadas}|{$final_comentada}";