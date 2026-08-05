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

    $enlace = "https://mzazzar.upv.edu.es/php/restablecer_contrasena.php?token=" . urlencode($token);

    $asunto = "Restablecer contraseña - Resignificarte";

    $mensaje = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Restablecer contraseña - Resignificarte</title>
    </head>
    <body style="margin:0; padding:0; width:100%; background-color:#f4f1f8; font-family:Arial, Helvetica, sans-serif; color:#333333;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; background-color:#f4f1f8; padding:40px 15px;">
        <tr>
            <td align="center">
                <!-- TARJETA PRINCIPAL -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; background-color:#ffffff; border-radius:16px; overflow:hidden;">
                    <!-- CABECERA -->
                    <tr>
                        <td align="center" style="padding:0;">
                            <img src="https://mzazzar.upv.edu.es/src/logo/logo_email.png" width="600" style="display:block; width:100%; max-width:600px; height:auto; border:0; margin:0; padding:0;" alt="Resignificarte">
                        </td>
                    </tr>
                    <!-- CONTENIDO -->
                    <tr>
                        <td style="padding:40px 6%;">
                            <h1 style="margin:0 0 20px 0; font-size:26px; line-height:1.3; color:#333333; font-weight:600;">
                                ¿Has solicitado restablecer tu contraseña?
                            </h1>
                            <p style="margin:0 0 18px 0; font-size:16px; line-height:1.7; color:#555555;">
                                Hola,
                            </p>
                            <p style="margin:0 0 18px 0; font-size:16px; line-height:1.7; color:#555555;">
                                Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en <strong style="color:#6f42c1;">Resignificarte</strong>.
                            </p>
                            <p style="margin:0 0 25px 0; font-size:16px; line-height:1.7; color:#555555;">
                                Haz clic en el siguiente botón para crear una nueva contraseña:
                            </p>
                            <!-- BOTÓN -->
                            <table cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 30px auto;">
                                <tr>
                                    <td align="center" style="border-radius:8px; background-color:#6f42c1;">
                                        <a href="' . $enlace . '" style="display:inline-block; padding:14px 28px; font-size:15px; font-weight:bold; color:#ffffff; text-decoration:none; border-radius:8px; background-color:#6f42c1;">
                                            Restablecer contraseña
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <!-- SEPARADOR -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="border-top:1px solid #eeeeee; font-size:1px; line-height:1px;">
                                        &nbsp;
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:25px 0 12px 0; font-size:14px; line-height:1.6; color:#666666;">
                                Si el botón no funciona, copia y pega este enlace en tu navegador:
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7f5fa; border-radius:8px;">
                                <tr>
                                    <td style="padding:12px 15px; font-size:12px; line-height:1.5; color:#6f42c1; word-break:break-all;">
                                        ' . $enlace . '
                                    </td>
                                </tr>
                            </table>
                            <!-- AVISO -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:25px; background-color:#faf9fc; border-radius:8px;">
                                <tr>
                                    <td style="padding:15px; font-size:13px; line-height:1.6; color:#777777;">
                                        <strong style="color:#555555;">Importante:</strong>
                                        Este enlace expirará en 30 minutos.
                                        Si no solicitaste este cambio, puedes ignorar este mensaje de forma segura.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- PIE -->
                    <tr>
                        <td align="center" style="background-color:#faf9fc; padding:22px 30px; border-top:1px solid #eeeeee;">
                            <p style="margin:0; font-size:12px; line-height:1.6; color:#999999;">
                                Este es un mensaje automático, por favor no respondas a este correo.
                            </p>
                            <p style="margin:8px 0 0 0; font-size:12px; color:#b0b0b0;">
                                © ' . date("Y") . ' Resignificarte
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    </body>
    </html>
    ';

    $cabeceras  = "MIME-Version: 1.0\r\n";
    $cabeceras .= "Content-Type: text/html; charset=UTF-8\r\n";
    $cabeceras .= "From: resiignificaarte@gmail.com\r\n";
    $cabeceras .= "Reply-To: resiignificaarte@gmail.com\r\n";

    return mail(
        $email,
        $asunto,
        $mensaje,
        $cabeceras,
        "-f resiignificaarte@gmail.com"
    );
}