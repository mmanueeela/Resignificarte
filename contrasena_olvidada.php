<?php
require_once 'php/logicaNegocio/redireccion_logeado.php'
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Restablecer contraseña - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/contrasena_olvidada.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="js/retardo_cambio_pagina.js" defer></script>
    <script src="js/contrasena_olvidada.js" defer></script>
</head>
<body>

<main>
    <div class="forgot-container">
        <!-- Logo -->
        <div class="logo-container">
            <a href="#"><img src="src/logo/logo_con_inifito.png" alt="Imagen del logo"></a>
        </div>
        <!-- Formulario login -->
        <form id="forgotForm">
            <h2>¿Has olvidado tu contraseña?</h2>
            <p>Introduce tu correo electrónico y te enviaremos las instrucciones para restablecerla.</p>

            <div class="input-group">
                <img src="src/iconos/email_login.png" alt="Icono email" class="input-icon">
                <label for="email">Correo electrónico</label>
                <input type="email" placeholder="Introduce tu email" id="email" name="email">
            </div>

            <p id="responseMessage" class="message"></p>

            <button type="submit" id="submitBtn" class="btn-login">ENVIAR ENLACE</button>
        </form>
    </div>
</main>

</body>
</html>