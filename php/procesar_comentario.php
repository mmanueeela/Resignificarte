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

// 1. Comprobar obra
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

// 2. Evitar segundo comentario
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

// 3. Número de obras normales
$stmt = $conexion->prepare("SELECT COUNT(*) FROM obras WHERE artista_id = ? AND es_recompensa = 0");
$stmt->bind_param("i", $artista_id);
$stmt->execute();
$stmt->bind_result($total_normales);
$stmt->fetch();
$stmt->close();

// 4. Obras normales ya comentadas
$stmt = $conexion->prepare("
    SELECT COUNT(DISTINCT c.obra_id)
    FROM comentarios c
    JOIN obras o ON c.obra_id = o.id
    WHERE o.artista_id = ? AND o.es_recompensa = 0 AND c.usuario_id = ?
");
$stmt->bind_param("ii", $artista_id, $usuario_id);
$stmt->execute();
$stmt->bind_result($comentadas_antes);
$stmt->fetch();
$stmt->close();

// Impedir acceder directamente a una recompensa sin desbloquearla
if ($es_recompensa == 1 && ($comentadas_antes < $total_normales || $total_normales <= 0)) {
    echo json_encode(['exito' => false, 'error' => 'Todavía no has desbloqueado esta obra.']);
    exit();
}

$es_sorteo_antonio = ($artista_id == 1 && $es_recompensa == 1);
$participa_sorteo = false;
$ruta_fisica_imagen = null;
$ruta_bd_imagen = null;

// 5. Validar imagen opcional del sorteo ANTES de guardar el comentario
if ($es_sorteo_antonio && isset($_FILES['imagen_sorteo']) && $_FILES['imagen_sorteo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $archivo = $_FILES['imagen_sorteo'];

    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['exito' => false, 'error' => 'No se ha podido subir la imagen.']);
        exit();
    }

    $maximo = 5 * 1024 * 1024;

    if ($archivo['size'] > $maximo) {
        echo json_encode(['exito' => false, 'error' => 'La imagen no puede superar los 5 MB.']);
        exit();
    }

    if (!is_uploaded_file($archivo['tmp_name'])) {
        echo json_encode(['exito' => false, 'error' => 'El archivo recibido no es válido.']);
        exit();
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($archivo['tmp_name']);

    $tipos_permitidos = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($tipos_permitidos[$mime])) {
        echo json_encode(['exito' => false, 'error' => 'Solo se permiten imágenes JPG, PNG o WEBP.']);
        exit();
    }

    $extension = $tipos_permitidos[$mime];
    $nombre_archivo = bin2hex(random_bytes(16)) . '.' . $extension;

    $directorio_fisico = dirname(__DIR__) . '/src/uploads/sorteo_antonio_nieto/';
    $directorio_bd = 'src/uploads/sorteo_antonio_nieto/';

    if (!is_dir($directorio_fisico) && !mkdir($directorio_fisico, 0755, true)) {
        echo json_encode(['exito' => false, 'error' => 'No se ha podido preparar la carpeta para guardar la imagen.']);
        exit();
    }

    $ruta_fisica_imagen = $directorio_fisico . $nombre_archivo;
    $ruta_bd_imagen = $directorio_bd . $nombre_archivo;
    $participa_sorteo = true;
}

$conexion->begin_transaction();

try {
    // 6. Guardar comentario
    $stmt = $conexion->prepare("INSERT INTO comentarios (obra_id, usuario_id, comentario) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $obra_id, $usuario_id, $comentario);

    if (!$stmt->execute()) {
        throw new Exception('No se ha podido guardar el comentario');
    }

    $comentario_id = $conexion->insert_id;
    $stmt->close();

    // 7. Guardar imagen y participación
    if ($participa_sorteo) {
        if (!move_uploaded_file($_FILES['imagen_sorteo']['tmp_name'], $ruta_fisica_imagen)) {
            throw new Exception('No se ha podido guardar la imagen del sorteo');
        }

        $stmt = $conexion->prepare("INSERT INTO sorteo_antonio_nieto (usuario_id, obra_id, comentario_id, imagen) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $usuario_id, $obra_id, $comentario_id, $ruta_bd_imagen);

        if (!$stmt->execute()) {
            throw new Exception('No se ha podido registrar la participación en el sorteo');
        }

        $stmt->close();
    }

    $conexion->commit();

} catch (Throwable $e) {
    $conexion->rollback();

    if ($ruta_fisica_imagen && file_exists($ruta_fisica_imagen)) {
        unlink($ruta_fisica_imagen);
    }

    echo json_encode([
        'exito' => false,
        'error' => $e->getMessage()
    ]);
    exit();
}

// 8. Recalcular obras comentadas
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

$recompensa_desbloqueada = ($comentadas >= $total_normales && $total_normales > 0);

echo json_encode([
    'exito' => true,
    'nombre_usuario' => $_SESSION['usuario_nombre'] ?? '',
    'comentadas' => $comentadas,
    'total_normales' => $total_normales,
    'recompensa_desbloqueada' => $recompensa_desbloqueada,
    'participa_sorteo' => $participa_sorteo
]);
?>