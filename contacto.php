<?php
require_once 'php/logicaNegocio/cargar_usuario_header.php';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Contacto - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/contacto.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="js/retardo_cambio_pagina.js" defer></script>
    <script src="js/contacto.js" defer></script>
    <script src="js/abrir_popup_header.js" defer></script>
</head>
<body>
<main>
    <div class="contacto-container">

        <!-- Botón volver atrás -->
        <a href="javascript:history.back()" class="btn-volver-atras" title="Volver a la página anterior">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </a>

        <!-- Logo -->
        <div class="logo-container">
            <a href="homepage.php"><img src="src/logo/logo_con_inifito.png" alt="Imagen del logo"></a>
        </div>

        <p class="contacto-descripcion">
            ¿Tienes alguna duda<span class="responsive-texto"> o quieres ponerte en contacto con nosotros</span>?
            Escríbenos y te responderemos lo antes posible.
        </p>

        <form action="php/procesar_contacto.php" method="POST" id="form-contacto">
            <div class="contacto-input">
                <img src="src/iconos/cara_mujer.png" alt="Nombre">
                <!-- Se ha añadido id="nombre" -->
                <input type="text" id="nombre" name="nombre" placeholder="Introduce tu nombre" value="<?= htmlspecialchars($nombre_usuario) ?>">
            </div>

            <div class="contacto-input">
                <img src="src/iconos/email_login.png" alt="Email">
                <!-- Se ha añadido id="email" -->
                <input type="email" id="email" name="email" placeholder="Introduce tu email" value="<?= htmlspecialchars($email_usuario) ?>">
            </div>

            <div class="contacto-input">
                <img src="src/iconos/asunto.png" alt="Asunto" class="icono-asunto">
                <!-- Se ha añadido id="asunto" -->
                <input type="text" id="asunto" name="asunto" placeholder="Asunto">
            </div>

            <!-- Se ha añadido id="mensaje" -->
            <textarea id="mensaje" name="mensaje" placeholder="Escribe tu mensaje..."></textarea>

            <!-- CAJA DE MENSAJES DE ERROR/ÉXITO -->
            <div id="mensaje-error" class="mensaje-error"></div>

            <button type="submit">
                ENVIAR MENSAJE
            </button>
        </form>
    </div>
</main>
</body>
</html>