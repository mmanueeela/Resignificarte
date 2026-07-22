<?php
session_start();

define('ACCESO_PERMITIDO', true);

require_once 'conexion.php';
require_once 'logicaNegocio/loginUsuario.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email    = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validar que no estén vacíos
    if (empty($email) || empty($password)) {
        header("Location: ../login.php?error=Por favor, rellena todos los campos.");
        exit();
    }

    // Llamamos a la lógica
    $resultado = verificarLogin($conexion, $email, $password);

    if ($resultado['exito']) {

        // Genera un nuevo ID de sesión seguro y borra el antiguo
        session_regenerate_id(true);
        // -------------------------------------------------

        // LOGIN CORRECTO: Guardamos sus datos en la sesión
        $_SESSION['usuario_id']     = $resultado['id'];
        $_SESSION['usuario_nombre'] = $resultado['nombre'];

        // --- LÓGICA DE RECUÉRDAME ---
        if (isset($_POST['remember'])) {
            $token = bin2hex(random_bytes(32));

            // Guardamos el HASH en la base de datos, NO el token plano
            $token_hasheado = hash('sha256', $token);
            guardarTokenRecuerdame($conexion, $resultado['id'], $token_hasheado);

            // Creamos la cookie segura en el navegador con el token plano
            setcookie("recuerdame_token", $token, [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'secure' => true,     // Solo HTTPS
                'httponly' => true,   // Inaccesible desde JavaScript (Evita XSS)
                'samesite' => 'Lax'   // Protege contra ataques CSRF
            ]);
        }

        // Lo mandamos a la pantalla de inicio
        header("Location: ../homepage_usuario_registrado.php");
        exit();
    } else {
        // LOGIN FALLIDO: Lo devolvemos al login pasándole el error por la URL
        $error_codificado = urlencode($resultado['mensaje']);
        header("Location: ../login.php?error=" . $error_codificado);
        exit();
    }

} else {
    header("Location: ../login.php");
    exit();
}
?>