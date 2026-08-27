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
    <title>Inicio de sesión - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="js/retardo_cambio_pagina.js" defer></script>
    <script src="js/login.js" defer></script>
</head>
<body>

<main>
    <div class="login-container">

        <!-- Botón volver atrás -->
        <a href="javascript:history.back()" class="btn-volver-atras" title="Volver a la página anterior">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </a>

        <!-- INICIO DEL FORMULARIO DE LOGIN -->
        <form action="php/procesar_login.php" method="POST" id="form-login">
            <!-- Logo -->
            <div class="logo-container">
                <a href="homepage.php"><img src="src/logo/logo_con_inifito.png" alt="Imagen del logo"></a>
            </div>

            <!-- Zona de inputs -->
            <div class="placeholders">
                <!-- Grupo Email -->
                <div class="input-group">
                    <img src="src/iconos/email_login.png" alt="Icono email" class="input-icon">
                    <!-- Añadido name="email" -->
                    <input type="email" placeholder="Introduce tu email" id="email" name="email">
                </div>

                <!-- Grupo Contraseña -->
                <div class="input-group password-group">
                    <img src="src/iconos/candado_login.png" alt="Icono contraseña" class="input-icon">
                    <input type="password" placeholder="Introduce tu contraseña" id="password" name="password">
                    <img src="src/iconos/ojo-cerrado.png" alt="Ver contraseña" class="toggle-password" data-target="password">
                </div>
            </div>

            <!-- Mensajes de error o exito -->
            <div id="mensaje-error" class="mensaje-error"></div>

            <!-- Zona de recordar usuario y contraseña olvidada -->
            <div class="aceptar-terminos-y-contraseña-olvidada">
                <label class="checkbox-label">
                    <input type="checkbox" id="remember-me" name="remember">
                    Recuérdame
                </label>
                <a href="contrasena_olvidada.php" class="forgot-link">¿Contraseña olvidada?</a>
            </div>

            <!-- Botón -->
            <button type="submit" class="btn-login">INICIA SESIÓN</button>
        </form>
        <!-- FIN DEL FORMULARIO -->

        <!-- Separador con los dos spans -->
        <div class="separador">
            <span></span>
            <p>o</p>
            <span></span>
        </div>

        <!-- Botón de Google -->
        <a href="php/login_google.php" style="text-decoration: none; width: 100%;">
            <button type="button" class="btn-google">
                <img src="src/iconos/google.png" alt="Icono de Google">
                Continuar con Google
            </button>
        </a>

        <!-- Enlace para registrarse -->
        <div class="registro-link">
            ¿No tienes cuenta? <a href="registro.php">Regístrate</a>
        </div>
    </div>
</main>

</body>
</html>