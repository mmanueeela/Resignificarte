<?php


// 1. Incluimos el archivo que verifica la sesión y la cookie
require_once 'php/verificar_sesion.php';

// 2. Si después de verificar, no hay ID de usuario en la sesión, ¡es un intruso!
// Lo redirigimos inmediatamente al login.
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$nombre_usuario = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Usuario';


?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/homepage.css">
    <link rel="stylesheet" href="css/homepage_usuario_registrado.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="js/retardo_cambio_pagina.js" defer></script>
    <script src="js/abrir_popup_homepage_usuario_registrado.js"></script>
</head>
<body>

<header>
    <!-- Logo -->
    <div class="logo-container">
        <a href="#"><img src="src/logo/logo_con_inifito.png" alt="Imagen del logo"></a>
    </div>

    <!-- Menú principal -->
    <nav class="menu-navegacion">
        <ul>
            <li><a href="#">Servicios</a></li>
            <li><a href="#">Nosotros</a></li>
            <li><a href="#">Contacto</a></li>
        </ul>
    </nav>

    <!-- Área de usuario con Dropdown -->
    <div class="area-usuario-dropdown">
        <!-- El botón que activa el menú -->
        <button class="area-usuario-btn" id="btn-usuario">
            <span class="enlace-acceder"><?php echo htmlspecialchars($nombre_usuario); ?></span>
            <img src="src/iconos/usuario.png" alt="Icono de usuario">
        </button>

        <!-- El pequeño popup flotante -->
        <div class="dropdown-menu" id="dropdown-usuario">
            <a href="perfil_usuario.php" class="dropdown-item">Ver perfil</a>
            <a href="php/cerrar_sesion.php" class="dropdown-item cerrar-sesion">Cerrar Sesión</a>
        </div>
    </div>
</header>

<main>

</main>

<footer>
    <p>Todos los derechos reservados. 2026 &copy;</p>
</footer>

</body>
</html>