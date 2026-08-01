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
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
                .container { background-color: #ffffff; padding: 20px; border-radius: 8px; max-width: 600px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                .footer { font-size: 12px; color: #777777; margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class="container">
                <h2>¡Hola ' . htmlspecialchars($nombre) . '!</h2>
                <p>Gracias por registrarte en <b>Resignificarte</b>.</p>
                <p>Estamos muy felices de tenerte con nosotros.</p>
                <br>
                <p>Un abrazo,</p>
                <p>El equipo de Resignificarte</p>
                <div class="footer">Este es un mensaje automático, por favor no respondas a este correo.</div>
            </div>
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