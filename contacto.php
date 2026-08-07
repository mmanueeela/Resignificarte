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
    <script src="js/login.js" defer></script>
</head>
<body>
<main>
    <div class="contacto-container">
        <h1>CONTACTO</h1>

        <p class="contacto-descripcion">
            ¿Tienes alguna duda o quieres ponerte en contacto con nosotros?
            Escríbenos y te responderemos lo antes posible.
        </p>

        <form action="php/procesar_contacto.php" method="POST" id="form-contacto">
            <div class="contacto-input">
                <img src="src/iconos/cara_mujer.png" alt="Nombre">
                <input type="text" name="nombre" placeholder="Introduce tu nombre" required>
            </div>

            <div class="contacto-input">
                <img src="src/iconos/email_login.png" alt="Email">
                <input type="email" name="email" placeholder="Introduce tu email" required>
            </div>

            <div class="contacto-input">
                <img src="src/iconos/asunto.png" alt="Asunto" class="icono-asunto">
                <input type="text" name="asunto" placeholder="Asunto" required>
            </div>

            <textarea name="mensaje" placeholder="Escribe tu mensaje..." required></textarea>

            <button type="submit">
                ENVIAR MENSAJE
            </button>
        </form>
    </div>
</main>
</body>
</html>