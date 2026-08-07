<?php
require_once 'php/logicaNegocio/header_usuario.php';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Obras - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/obras.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="js/retardo_cambio_pagina.js" defer></script>
    <script src="js/obras.js" defer></script>
</head>
<body>
<header>
    <!-- Logo -->
    <div class="logo-container">
        <a href="homepage.php"><img src="src/logo/logo_con_inifito.png" alt="Imagen del logo"></a>
    </div>

    <!-- Menú principal -->
    <nav class="menu-navegacion">
        <ul>
            <li><a href="#">OBRAS</a></li>
            <li><a href="contacto.php">CONTACTO</a></li>
        </ul>
    </nav>

    <!-- Área de usuario -->
    <?php if ($usuario_logeado): ?>
        <div class="area-usuario-dropdown">
            <button class="area-usuario area-usuario-btn" id="btn-usuario">
            <span class="enlace-acceder">
                <?= htmlspecialchars($nombre_usuario) ?>
            </span>
                <img
                        src="<?= htmlspecialchars($ruta_foto) ?>"
                        alt="Usuario"
                        style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;"
                >
            </button>

            <div class="dropdown-menu" id="dropdown-usuario">
                <a href="perfil_usuario.php" class="dropdown-item">
                    Ver perfil
                </a>
                <a href="php/cerrar_sesion.php" class="dropdown-item cerrar-sesion">
                    Cerrar Sesión
                </a>
            </div>
        </div>
    <?php else: ?>
        <a href="login.php" class="area-usuario">
        <span class="enlace-acceder">
            Acceder
        </span>
            <img src="src/iconos/usuario.png" alt="Icono de usuario">
        </a>
    <?php endif; ?>
</header>
<main>
    <!-- PARTE SUPERIOR -->
    <div class="contenido-inicial">
        <h1>OBRAS</h1>
        <div class="buscador">

            <input type="text" placeholder="Busca el artista...">

            <button type="button" class="boton-busqueda" aria-label="Buscar">
                <img src="src/iconos/lupa.png" alt="">
            </button>

        </div>
    </div>
    <!-- EXPERIENCIA ARTISTA -->
    <section class="experiencia-1">
        <div class="contenedor-imagen-mas-texto">
            <img src="src/images/img_antonio_nieto.jpg" alt="Antonio Nieto">
            <div class="contenedor-texto">
                <h2>
                    Experiencia #1
                </h2>
                <p>
                    <img src="src/iconos/bandera_mexico.png" alt="México">
                    Obras de Antonio Nieto, México
                </p>
            </div>
        </div>
        <a href="obras.php">Ver las obras</a>
    </section>
</main>
<footer>
    <p>
        &copy; Todos los derechos reservados. 2026
    </p>
</footer>
</body>
</html>