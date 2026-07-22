<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../conexion.php';

// 1. Si YA tiene la sesión activa, lo mandamos directo a su homepage
if (isset($_SESSION['usuario_id'])) {
    header("Location: homepage_usuario_registrado.php");
    exit();
}

// 2. Si no tiene sesión, pero SÍ tiene cookie de recuérdame... intentamos el autologin
if (!isset($_SESSION['usuario_id']) && isset($_COOKIE['recuerdame_token'])) {

    $token_cookie = $_COOKIE['recuerdame_token'];
    $token_hasheado = hash('sha256', $token_cookie);

    $consulta = "SELECT id, nombre FROM usuarios WHERE remember_token = ?";
    $stmt = $conexion->prepare($consulta);

    if ($stmt) {
        $stmt->bind_param("s", $token_hasheado);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();

            // Creamos la sesión
            $_SESSION['usuario_id']     = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];

            // Como el autologin fue un éxito, lo mandamos a la zona de registrados
            header("Location: homepage_usuario_registrado.php");
            exit();
        }
        $stmt->close();
    }
}

// 3. SI EL CÓDIGO LLEGA HASTA AQUÍ: 
// Significa que NO está logueado y NO tiene cookie válida.
// No hacemos un header("Location..."). Simplemente dejamos que la página HTML de debajo se cargue.
?>