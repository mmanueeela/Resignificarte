<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

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

// 1. Comprobar que la obra existe y obtener su artista
$stmt = $conexion->prepare("SELECT artista_id, es_recompensa FROM obras WHERE id = ?");
$stmt->bind_param("i", $obra_id);
$stmt->execute();
$stmt->bind_result($artista_id, $es_recompensa);

if (!$stmt->fetch()) {
    $stmt->close();
    echo json_encode(['exito' => false, 'error' => 'La obra no existe']);
    exit();
}
$stmt->close();

// 2. Comprobar si el usuario ya ha comentado esta obra
$stmt = $conexion->prepare("SELECT COUNT(*) FROM comentarios WHERE obra_id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $obra_id, $usuario_id);
$stmt->execute();
$stmt->bind_result($ya_comento);
$stmt->fetch();
$stmt->close();

if ($ya_comento > 0) {
    echo json_encode(['exito' => false, 'error' => 'Ya has comentado esta obra.']);
    exit();
}

// 3. Guardar el comentario
$stmt = $conexion->prepare("INSERT INTO comentarios (obra_id, usuario_id, comentario) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $obra_id, $usuario_id, $comentario);
$exito = $stmt->execute();
$stmt->close();

if (!$exito) {
    echo json_encode(['exito' => false, 'error' => 'No se ha podido guardar el comentario']);
    exit();
}

// 4. Contar cuántas obras normales existen
$stmt = $conexion->prepare("SELECT COUNT(*) FROM obras WHERE artista_id = ? AND es_recompensa = 0");
$stmt->bind_param("i", $artista_id);
$stmt->execute();
$stmt->bind_result($total_normales);
$stmt->fetch();
$stmt->close();

// 5. Contar cuántas obras normales ha comentado este usuario
$stmt = $conexion->prepare("
    SELECT COUNT(DISTINCT c.obra_id)
    FROM comentarios c
    JOIN obras o ON c.obra_id = o.id
    WHERE o.artista_id = ? AND o.es_recompensa = 0 AND c.usuario_id = ?
");
$stmt->bind_param("ii", $artista_id, $usuario_id);
$stmt->execute();
$stmt->bind_result($comentadas);
$stmt->fetch();
$stmt->close();

// 6. Comprobar desbloqueo
$recompensa_desbloqueada = ($comentadas >= $total_normales && $total_normales > 0);

echo json_encode([
    'exito' => true,
    'nombre_usuario' => $_SESSION['usuario_nombre'],
    'comentadas' => $comentadas,
    'total_normales' => $total_normales,
    'recompensa_desbloqueada' => $recompensa_desbloqueada
]);
?>