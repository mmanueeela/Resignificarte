<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../conexion.php';

// 1. Si no hay sesión activa, pero SÍ hay una cookie de recuérdame...
if (!isset($_SESSION['usuario_id']) && isset($_COOKIE['recuerdame_token'])) {

    $token_cookie = $_COOKIE['recuerdame_token'];

    // Convertimos el token de la cookie a hash para poder compararlo con la BD
    $token_hasheado = hash('sha256', $token_cookie);

    // Buscamos a quién le pertenece este token usando el HASH
    $consulta = "SELECT id, nombre FROM usuarios WHERE remember_token = ?";
    $stmt = $conexion->prepare($consulta);
    $stmt->bind_param("s", $token_hasheado);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // Si el token es válido y coincide con un usuario
    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Le iniciamos la sesión automáticamente
        $_SESSION['usuario_id']     = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
    }
    $stmt->close();
}

// 2. Si no tiene sesión lo llevamos al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?error=" . urlencode("Debes iniciar sesión para acceder."));
    exit();
}
?>