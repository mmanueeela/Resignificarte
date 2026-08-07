<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "HEADER FUNCIONANDO";

$usuario_logeado = false;
$nombre_usuario = "";
$ruta_foto = "src/iconos/usuario.png";


if(isset($_SESSION['usuario_id'])) {

    require_once __DIR__ . '/../conexion.php';

    $usuario_id = $_SESSION['usuario_id'];

    $consulta = "
        SELECT nombre
        FROM usuarios_credenciales
        WHERE usuario_id = ?
    ";

    $stmt = $conexion->prepare($consulta);

    if($stmt) {

        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if($usuario = $resultado->fetch_assoc()) {

            $usuario_logeado = true;

            $nombre_usuario = !empty($usuario['nombre'])
                ? $usuario['nombre']
                : "Usuario";
        }

        $stmt->close();
    }
}

?>