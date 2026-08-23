<?php
require_once 'php/logicaNegocio/cargar_usuario_header.php';
require_once 'php/conexion.php';

// Detectar en qué página estamos actualmente
$pagina_actual = basename($_SERVER['PHP_SELF']);

// Obtener todas las experiencias (Artistas) y su primer cuadro (portada)
$query = "
    SELECT a.id as artista_id, a.nombre, a.pais, 
           (SELECT imagen FROM obras o WHERE o.artista_id = a.id ORDER BY id ASC LIMIT 1) as imagen_portada
    FROM artistas a
";
$resultado = $conexion->query($query);
$experiencias = $resultado->fetch_all(MYSQLI_ASSOC);
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
    <script src="js/menu_hamburguesa.js" defer></script>
    <script src="js/abrir_popup_header.js" defer></script>
</head>
<body>
<header>
    <!-- Logo dinámico -->
    <div class="logo-container">
        <a href="<?= (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1) ? 'homepage_administrador.php' : 'homepage.php' ?>">
            <img src="src/logo/logo_con_inifito.png" alt="Imagen del logo">
        </a>
    </div>

    <!-- Menú principal (Escritorio) -->
    <nav class="menu-navegacion">
        <ul>
            <li><a href="obras.php" class="<?= ($pagina_actual == 'obras.php' || $pagina_actual == 'Obras_Artista.php') ? 'activo' : '' ?>">OBRAS</a></li>

            <?php if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1): ?>
                <li><a href="contacto.php" class="<?= ($pagina_actual == 'contacto.php') ? 'activo' : '' ?>">CONTACTO</a></li>
            <?php endif; ?>

            <?php if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1): ?>
                <li><a href="homepage_administrador.php" class="<?= ($pagina_actual == 'homepage_administrador.php') ? 'activo' : '' ?>">PANEL ADMIN</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Área de usuario -->
    <?php if ($usuario_logeado): ?>
        <div class="area-usuario-dropdown">
            <button class="area-usuario area-usuario-btn" id="btn-usuario">
            <span class="enlace-acceder">
                <?= htmlspecialchars($nombre_usuario) ?>
            </span>
                <img src="<?= htmlspecialchars($ruta_foto) ?>" alt="Usuario" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
            </button>
            <div class="dropdown-menu" id="dropdown-usuario">
                <a href="perfil_usuario.php" class="dropdown-item">Ver perfil</a>
                <a href="php/cerrar_sesion.php" class="dropdown-item cerrar-sesion">Cerrar Sesión</a>
            </div>
        </div>
    <?php else: ?>
        <a href="login.php" class="area-usuario">
            <span class="enlace-acceder">Acceder</span>
            <img src="src/iconos/usuario.png" alt="Icono de usuario">
        </a>
    <?php endif; ?>

    <!-- Menú hamburguesa (Móvil) -->
    <button id="btn-menu">
        <div></div><div></div><div></div>
    </button>

    <!-- Menú Desplegable (Móvil) -->
    <nav class="menu-navegacion-mobile" id="menu-mobile">
        <ul>
            <li><a href="obras.php" class="<?= ($pagina_actual == 'obras.php' || $pagina_actual == 'Obras_Artista.php') ? 'activo' : '' ?>">OBRAS</a></li>

            <?php if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1): ?>
                <li><a href="contacto.php" class="<?= ($pagina_actual == 'contacto.php') ? 'activo' : '' ?>">CONTACTO</a></li>
            <?php endif; ?>

            <?php if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1): ?>
                <li><a href="homepage_administrador.php" class="<?= ($pagina_actual == 'homepage_administrador.php') ? 'activo' : '' ?>">PANEL ADMIN</a></li>
            <?php endif; ?>

            <hr class="separador-movil">
            <?php if ($usuario_logeado): ?>
                <li>
                    <a href="perfil_usuario.php">
                        <img src="<?= htmlspecialchars($ruta_foto) ?>" alt="Usuario" style="width: 25px; height: 25px; border-radius: 50%; object-fit: cover; vertical-align: middle; margin-right: 10px;">
                        Mi perfil
                    </a>
                </li>
                <li><a href="php/cerrar_sesion.php" style="color: #ff8787;">Cerrar Sesión</a></li>
            <?php else: ?>
                <li>
                    <a href="login.php">
                        <img src="src/iconos/usuario.png" alt="Icono de usuario" style="width: 20px; vertical-align: middle; margin-right: 10px;">
                        Acceder
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main>
    <div class="contenido-inicial">
        <h1>OBRAS</h1>
        <div class="buscador">
            <input type="text" placeholder="Busca el artista...">
            <button type="button" class="boton-busqueda" aria-label="Buscar">
                <img src="src/iconos/lupa.png" alt="">
            </button>
        </div>
    </div>

    <!-- EXPERIENCIAS GENERADAS DINÁMICAMENTE -->
    <?php foreach ($experiencias as $index => $exp):
        $num_experiencia = $index + 1;

        // LÓGICA DE LA IMAGEN: Si es Antonio Nieto (ID 1), foto original. Si no, su primer cuadro.
        if ($exp['artista_id'] == 1) {
            $img_perfil = 'src/images/img_antonio_nieto.jpg';
        } else {
            $img_perfil = !empty($exp['imagen_portada']) ? $exp['imagen_portada'] : 'src/iconos/usuario.png';
        }
        ?>
        <section class="experiencia-1" style="margin-bottom: 20px;">
            <div class="contenedor-imagen-mas-texto">
                <img src="<?= htmlspecialchars($img_perfil) ?>" alt="<?= htmlspecialchars($exp['nombre']) ?>">
                <div class="contenedor-texto">
                    <h2>Experiencia #<?= $num_experiencia ?></h2>
                    <p>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; color: white;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        Obras de <?= htmlspecialchars($exp['nombre']) ?>, <?= htmlspecialchars($exp['pais']) ?>
                    </p>
                </div>
            </div>
            <!-- Enviamos el ID del artista por GET -->
            <div class="contenedor-botones">
                <a href="Obras_Artista.php?id=<?= $exp['artista_id'] ?>" class="btn-ver-obras">Ver las obras</a>

                <?php if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1): ?>
                    <a href="editar_experiencia.php?id=<?= $exp['artista_id'] ?>" class="btn-editar-obra">Editar</a>

                    <!-- Botón para Eliminar la Experiencia entera -->
                    <form action="php/eliminar_experiencia.php" method="POST" onsubmit="return confirm('¿Seguro que quieres borrar a este artista y TODAS sus obras? Se borrarán también los comentarios asociados.');" style="margin:0;">
                        <input type="hidden" name="id_artista" value="<?= $exp['artista_id'] ?>">
                        <button type="submit" class="btn-eliminar-experiencia">Eliminar</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>

</main>
<footer>
    <p>
        &copy; Todos los derechos reservados. 2026
    </p>
</footer>
</body>
</html>