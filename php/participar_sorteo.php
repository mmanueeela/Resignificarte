<?php

session_start();

require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

$usuario_id = intval($_SESSION['usuario_id']);
$obra_id = intval($_POST['obra_id'] ?? 0);
$artista_id = intval($_POST['artista_id'] ?? 0);

// ======================================================
// 1. COMPROBAR DATOS
// ======================================================

if ($obra_id <= 0 || $artista_id <= 0) {
    header("Location: ../artistas.php");
    exit();
}

// ======================================================
// 2. COMPROBAR QUE ES LA OBRA FINAL DE ANTONIO NIETO
// ======================================================

$stmt = $conexion->prepare("
    SELECT artista_id, es_recompensa
    FROM obras
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param("i", $obra_id);
$stmt->execute();
$stmt->bind_result($obra_artista_id, $es_recompensa);

if (!$stmt->fetch()) {
    $stmt->close();
    header("Location: ../Obras_Artista.php?id={$artista_id}&sorteo_error=obra");
    exit();
}
$stmt->close();

if ($obra_artista_id != 1 || $es_recompensa != 1) {
    header("Location: ../Obras_Artista.php?id={$artista_id}&sorteo_error=obra");
    exit();
}

// ======================================================
// 3. COMPROBAR QUE EL USUARIO HA COMENTADO LA OBRA FINAL (WEB + RV)
// ======================================================

$stmt = $conexion->prepare("
    SELECT id
    FROM comentarios
    WHERE usuario_id = ?
      AND obra_id = ?
    ORDER BY id ASC
    LIMIT 1
");
$stmt->bind_param("ii", $usuario_id, $obra_id);
$stmt->execute();
$stmt->bind_result($comentario_id);

if (!$stmt->fetch()) {
    $stmt->close();
    header("Location: ../Obras_Artista.php?id={$artista_id}&sorteo_error=sin_comentario");
    exit();
}
$stmt->close();

// ======================================================
// 4. COMPROBAR SI YA PARTICIPA
// ======================================================

$stmt = $conexion->prepare("
    SELECT COUNT(*)
    FROM sorteo_antonio_nieto
    WHERE usuario_id = ?
      AND obra_id = ?
");
$stmt->bind_param("ii", $usuario_id, $obra_id);
$stmt->execute();
$stmt->bind_result($ya_participa);
$stmt->fetch();
$stmt->close();

if ($ya_participa > 0) {
    header("Location: ../Obras_Artista.php?id={$artista_id}&sorteo=ya_participa#obra-secreta");
    exit();
}

// ======================================================
// 5. COMPROBAR IMAGEN
// ======================================================

if (!isset($_FILES['imagen_sorteo']) || $_FILES['imagen_sorteo']['error'] === UPLOAD_ERR_NO_FILE) {
    header("Location: ../Obras_Artista.php?id={$artista_id}&sorteo_error=sin_imagen#obra-secreta");
    exit();
}

$archivo = $_FILES['imagen_sorteo'];

if ($archivo['error'] !== UPLOAD_ERR_OK) {
    header("Location: ../Obras_Artista.php?id={$artista_id}&sorteo_error=subida#obra-secreta");
    exit();
}

// ======================================================
// 6. TAMAÑO MÁXIMO 5 MB
// ======================================================

$maximo = 5 * 1024 * 1024;

if ($archivo['size'] > $maximo) {
    header("Location: ../Obras_Artista.php?id={$artista_id}&sorteo_error=tamano#obra-secreta");
    exit();
}

// ======================================================
// 7. COMPROBAR QUE ES UN ARCHIVO SUBIDO REALMENTE
// ======================================================

if (!is_uploaded_file($archivo['tmp_name'])) {
    header("Location: ../Obras_Artista.php?id={$artista_id}&sorteo_error=archivo#obra-secreta");
    exit();
}

// ======================================================
// 8. COMPROBAR TIPO DE IMAGEN
// ======================================================

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($archivo['tmp_name']);

$tipos_permitidos = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

if (!isset($tipos_permitidos[$mime])) {
    header("Location: ../Obras_Artista.php?id={$artista_id}&sorteo_error=formato#obra-secreta");
    exit();
}

// ======================================================
// 9. PREPARAR NOMBRE Y RUTA
// ======================================================

$extension = $tipos_permitidos[$mime];
$nombre_archivo = bin2hex(random_bytes(16)) . '.' . $extension;

$directorio_fisico = dirname(__DIR__) . '/src/uploads/sorteo_antonio_nieto/';
$directorio_bd = 'src/uploads/sorteo_antonio_nieto/';

if (!is_dir($directorio_fisico) && !mkdir($directorio_fisico, 0755, true)) {
    header("Location: ../Obras_Artista.php?id={$artista_id}&sorteo_error=carpeta#obra-secreta");
    exit();
}

$ruta_fisica = $directorio_fisico . $nombre_archivo;
$ruta_bd = $directorio_bd . $nombre_archivo;

// ======================================================
// 10. GUARDAR IMAGEN
// ======================================================

if (!move_uploaded_file($archivo['tmp_name'], $ruta_fisica)) {
    header("Location: ../Obras_Artista.php?id={$artista_id}&sorteo_error=subida#obra-secreta");
    exit();
}

// ======================================================
// 11. REGISTRAR PARTICIPACIÓN
// ======================================================

$stmt = $conexion->prepare("
    INSERT INTO sorteo_antonio_nieto (usuario_id, obra_id, comentario_id, imagen)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("iiis", $usuario_id, $obra_id, $comentario_id, $ruta_bd);

if (!$stmt->execute()) {
    $stmt->close();

    if (file_exists($ruta_fisica)) {
        unlink($ruta_fisica);
    }

    header("Location: ../Obras_Artista.php?id={$artista_id}&sorteo_error=bd#obra-secreta");
    exit();
}
$stmt->close();

// ======================================================
// 12. TODO CORRECTO
// ======================================================

header("Location: ../Obras_Artista.php?id={$artista_id}&sorteo=ok#obra-secreta");
exit();