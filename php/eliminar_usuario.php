<?php
session_start();
require_once 'conexion.php';

// 1. COMPROBACIÓN DE SEGURIDAD: Solo administradores
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("Location: ../homepage.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_usuario'])) {
    header("Location: ../homepage_administrador.php");
    exit();
}

$id_usuario = intval($_POST['id_usuario']);

if ($id_usuario === intval($_SESSION['usuario_id'])) {
    header("Location: ../homepage_administrador.php?error=no_puedes_eliminarte");
    exit();
}

if ($id_usuario <= 0 || $id_usuario === intval($_SESSION['usuario_id'])) {
    header("Location: ../homepage_administrador.php?error=1");
    exit();
}

// 2. GUARDAR LA RUTA DE LA FOTO DE PERFIL ANTES DE BORRAR AL USUARIO
$foto_perfil = null;

$stmt = $conexion->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($foto_encontrada);

if ($stmt->fetch()) {
    $foto_perfil = $foto_encontrada;
}

$stmt->close();

// 3. GUARDAR LAS RUTAS DE TODAS SUS IMÁGENES DEL SORTEO
$imagenes_sorteo = [];

$stmt = $conexion->prepare("
    SELECT imagen
    FROM sorteo_antonio_nieto
    WHERE usuario_id = ?
");

$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

while ($fila = $resultado->fetch_assoc()) {
    if (!empty($fila['imagen'])) {
        $imagenes_sorteo[] = $fila['imagen'];
    }
}

$stmt->close();

$conexion->begin_transaction();

try {
    // 4. ELIMINAR SUS PARTICIPACIONES DEL SORTEO
    $stmt = $conexion->prepare("
        DELETE FROM sorteo_antonio_nieto
        WHERE usuario_id = ?
    ");

    $stmt->bind_param("i", $id_usuario);

    if (!$stmt->execute()) {
        throw new Exception('No se han podido eliminar las participaciones del sorteo');
    }

    $stmt->close();

    // 5. ELIMINAR AL USUARIO
    // Los comentarios y credenciales se eliminan mediante ON DELETE CASCADE
    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id_usuario);

    if (!$stmt->execute()) {
        throw new Exception('No se ha podido eliminar el usuario');
    }

    $stmt->close();

    $conexion->commit();

} catch (Throwable $e) {
    $conexion->rollback();

    header("Location: ../homepage_administrador.php?error=1");
    exit();
}

// 6. ELIMINAR FÍSICAMENTE LAS IMÁGENES DEL SORTEO
foreach ($imagenes_sorteo as $imagen) {
    $ruta_fisica = dirname(__DIR__) . '/' . ltrim($imagen, '/');

    if (file_exists($ruta_fisica) && is_file($ruta_fisica)) {
        if (!unlink($ruta_fisica)) {
            error_log("No se ha podido eliminar la imagen del sorteo: " . $ruta_fisica);
        }
    }
}

// 7. ELIMINAR FÍSICAMENTE LA FOTO DE PERFIL
if (!empty($foto_perfil) && strpos($foto_perfil, 'src/uploads/') !== false) {
    $ruta_foto = dirname(__DIR__) . '/' . ltrim($foto_perfil, '/');

    if (file_exists($ruta_foto) && is_file($ruta_foto)) {
        if (!unlink($ruta_foto)) {
            error_log("No se ha podido eliminar la foto de perfil: " . $ruta_foto);
        }
    }
}

// 8. VOLVER AL PANEL
header("Location: ../homepage_administrador.php?msg=usuario_eliminado");
exit();
?>