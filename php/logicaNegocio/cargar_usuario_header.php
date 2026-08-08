<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../conexion.php';

$usuario_logeado = false;
$nombre_usuario = '';
$email_usuario = '';
$ruta_foto = 'src/iconos/usuario.png';

if (isset($_SESSION['usuario_id'])) {

    $usuario_logeado = true;
    $usuario_id = $_SESSION['usuario_id'];

    $consulta = "
        SELECT u.nombre, u.foto_perfil, c.email
        FROM usuarios u
        INNER JOIN usuarios_credenciales c ON u.id = c.usuario_id
        WHERE u.id = ?
    ";

    $stmt = $conexion->prepare($consulta);

    if ($stmt) {
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $usuario_bd = $resultado->fetch_assoc();

        $stmt->close();

        if ($usuario_bd) {

            $nombre_usuario = !empty($usuario_bd['nombre'])
                ? $usuario_bd['nombre']
                : 'Usuario';

            $email_usuario = !empty($usuario_bd['email'])
                ? $usuario_bd['email']
                : '';

            $foto_bd = isset($usuario_bd['foto_perfil'])
                ? trim($usuario_bd['foto_perfil'])
                : '';

            if (!empty($foto_bd) && strtolower($foto_bd) !== 'null') {
                $ruta_foto = $foto_bd;
            }
        }
    }
}
?>