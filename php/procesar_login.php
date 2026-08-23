<?php
session_start();

define('ACCESO_PERMITIDO', true);

require_once 'conexion.php';
require_once 'logicaNegocio/loginUsuario.php';

// Redirige al formulario de login mostrando un mensaje de error
function volverConError($mensaje) {
    header("Location: ../login.php?error=" . urlencode($mensaje));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recogemos y limpiamos los datos
    $email    = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validación de seguridad
    if (empty($email) || empty($password)) {
        volverConError("Por favor, rellena todos los campos.");
    }

    // Comprobamos el login
    $resultado = verificarLogin($conexion, $email, $password);

    if ($resultado['exito']) {

        // Evita ataques de Session Fixation
        session_regenerate_id(true);

        // Guardamos los datos del usuario en la sesión
        $_SESSION['usuario_id'] = $resultado['id'];
        $_SESSION['usuario_nombre'] = $resultado['nombre'];

        // --- AÑADIMOS ESTA LÍNEA PARA EL ADMIN ---
        $_SESSION['es_admin'] = $resultado['es_admin'];

        // --- LÓGICA DE RECUÉRDAME ---
        if (isset($_POST['remember'])) {

            $token = bin2hex(random_bytes(32));

            // Guardamos el hash del token en la base de datos
            $token_hasheado = hash('sha256', $token);
            guardarTokenRecuerdame($conexion, $resultado['id'], $token_hasheado);

            // Creamos la cookie segura
            setcookie("recuerdame_token", $token, [
                'expires'  => time() + (86400 * 30), // 30 días
                'path'     => '/',
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }

        // Redirección dependiendo del rol
        if ($_SESSION['es_admin'] == 1) {
            header("Location: ../homepage_admin.php");
        } else {
            header("Location: ../homepage.php");
        }
        exit();

    } else {

        // Login incorrecto
        volverConError($resultado['mensaje']);
    }

} else {

    // Si intentan acceder directamente al archivo
    header("Location: ../login.php");
    exit();
}
?>