<?php
session_start();

require_once 'conexion.php';

// ==========================================
// COMPROBAR QUE VIENE DE GOOGLE
// ==========================================
if (!isset($_SESSION['registro_google'])) {
    header("Location: ../login.php");
    exit();
}

// ==========================================
// COMPROBAR POST
// ==========================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../telefono_google.php");
    exit();
}

// ==========================================
// RECOGER TELÉFONO
// ==========================================
$telefono = trim(
    isset($_POST['telefono'])
        ? $_POST['telefono']
        : ''
);

// ==========================================
// VALIDAR TELÉFONO
// ==========================================
if (empty($telefono)) {
    die("Error: El teléfono es obligatorio.");
}

// Permitimos números, espacios, +, paréntesis y guiones
if (!preg_match('/^[0-9+\s()-]{7,20}$/', $telefono)) {
    die("Error: El número de teléfono no es válido.");
}

// ==========================================
// RECUPERAR DATOS DE GOOGLE
// ==========================================
$datos_google = $_SESSION['registro_google'];

$email = $datos_google['email'];
$nombre = $datos_google['nombre'];
$apellidos = $datos_google['apellidos'];
$foto_perfil = $datos_google['foto_perfil'];

// ==========================================
// DATOS GENÉRICOS
// ==========================================
$pais_generico = "ND";
$fecha_generica = "2000-01-01";

// Generamos una contraseña aleatoria.
// El usuario entra mediante Google, así que no
// necesita conocer esta contraseña.
$password_aleatoria = password_hash(
    bin2hex(random_bytes(10)),
    PASSWORD_DEFAULT
);

// ==========================================
// INSERTAR USUARIO
// ==========================================
$insertar_user = $conexion->prepare("
    INSERT INTO usuarios
    (
        nombre,
        apellidos,
        pais,
        fecha_nacimiento,
        foto_perfil
    )
    VALUES (?, ?, ?, ?, ?)
");

$insertar_user->bind_param(
    "sssss",
    $nombre,
    $apellidos,
    $pais_generico,
    $fecha_generica,
    $foto_perfil
);

if (!$insertar_user->execute()) {
    $error = $conexion->error;
    $insertar_user->close();
    die("Error al registrar usuario: " . $error);
}

$nuevo_id = $insertar_user->insert_id;
$insertar_user->close();

// ==========================================
// INSERTAR CREDENCIALES
// ==========================================
$insertar_cred = $conexion->prepare("
    INSERT INTO usuarios_credenciales
    (
        usuario_id,
        telefono,
        email,
        password,
        metodo_registro
    )
    VALUES (?, ?, ?, ?, 'google')
");

$insertar_cred->bind_param(
    "isss",
    $nuevo_id,
    $telefono,
    $email,
    $password_aleatoria
);

if (!$insertar_cred->execute()) {
    $error = $conexion->error;
    $insertar_cred->close();
    die("Error al guardar las credenciales: " . $error);
}

$insertar_cred->close();

// ==========================================
// LOGIN AUTOMÁTICO
// ==========================================
session_regenerate_id(true);

$_SESSION['usuario_id'] = $nuevo_id;
$_SESSION['usuario_nombre'] = $nombre;

// ==========================================
// RECUÉRDAME
// ==========================================
$token_cookie = bin2hex(random_bytes(32));

$token_hasheado = hash(
    'sha256',
    $token_cookie
);

$update_token = $conexion->prepare("
    UPDATE usuarios_credenciales
    SET remember_token = ?
    WHERE usuario_id = ?
");

$update_token->bind_param(
    "si",
    $token_hasheado,
    $nuevo_id
);

$update_token->execute();
$update_token->close();

setcookie("recuerdame_token", $token_cookie, [
    'expires' => time() + (86400 * 30),
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// ==========================================
// BORRAR DATOS TEMPORALES DE GOOGLE
// ==========================================
unset($_SESSION['registro_google']);

// ==========================================
// CORREO DE BIENVENIDA
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
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
                <tr>
                    <td align="center" style="padding:0;">
                        <img src="https://mzazzar.upv.edu.es/src/logo/logo_email.png" width="600" style="display:block; width:100%; max-width:600px; height:auto; border:0;" alt="Resignificarte">
                    </td>
                </tr>
                <tr>
                    <td style="padding:40px 40px 30px 40px;">
                        <h1 style="margin:0 0 20px 0; font-size:26px; line-height:1.3; color:#333333; font-weight:600;">
                            ¡Hola ' . htmlspecialchars($nombre) . '!
                        </h1>
                        <p style="margin:0 0 18px 0; font-size:16px; line-height:1.7; color:#555555;">
                            Gracias por registrarte en
                            <strong style="color:#6f42c1;">
                                Resignificarte
                            </strong>
                            usando tu cuenta de Google.
                        </p>
                        <p style="margin:0 0 25px 0; font-size:16px; line-height:1.7; color:#555555;">
                            Estamos muy felices de tenerte con nosotros.
                        </p>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="border-top:1px solid #eeeeee; font-size:1px; line-height:1px;">
                                    &nbsp;
                                </td>
                            </tr>
                        </table>
                        <p style="margin:25px 0 5px 0; font-size:16px; line-height:1.6; color:#555555;">
                            Un abrazo,
                        </p>
                        <p style="margin:0; font-size:16px; font-weight:bold; color:#6f42c1;">
                            El equipo de Resignificarte
                        </p>
                    </td>
                </tr>
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
$cabeceras .= "From: mzazzar@epsg.upv.es\r\n";
$cabeceras .= "Reply-To: mzazzar@epsg.upv.es\r\n";

mail(
    $email,
    $asunto,
    $mensaje,
    $cabeceras,
    "-f mzazzar@epsg.upv.es"
);

// ==========================================
// IR A LA PÁGINA DEL USUARIO
// ==========================================
header("Location: ../homepage_usuario_registrado.php?registro=google_exito");
exit();
?>