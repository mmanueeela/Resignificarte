<?php
require_once 'php/logicaNegocio/redireccion_logeado.php';
require_once 'php/logicaNegocio/header_usuario.php';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Inicio - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/homepage.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="js/retardo_cambio_pagina.js" defer></script>
    <script src="js/homepage.js" defer></script>
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
            <li><a href="#">¿QUÉ ES RESIGNIFIC<span>ARTE</span>?</a></li>
            <li><a href="obras.php">OBRAS</a></li>
            <li><a href="contacto.php">CONTACTO</a></li>
        </ul>
    </nav>

    <!-- Área de usuario -->
    <a href="login.php" class="area-usuario">
        <span class="enlace-acceder">Acceder</span>
        <img src="src/iconos/usuario.png" alt="Icono de usuario">
    </a>
</header>

<main>
    <img src="src/images/img_fondo.png" alt="imagen de fondo">
    <div class="info_principal">
        <h2>El arte es que jamás le habla a <br>dos personas de la misma forma. <br><span>Y a ti, ¿qué te dice?</span></h2>
        <a href="obras.php">Descubre las obras</a>
    </div>

    <!-- Flecha para bajar -->
    <a href="#siguiente-seccion" class="btn-bajar" title="Bajar">
        <img src="src/iconos/down_arrow.png" alt="Bajar">
    </a>

    <div class="hola" id="siguiente-seccion">
        asjdfjlajslfñ
    </div>
</main>

<a href="#" class="btn-volver-arriba" title="Volver arriba">
    <img src="src/iconos/up_arrow.png" alt="Subir">
</a>

<footer>
    <p>&copy; Todos los derechos reservados. 2026</p>
</footer>

</body>
</html>