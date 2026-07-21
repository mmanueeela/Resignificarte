<?php
// Arrancamos el motor de sesiones de PHP (¡Imprescindible para mantener al usuario logueado!)
session_start();

define('ACCESO_PERMITIDO', true);

require_once 'conexion.php';
require_once 'logicaNegocio/loginUsuario.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email    = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validar que no estén vacíos
    if (empty($email) || empty($password)) {
        header("Location: ../login.html?error=Por favor, rellena todos los campos.");
        exit();
    }

    // Llamamos a la lógica
    $resultado = verificarLogin($conexion, $email, $password);

    if ($resultado['exito']) {
        // LOGIN CORRECTO: Guardamos sus datos en la sesión
        $_SESSION['usuario_id']     = $resultado['id'];
        $_SESSION['usuario_nombre'] = $resultado['nombre'];

        // --- LÓGICA DE RECUÉRDAME ---
        if (isset($_POST['remember'])) {
            // Generamos un token seguro y aleatorio de 64 caracteres
            $token = bin2hex(random_bytes(32));

            // Lo guardamos en la base de datos
            guardarTokenRecuerdame($conexion, $resultado['id'], $token);

            // Creamos la cookie en el navegador del usuario (Dura 30 días)
            setcookie("recuerdame_token", $token, time() + (86400 * 30), "/");
        }

        // Lo mandamos a la pantalla de inicio
        header("Location: ../homepage_usuario_registrado.php");
        exit();
    } else {
        // LOGIN FALLIDO: Lo devolvemos al login pasándole el error por la URL
        $error_codificado = urlencode($resultado['mensaje']);
        header("Location: ../login.html?error=" . $error_codificado);
        exit();
    }

} else {
    header("Location: ../login.html");
    exit();
}
?>