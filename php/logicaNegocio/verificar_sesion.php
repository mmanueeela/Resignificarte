<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../conexion.php';

// 1. Si no hay sesión activa, pero SÍ hay una cookie de recuérdame...
if (!isset($_SESSION['usuario_id']) && isset($_COOKIE['recuerdame_token'])) {

    $token_cookie = $_COOKIE['recuerdame_token'];
    $token_hasheado = hash('sha256', $token_cookie);

    // Buscamos el token en 'usuarios_credenciales' y unimos con 'usuarios'
    $consulta = "SELECT u.id, u.nombre 
                 FROM usuarios_credenciales c
                 JOIN usuarios u ON c.usuario_id = u.id
                 WHERE c.remember_token = ?";
    $stmt = $conexion->prepare($consulta);

    if ($stmt) {
        $stmt->bind_param("s", $token_hasheado);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();

            $_SESSION['usuario_id']     = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];

            header("Location: homepage_usuario_registrado.php");
            exit();
        }
        $stmt->close();
    }
}

// 2. Si no tiene sesión lo llevamos al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?error=" . urlencode("Debes iniciar sesión para acceder."));
    exit();
}
?>