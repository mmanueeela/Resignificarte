<?php
session_start();
require_once 'conexion.php';

// ==========================================
// 1. COMPROBAR QUE VIENE DE GOOGLE Y ES POST
// ==========================================
if (
    !isset($_SESSION['registro_google']) ||
    $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    header("Location: ../login.php");
    exit();
}

$datos_google = $_SESSION['registro_google'];

// ==========================================
// 2. IDENTIFICAR SI ES USUARIO NUEVO O EXISTENTE
// ==========================================
$es_existente = isset($datos_google['usuario_existente']) && $datos_google['usuario_existente'] === true;
$id_usuario_final = null;

// ==========================================
// 3. RECOGER DATOS BÁSICOS DE GOOGLE
// ==========================================
$email = trim($datos_google['email'] ?? '');
$nombre = trim($datos_google['nombre'] ?? '');
$foto_perfil = $datos_google['foto_perfil'] ?? '';

// ==========================================
// 4. COMPROBAR QUÉ DATOS FALTABAN Y ASIGNAR
// ==========================================
$falta_apellidos = empty(trim($datos_google['apellidos'] ?? ''));
$falta_telefono  = empty(trim($datos_google['telefono'] ?? ''));
$falta_pais      = empty(trim($datos_google['pais'] ?? ''));
$falta_fecha     = empty(trim($datos_google['fecha_nacimiento'] ?? ''));

$apellidos = $falta_apellidos ? trim($_POST['apellidos'] ?? '') : trim($datos_google['apellidos'] ?? '');
$telefono  = $falta_telefono  ? trim($_POST['telefono'] ?? '')  : trim($datos_google['telefono'] ?? '');
$pais      = $falta_pais      ? trim($_POST['pais'] ?? '')      : trim($datos_google['pais'] ?? '');

$fecha_nacimiento = trim($datos_google['fecha_nacimiento'] ?? '');

if ($falta_fecha) {
    $dia = $_POST['dia'] ?? '';
    $mes = $_POST['mes'] ?? '';
    $ano = $_POST['ano'] ?? '';
} else {
    $fecha_partes = explode('-', $fecha_nacimiento);
    $ano = $fecha_partes[0] ?? '';
    $mes = $fecha_partes[1] ?? '';
    $dia = $fecha_partes[2] ?? '';
}

// ==========================================
// 5. VALIDACIONES DE SEGURIDAD
// ==========================================
if (empty($email) || empty($nombre)) {
    header("Location: ../login.php?error=" . urlencode("No se han podido obtener los datos de Google."));
    exit();
}

if ($falta_apellidos && empty($apellidos)) {
    header("Location: ../telefono_google.php?error=" . urlencode("Los apellidos son obligatorios."));
    exit();
}

if ($falta_telefono) {
    if (empty($telefono)) {
        header("Location: ../telefono_google.php?error=" . urlencode("El teléfono es obligatorio."));
        exit();
    }
    if (!preg_match('/^[0-9+\s()-]{7,20}$/', $telefono)) {
        header("Location: ../telefono_google.php?error=" . urlencode("El número de teléfono no es válido."));
        exit();
    }
}

if ($falta_pais && empty($pais)) {
    header("Location: ../telefono_google.php?error=" . urlencode("El país es obligatorio."));
    exit();
}

if ($falta_fecha) {
    if (empty($dia) || empty($mes) || empty($ano)) {
        header("Location: ../telefono_google.php?error=" . urlencode("La fecha de nacimiento es obligatoria."));
        exit();
    }
    if (!checkdate((int)$mes, (int)$dia, (int)$ano)) {
        header("Location: ../telefono_google.php?error=" . urlencode("La fecha de nacimiento no es válida."));
        exit();
    }
    $fecha_nacimiento = sprintf('%04d-%02d-%02d', (int)$ano, (int)$mes, (int)$dia);
}

// ==========================================
// 6. BIFURCACIÓN: ¿UPDATE O INSERT?
// ==========================================
if ($es_existente) {

    // --- A) EL USUARIO YA EXISTE -> UPDATE ---
    $id_usuario_final = $datos_google['usuario_existente_id'];

    $update_user = $conexion->prepare("
        UPDATE usuarios 
        SET nombre = ?, apellidos = ?, pais = ?, fecha_nacimiento = ?, foto_perfil = ?
        WHERE id = ?
    ");
    $update_user->bind_param("sssssi", $nombre, $apellidos, $pais, $fecha_nacimiento, $foto_perfil, $id_usuario_final);
    $update_user->execute();
    $update_user->close();

    $update_cred = $conexion->prepare("
        UPDATE usuarios_credenciales 
        SET telefono = ?
        WHERE usuario_id = ?
    ");
    $update_cred->bind_param("si", $telefono, $id_usuario_final);
    $update_cred->execute();
    $update_cred->close();

} else {

    // --- B) EL USUARIO ES NUEVO -> INSERT ---
    $password_aleatoria = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT);

    $insertar_user = $conexion->prepare("
        INSERT INTO usuarios (nombre, apellidos, pais, fecha_nacimiento, foto_perfil)
        VALUES (?, ?, ?, ?, ?)
    ");
    $insertar_user->bind_param("sssss", $nombre, $apellidos, $pais, $fecha_nacimiento, $foto_perfil);
    $insertar_user->execute();
    $id_usuario_final = $insertar_user->insert_id;
    $insertar_user->close();

    $insertar_cred = $conexion->prepare("
        INSERT INTO usuarios_credenciales (usuario_id, telefono, email, password, metodo_registro)
        VALUES (?, ?, ?, ?, 'google')
    ");
    $insertar_cred->bind_param("isss", $id_usuario_final, $telefono, $email, $password_aleatoria);
    $insertar_cred->execute();
    $insertar_cred->close();
}

// ==========================================
// 7. LOGIN AUTOMÁTICO Y RECUÉRDAME
// ==========================================
session_regenerate_id(true);

$_SESSION['usuario_id'] = $id_usuario_final;
$_SESSION['usuario_nombre'] = $nombre;

$token_cookie = bin2hex(random_bytes(32));
$token_hasheado = hash('sha256', $token_cookie);

$update_token = $conexion->prepare("UPDATE usuarios_credenciales SET remember_token = ? WHERE usuario_id = ?");
$update_token->bind_param("si", $token_hasheado, $id_usuario_final);
$update_token->execute();
$update_token->close();

setcookie("recuerdame_token", $token_cookie, [
    'expires'  => time() + (86400 * 30),
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

unset($_SESSION['registro_google']);

// ==========================================
// 8. CORREO DE BIENVENIDA (SOLO NUEVOS)
// ==========================================
if (!$es_existente) {
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
                                    Gracias por registrarte en <strong style="color:#6f42c1;">Resignificarte</strong> usando tu cuenta de Google.
                                </p>
                                <p style="margin:0 0 25px 0; font-size:16px; line-height:1.7; color:#555555;">
                                    Estamos muy felices de tenerte con nosotros.
                                </p>
                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td style="border-top:1px solid #eeeeee; font-size:1px; line-height:1px;">&nbsp;</td>
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

    mail($email, $asunto, $mensaje, $cabeceras, "-f mzazzar@epsg.upv.es");
}

// ==========================================
// 9. REDIRECCIÓN FINAL
// ==========================================
header("Location: ../homepage.php?login=exito");
exit();
?>