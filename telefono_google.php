<?php
session_start();

// Si no viene de un registro con Google,
// no permitimos acceder a esta página.
if (!isset($_SESSION['registro_google'])) {
    header("Location: login.php");
    exit();
}

$datos_google = $_SESSION['registro_google'];

$nombre = htmlspecialchars($datos_google['nombre']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completa tu registro - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
<main>
    <div class="login-container">
        <!-- Logo -->
        <div class="logo-container">
            <a href="homepage.php">
                <img src="src/logo/logo_con_inifito.png" alt="Imagen del logo">
            </a>
        </div>
        <!-- Título -->
        <h2>¡Bienvenido, <?= $nombre ?>!</h2>
        <p>
            Para terminar tu registro necesitamos tu número de teléfono.
        </p>
        <!-- Formulario -->
        <form action="php/procesar_registro_google.php" method="POST" id="form-telefono-google">
            <div class="input-group">
                <img src="src/iconos/telefono.png" alt="Icono teléfono" class="input-icon">
                <input type="tel" id="telefono" name="telefono" placeholder="Teléfono" autocomplete="tel" required maxlength="20">
            </div>
            <div id="mensaje-error" class="mensaje-error"></div>
            <button type="submit" class="btn-login">
                CONTINUAR
            </button>
        </form>
        <div class="registro-link">
            ¿Ya tienes cuenta?
            <a href="login.php">
                Inicia sesión
            </a>
        </div>
    </div>
</main>
</body>
</html>