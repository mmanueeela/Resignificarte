<?php
require_once __DIR__ . '/../conexion.php';

function buscarUsuarioPorEmail($conexion, $email) {
    // Buscamos en 'usuarios_credenciales'
    $stmt = $conexion->prepare("SELECT usuario_id AS id FROM usuarios_credenciales WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
    $stmt->close();

    return $usuario;
}

function generarTokenRecuperacion($conexion, $email) {
    // Forzamos la zona horaria correcta de España
    date_default_timezone_set('Europe/Madrid');

    $token = bin2hex(random_bytes(32));
    // Fecha actual + 30 minutos en formato MySQL (YYYY-MM-DD HH:MM:SS)
    $expiracion = date("Y-m-d H:i:s", strtotime("+30 minutes"));

    $stmt = $conexion->prepare("UPDATE usuarios_credenciales SET reset_token = ?, reset_expiracion = ? WHERE email = ?");
    $stmt->bind_param("sss", $token, $expiracion, $email);
    $stmt->execute();
    $stmt->close();

    return $token;
}

function enviarCorreoRecuperacion($email, $token) {
    $enlace = "https://tuweb.com/restablecer_contrasena.php?token=" . $token;
    $asunto = "Restablecer contraseña - Resignificarte";
    $mensaje = "Hola,\n\nHas solicitado restablecer tu contraseña. Haz clic en el siguiente enlace:\n\n" . $enlace . "\n\nEste enlace expirará en 30 minutos.\n\nSi no fuiste tú, ignora este mensaje.";
    $cabeceras = "From: no-reply@resignificarte.com";

    return mail($email, $asunto, $mensaje, $cabeceras);
}