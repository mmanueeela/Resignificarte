<?php
session_start();
require_once 'conexion.php';

// ==========================================
// COMPROBAR QUE VIENE DE GOOGLE Y ES POST
// ==========================================
if (!isset($_SESSION['registro_google']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit();
}

$datos_google = $_SESSION['registro_google'];

// ==========================================
// RECOGER DATOS DEL FORMULARIO Y DE GOOGLE
// ==========================================
$email = $datos_google['email'];
$nombre = $datos_google['nombre'];
$foto_perfil = $datos_google['foto_perfil'];

// Si Google nos dio apellidos, los usamos. Si no, los cogemos del formulario.
$apellidos = !empty(trim($datos_google['apellidos']))
    ? $datos_google['apellidos']
    : trim($_POST['apellidos'] ?? '');

$telefono = trim($_POST['telefono'] ?? '');
$pais = trim($_POST['pais'] ?? '');
$dia = $_POST['dia'] ?? '';
$mes = $_POST['mes'] ?? '';
$ano = $_POST['ano'] ?? '';

// Formatear la fecha (YYYY-MM-DD)
$fecha_nacimiento = "$ano-$mes-$dia";

// ==========================================
// VALIDACIONES BÁSICAS
// ==========================================

// 1. Comprobar campos vacíos
if (empty($telefono) || empty($pais) || empty($dia) || empty($mes) || empty($ano) || empty($apellidos)) {
    header("Location: ../telefono_google.php?error=" . urlencode("Todos los campos son obligatorios."));
    exit();
}

// 2. Comprobar que la fecha es real (ej. evitar 31 de Febrero)
if (!checkdate((int)$mes, (int)$dia, (int)$ano)) {
    header("Location: ../telefono_google.php?error=" . urlencode("La fecha de nacimiento no es válida."));
    exit();
}

// 3. Comprobar formato del teléfono
if (!preg_match('/^[0-9+\s()-]{7,20}$/', $telefono)) {
    header("Location: ../telefono_google.php?error=" . urlencode("El número de teléfono no es válido."));
    exit();
}

// Generamos contraseña aleatoria porque entra con Google
$password_aleatoria = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT);

// ==========================================
// INSERTAR USUARIO
// ==========================================
$insertar_user = $conexion->prepare("
    INSERT INTO usuarios (nombre, apellidos, pais, fecha_nacimiento, foto_perfil)
    VALUES (?, ?, ?, ?, ?)
");
$insertar_user->bind_param("sssss", $nombre, $apellidos, $pais, $fecha_nacimiento, $foto_perfil);

if (!$insertar_user->execute()) {
    die("Error al registrar usuario: " . $conexion->error);
}
$nuevo_id = $insertar_user->insert_id;
$insertar_user->close();

// ==========================================
// INSERTAR CREDENCIALES
// ==========================================
$insertar_cred = $conexion->prepare("
    INSERT INTO usuarios_credenciales (usuario_id, telefono, email, password, metodo_registro)
    VALUES (?, ?, ?, ?, 'google')
");
$insertar_cred->bind_param("isss", $nuevo_id, $telefono, $email, $password_aleatoria);

if (!$insertar_cred->execute()) {
    die("Error al guardar credenciales: " . $conexion->error);
}
$insertar_cred->close();

// ==========================================
// LOGIN AUTOMÁTICO Y RECUÉRDAME
// ==========================================
session_regenerate_id(true);
$_SESSION['usuario_id'] = $nuevo_id;
$_SESSION['usuario_nombre'] = $nombre;

$token_cookie = bin2hex(random_bytes(32));
$token_hasheado = hash('sha256', $token_cookie);

$update_token = $conexion->prepare("UPDATE usuarios_credenciales SET remember_token = ? WHERE usuario_id = ?");
$update_token->bind_param("si", $token_hasheado, $nuevo_id);
$update_token->execute();
$update_token->close();

setcookie("recuerdame_token", $token_cookie, [
    'expires' => time() + (86400 * 30),
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Borramos la sesión de registro temporal
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
// REDIRECCIÓN FINAL
// ==========================================
header("Location: ../homepage.php?registro=google_exito");
exit();
?>