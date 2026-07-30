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
    // URL exacta a la futura página de restablecimiento en tu servidor
    $enlace = "https://mzazzar.upv.edu.es/restablecer_contrasena.php?token=" . $token;

    $asunto = "Restablecer contraseña - Resignificarte";

    $mensaje = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
            .container { background-color: #ffffff; padding: 20px; border-radius: 8px; max-width: 600px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            .btn { display: inline-block; padding: 12px 20px; background-color: #6c5ce7; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }
            .footer { font-size: 12px; color: #777777; margin-top: 30px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>¿Has solicitado restablecer tu contraseña?</h2>
            <p>Hola,</p>
            <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en <b>Resignificarte</b>.</p>
            <p>Haz clic en el siguiente botón para crear una nueva contraseña:</p>
            <a href="' . $enlace . '" class="btn">Restablecer contraseña</a>
            <p style="margin-top: 20px;">Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
            <p><small>' . $enlace . '</small></p>
            <p class="footer">Este enlace expirará en 30 minutos. Si no solicitaste este cambio, puedes ignorar este mensaje de forma segura.</p>
        </div>
    </body>
    </html>
    ';

    $cabeceras  = 'MIME-Version: 1.0' . "\r\n";
    $cabeceras .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $cabeceras .= 'From: Resignificarte <hola@mzazzaro.epsg.upv.es>' . "\r\n";

    return mail($email, $asunto, $mensaje, $cabeceras);
}