<?php
session_start();
require_once 'conexion.php';

// Seguridad: Solo admins
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("Location: ../homepage.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_artista'])) {
    $id_artista = intval($_POST['id_artista']);

    // --- ¡NUEVO!: BLOQUEO DE SEGURIDAD PARA ANTONIO NIETO ---
    if ($id_artista === 1) {
        // Redirigimos con un mensaje de error (no se puede borrar la experiencia principal)
        header("Location: ../obras.php?error=protegido");
        exit();
    }
    // --------------------------------------------------------

    // Borrar el artista (Gracias a tu base de datos en cascada, al borrar el artista
    // se borrarán automáticamente todas sus obras en la tabla 'obras'
    // y todos los comentarios asociados a esas obras.)
    $stmt = $conexion->prepare("DELETE FROM artistas WHERE id = ?");
    $stmt->bind_param("i", $id_artista);

    if ($stmt->execute()) {
        header("Location: ../obras.php?msg=experiencia_eliminada");
    } else {
        header("Location: ../obras.php?error=1");
    }

    $stmt->close();
} else {
    header("Location: ../obras.php");
}
exit();
?>