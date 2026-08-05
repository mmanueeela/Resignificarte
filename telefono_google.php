<?php

session_start();

// Comprobamos que el usuario realmente viene del registro con Google
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

    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0,
                   maximum-scale=1.0, minimum-scale=1.0">

    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Completar registro - Resignificarte</title>

    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/telefono_google.css">

    <link rel="icon" href="favicon.ico" type="image/x-icon">

</head>

<body>

<main>

    <div class="forgot-container">

        <!-- Logo -->
        <div class="logo-container">

            <a href="homepage.php">

                <img
                        src="src/logo/logo_con_inifinito.png"
                        alt="Logo de Resignificarte"
                >

            </a>

        </div>


        <!-- Título -->
        <h2>
            ¡Bienvenido, <?= $nombre ?>!
        </h2>


        <!-- Explicación -->
        <p>
            Para terminar tu registro necesitamos tu número de teléfono.
        </p>


        <!-- Formulario -->
        <form
                action="php/procesar_registro_google.php"
                method="POST"
                id="form-telefono-google"
                novalidate
        >

            <div class="input-group">

                <img
                        src="src/iconos/telefono.png"
                        alt="Icono teléfono"
                        class="input-icon"
                >

                <input
                        type="tel"
                        id="telefono"
                        name="telefono"
                        placeholder="Teléfono"
                        autocomplete="tel"
                        maxlength="20"
                        required
                >

            </div>


            <!-- Mensaje de error -->
            <div
                    id="mensaje-error"
                    class="message"
            ></div>


            <!-- Botón -->
            <button
                    type="submit"
                    class="btn-login"
            >
                CONTINUAR
            </button>

        </form>


        <!-- Volver al login -->
        <div class="registro-link">

            ¿Ya tienes cuenta?<?php
            session_start();

            // Comprobamos que el usuario realmente viene del registro con Google
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
                <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Completar registro - Resignificarte</title>
                <link rel="stylesheet" href="css/estilos_comunes.css">
                <link rel="stylesheet" href="css/telefono_google.css">
                <link rel="icon" href="favicon.ico" type="image/x-icon">
            </head>
            <body>
            <main>
                <div class="forgot-container">
                    <!-- Logo -->
                    <div class="logo-container">
                        <a href="homepage.php">
                            <img src="src/logo/logo_con_inifito.png" alt="Logo de Resignificarte">
                        </a>
                    </div>
                    <!-- Título -->
                    <h2>
                        ¡Bienvenido, <?= $nombre ?>!
                    </h2>
                    <!-- Explicación -->
                    <p>
                        Para terminar tu registro necesitamos tu número de teléfono.
                    </p>
                    <!-- Formulario -->
                    <form action="php/procesar_registro_google.php" method="POST" id="form-telefono-google" novalidate>
                        <div class="input-group">
                            <img src="src/iconos/telefono.png" alt="Icono teléfono" class="input-icon">
                            <input type="tel" id="telefono" name="telefono" placeholder="Teléfono" autocomplete="tel" maxlength="20" required>
                        </div>
                        <!-- Mensaje de error -->
                        <div id="mensaje-error" class="message"></div>
                        <!-- Botón -->
                        <button type="submit" class="btn-login">
                            CONTINUAR
                        </button>
                    </form>
                    <!-- Volver al login -->
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

            <a href="login.php">
                Inicia sesión
            </a>

        </div>

    </div>

</main>

</body>

</html>