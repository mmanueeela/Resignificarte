<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

// Proteger
if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exito' => false, 'error' => 'No autorizado']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$obra_id = intval($_POST['obra_id'] ?? 0);
$comentario = trim($_POST['comentario'] ?? '');

if ($obra_id <= 0 || empty($comentario)) {
    echo json_encode(['exito' => false, 'error' => 'Datos vacíos']);
    exit();
}

// 1. Guardar el comentario
$stmt = $conexion->prepare("INSERT INTO comentarios (obra_id, usuario_id, comentario) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $obra_id, $usuario_id, $comentario);
$exito = $stmt->execute();
$stmt->close();

// 2. Comprobar si con este comentario desbloquea la recompensa
// Primero, sacamos a qué artista pertenece la obra
$stmt = $conexion->prepare("SELECT artista_id FROM obras WHERE id = ?");
$stmt->bind_param("i", $obra_id);
$stmt->execute();
$stmt->bind_result($artista_id);
$stmt->fetch();
$stmt->close();

// Contamos cuántas obras normales hay de este artista
$stmt = $conexion->prepare("SELECT COUNT(*) FROM obras WHERE artista_id = ? AND es_recompensa = 0");
$stmt->bind_param("i", $artista_id);
$stmt->execute();
$stmt->bind_result($total_obras);
$stmt->fetch();
$stmt->close();

// Contamos cuántas de esas obras normales ha comentado el usuario
$stmt = $conexion->prepare("
    SELECT COUNT(DISTINCT c.obra_id) 
    FROM comentarios c 
    JOIN obras o ON c.obra_id = o.id 
    WHERE o.artista_id = ? AND o.es_recompensa = 0 AND c.usuario_id = ?
");
$stmt->bind_param("ii", $artista_id, $usuario_id);
$stmt->execute();
$stmt->bind_result($obras_comentadas);
$stmt->fetch();
$stmt->close();

// Si ha comentado las 3, mandamos un aviso de desbloqueo al JavaScript
$recompensa_desbloqueada = ($obras_comentadas >= $total_obras);

echo json_encode([
    'exito' => $exito,
    'nombre_usuario' => $_SESSION['usuario_nombre'],
    'recompensa_desbloqueada' => $recompensa_desbloqueada
]);
?>