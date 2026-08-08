<?php
// php/redireccion_logueado.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Usamos __DIR__ para que busque en la misma carpeta donde está este archivo
require_once __DIR__ . '/../conexion.php';

// 1. Si YA tiene la sesión activa, lo mandamos directo a su homepage
if (isset($_SESSION['usuario_id'])) {
    header("Location: homepage.php");
    exit();
}

// 2. Si no tiene sesión, pero SÍ tiene cookie de recuérdame...
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

            header("Location: homepage.php");
            exit();
        }
        $stmt->close();
    }
}
?>