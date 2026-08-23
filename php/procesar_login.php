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

        // GUARDAMOS EL ROL DE ADMIN
        $_SESSION['es_admin'] = $resultado['es_admin'];

        // --- LÓGICA DE RECUÉRDAME ---
        if (isset($_POST['remember'])) {
            $token = bin2hex(random_bytes(32));
            $token_hasheado = hash('sha256', $token);
            guardarTokenRecuerdame($conexion, $resultado['id'], $token_hasheado);

            setcookie("recuerdame_token", $token, [
                'expires'  => time() + (86400 * 30), // 30 días
                'path'     => '/',
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }

        // ==========================================
        // REDIRECCIÓN DEPENDIENDO DEL ROL
        // ==========================================
        if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1) {
            header("Location: ../homepage_administrador.php");
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