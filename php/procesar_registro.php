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

        // Lo enviamos directo a su homepage
        header("Location: ../homepage_usuario_registrado.php");
        exit();
    } else {
        // Si falla (ej. correo duplicado), mostramos el error
        echo "Error: " . $resultado['mensaje'];
    }

} else {
    // Si alguien intenta entrar directamente a esta URL sin enviar formulario
    header("Location: ../registro.html");
    exit();
}
?>