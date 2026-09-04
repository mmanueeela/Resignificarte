<?php
session_start();
require_once 'conexion.php';

// 1. COMPROBACIÓN DE SEGURIDAD: Solo admins
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("Location: ../homepage.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_comentario']) && isset($_POST['id_artista'])) {
    $id_comentario = intval($_POST['id_comentario']);
    $id_artista = intval($_POST['id_artista']); // Lo necesitamos para saber a qué página devolverte

    // 2. BORRAR EL COMENTARIO
    $stmt = $conexion->prepare("DELETE FROM comentarios WHERE id = ?");
    $stmt->bind_param("i", $id_comentario);

    if ($stmt->execute()) {
        // Devolver a la página de las obras del artista exacto en el que estábamos
        header("Location: ../Obras_Artista.php?id=" . $id_artista . "&msg=comentario_eliminado");
    } else {
        header("Location: ../Obras_Artista.php?id=" . $id_artista . "&error=1");
    }

    $stmt->close();
} else {
    header("Location: ../artistas.php");
}
exit();
?>