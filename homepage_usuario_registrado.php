<?php
// 1. Incluimos el archivo que verifica la sesión y la cookie
require_once 'php/verificar_sesion.php';

// 2. Si después de verificar, no hay ID de usuario en la sesión, ¡es un intruso!
// Lo redirigimos inmediatamente al login.
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Inicio - Resignificarte</title>
    <!-- He añadido un poco de CSS en línea temporal para que se vea bonito el mensaje -->
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(to bottom, #200B30, #a486c9);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .bienvenida-container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            padding: 40px 60px;
            border-radius: 25px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }
        .btn-logout {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #523479;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .btn-logout:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<div class="bienvenida-container">
    <!-- Usamos htmlspecialchars por seguridad, para limpiar caracteres raros -->
    <h1>¡Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>! 💜</h1>
    <p>Has iniciado sesión correctamente y el sistema te reconoce.</p>

    <!-- Enlace para probar a cerrar sesión -->
    <a href="php/cerrar_sesion.php" class="btn-logout">Cerrar sesión</a>
</div>

</body>
</html>