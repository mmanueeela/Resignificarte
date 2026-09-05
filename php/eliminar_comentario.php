<?php
session_start();
require_once 'conexion.php';

// 1. COMPROBACIÓN DE SEGURIDAD: Solo administradores
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("Location: ../homepage.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_comentario']) || !isset($_POST['id_artista'])) {
    header("Location: ../artistas.php");
    exit();
}

$id_comentario = intval($_POST['id_comentario']);
$id_artista = intval($_POST['id_artista']);

if ($id_comentario <= 0 || $id_artista <= 0) {
    header("Location: ../artistas.php");
    exit();
}

// 2. COMPROBAR SI EL COMENTARIO ESTÁ ASOCIADO A UNA PARTICIPACIÓN DEL SORTEO
$ruta_imagen = null;

$stmt = $conexion->prepare("
    SELECT imagen
    FROM sorteo_antonio_nieto
    WHERE comentario_id = ?
");

$stmt->bind_param("i", $id_comentario);
$stmt->execute();
$stmt->bind_result($imagen_sorteo);

if ($stmt->fetch()) {
    $ruta_imagen = $imagen_sorteo;
}

$stmt->close();

$conexion->begin_transaction();

try {
    // 3. SI EXISTE PARTICIPACIÓN EN EL SORTEO, ELIMINARLA DE LA BASE DE DATOS
    $stmt = $conexion->prepare("
        DELETE FROM sorteo_antonio_nieto
        WHERE comentario_id = ?
    ");

    $stmt->bind_param("i", $id_comentario);

    if (!$stmt->execute()) {
        throw new Exception('No se ha podido eliminar la participación del sorteo');
    }

    $stmt->close();

    // 4. ELIMINAR EL COMENTARIO
    $stmt = $conexion->prepare("DELETE FROM comentarios WHERE id = ?");
    $stmt->bind_param("i", $id_comentario);

    if (!$stmt->execute()) {
        throw new Exception('No se ha podido eliminar el comentario');
    }

    $stmt->close();

    $conexion->commit();

} catch (Throwable $e) {
    $conexion->rollback();

    header("Location: ../Obras_Artista.php?id=" . $id_artista . "&error=1");
    exit();
}

// 5. ELIMINAR FÍSICAMENTE LA IMAGEN DEL SORTEO
if ($ruta_imagen) {
    $ruta_fisica = dirname(__DIR__) . '/' . ltrim($ruta_imagen, '/');

    if (file_exists($ruta_fisica) && is_file($ruta_fisica)) {
        if (!unlink($ruta_fisica)) {
            error_log("No se ha podido eliminar la imagen del sorteo: " . $ruta_fisica);
        }
    }
}

// 6. VOLVER A LAS OBRAS DEL ARTISTA
header("Location: ../Obras_Artista.php?id=" . $id_artista . "&msg=comentario_eliminado");
exit();
?>