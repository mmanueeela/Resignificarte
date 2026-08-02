<?php
session_start();

// Definimos esta constante para que 'crearUsuario.php' sepa que está siendo llamado legalmente
define('ACCESO_PERMITIDO', true);

// 1. Requerimos las piezas clave
require_once 'conexion.php';
require_once 'logicaNegocio/crearUsuario.php';

// 2. Comprobamos que el formulario se ha enviado por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recogemos y limpiamos los datos para evitar espacios en blanco extra
    $nombre           = trim(isset($_POST['nombre']) ? $_POST['nombre'] : '');
    $apellidos        = trim(isset($_POST['apellidos']) ? $_POST['apellidos'] : '');
    $pais             = trim(isset($_POST['pais']) ? $_POST['pais'] : '');
    $dia              = trim(isset($_POST['dia']) ? $_POST['dia'] : '');
    $mes              = trim(isset($_POST['mes']) ? $_POST['mes'] : '');
    $anyo             = trim(isset($_POST['anyo']) ? $_POST['anyo'] : '');
    $email            = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password         = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validaciones de seguridad en el servidor
    if (empty($nombre) || empty($apellidos) || empty($pais) || empty($dia) || empty($mes) || empty($anyo) || empty($email) || empty($password)) {
        die("Error: Todos los campos son obligatorios.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: El formato del correo no es válido.");
    }

    if (strlen($password) < 8) {
        die("Error: La contraseña debe tener al menos 8 caracteres.");
    }

    if (!preg_match('/[A-Z]/', $password)) {
        die("Error: La contraseña debe tener al menos una letra mayúscula.");
    }

    if (!preg_match('/[0-9]/', $password)) {
        die("Error: La contraseña debe tener al menos un número.");
    }

    if ($password !== $confirm_password) {
        die("Error: Las contraseñas no coinciden.");
    }

    // Unimos los 3 campos de fecha en el formato de base de datos (YYYY-MM-DD)
    $fecha_nacimiento = $anyo . '-' . str_pad($mes, 2, "0", STR_PAD_LEFT) . '-' . str_pad($dia, 2, "0", STR_PAD_LEFT);

    // 3. ¡La magia de la separación de lógica! Llamamos a nuestra función
    $resultado = registrarUsuario($conexion, $nombre, $apellidos, $pais, $fecha_nacimiento, $email, $password);

    // 4. Actuamos en base al resultado
    if ($resultado['exito']) {
        // LOGIN AUTOMÁTICO: Guardamos sus datos en la sesión
        $_SESSION['usuario_id']     = $resultado['id'];
        $_SESSION['usuario_nombre'] = $nombre;

        // --- INICIO LÓGICA DE RECUÉRDAME AUTOMÁTICO ---
        $token_cookie = bin2hex(random_bytes(32));
        $token_hasheado = hash('sha256', $token_cookie);

        $update_token = $conexion->prepare("UPDATE usuarios_credenciales SET remember_token = ? WHERE usuario_id = ?");
        if ($update_token) {
            $update_token->bind_param("si", $token_hasheado, $resultado['id']);
            $update_token->execute();
            $update_token->close();
        }

        setcookie("recuerdame_token", $token_cookie, [
            'expires' => time() + (86400 * 30),
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        // --- FIN LÓGICA DE RECUÉRDAME AUTOMÁTICO ---

        // ==========================================
        // ENVIAR CORREO DE BIENVENIDA (REGISTRO MANUAL)
        // ==========================================
        $asunto = "¡Bienvenido a Resignificarte!";
        $mensaje = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Bienvenido a Resignificarte</title>
        </head>
        <body style="margin: 0; padding: 0; background-color: #f4f1f8; font-family: Arial, Helvetica, sans-serif; color: #333333;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f1f8; padding: 40px 15px;">
            <tr>
                <td align="center">
                    <!-- TARJETA PRINCIPAL -->
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
                        <!-- CABECERA -->
                        <tr>
                            <td align="center" style="padding:0;">
                                <img src="https://mzazzar.upv.edu.es/src/logo/logo_email.png" width="600" style="display:block; width:100%; max-width:600px; height:auto; border:0;" alt="Resignificarte">
                            </td>
                        </tr>
                        <!-- CONTENIDO -->
                        <tr>
                            <td style="padding: 40px 40px 30px 40px;">
                                <h1 style="margin: 0 0 20px 0; font-size: 26px; line-height: 1.3; color: #333333; font-weight: 600;">
                                    ¡Hola ' . htmlspecialchars($nombre) . '!
                                </h1>
                                <p style="margin: 0 0 18px 0; font-size: 16px; line-height: 1.7; color: #555555;">
                                    Gracias por registrarte en <strong style="color: #6f42c1;">Resignificarte</strong>.
                                </p>
                                <p style="margin: 0 0 25px 0; font-size: 16px; line-height: 1.7; color: #555555;">
                                    Estamos muy felices de tenerte con nosotros.
                                </p>
                                <!-- SEPARADOR -->
                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td style="border-top: 1px solid #eeeeee; font-size: 1px; line-height: 1px;">
                                            &nbsp;
                                        </td>
                                    </tr>
                                </table>
                                <p style="margin: 25px 0 5px 0; font-size: 16px; line-height: 1.6; color: #555555;">
                                    Un abrazo,
                                </p>
                                <p style="margin: 0; font-size: 16px; font-weight: bold; color: #6f42c1;">
                                    El equipo de Resignificarte
                                </p>
                            </td>
                        </tr>
                        <!-- PIE -->
                        <tr>
                            <td align="center" style="background-color: #faf9fc; padding: 22px 30px; border-top: 1px solid #eeeeee;">
                                <p style="margin: 0; font-size: 12px; line-height: 1.6; color: #999999;">
                                    Este es un mensaje automático, por favor no respondas a este correo.
                                </p>
                                <p style="margin: 8px 0 0 0; font-size: 12px; color: #b0b0b0;">
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
        $cabeceras .= "From: mzazzar@epsg.upv.es\r\n";
        $cabeceras .= "Reply-To: mzazzar@epsg.upv.es\r\n";

        // Comprobación detallada del envío con el parámetro -f incluido
        $enviado = mail(
            $email,
            $asunto,
            $mensaje,
            $cabeceras,
            "-f mzazzar@epsg.upv.es"
        );

        if (!$enviado) {
            die("ERROR CRÍTICO: La función mail() ha fallado y el servidor ha bloqueado el envío.");
        }
        // ==========================================

        // Lo enviamos directo a su homepage
        header("Location: ../homepage_usuario_registrado.php");
        exit();
    } else {
        // Si falla (ej. correo duplicado), mostramos el error
        echo "Error: " . $resultado['mensaje'];
    }

} else {
    // Si alguien intenta entrar directamente a esta URL sin enviar formulario
    header("Location: ../registro.php");
    exit();
}
?>