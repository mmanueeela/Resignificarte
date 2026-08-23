<?php
session_start();
require_once 'conexion.php';

// 1. COMPROBACIÓN DE SEGURIDAD: Solo admins
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("Location: ../homepage.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_usuario'])) {
    $id_usuario = intval($_POST['id_usuario']);

    // Opcional: Buscar si tiene foto de perfil subida al servidor para borrar el archivo y no ocupar espacio
    $stmt_foto = $conexion->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
    $stmt_foto->bind_param("i", $id_usuario);
    $stmt_foto->execute();
    $stmt_foto->bind_result($foto_perfil);
    if ($stmt_foto->fetch() && !empty($foto_perfil) && strpos($foto_perfil, 'src/uploads/') !== false) {
        $ruta_archivo = '../' . $foto_perfil;
        if (file_exists($ruta_archivo)) {
            unlink($ruta_archivo); // Borra la imagen del servidor
        }
    }
    $stmt_foto->close();

    // 2. BORRAR AL USUARIO (El ON DELETE CASCADE borrará sus comentarios y credenciales automáticamente)
    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id_usuario);

    if ($stmt->execute()) {
        header("Location: ../homepage_administrador.php?msg=usuario_eliminado");
    } else {
        header("Location: ../homepage_administrador.php?error=1");
    }

    $stmt->close();
} else {
    header("Location: ../homepage_administrador.php");
}
exit();
?>