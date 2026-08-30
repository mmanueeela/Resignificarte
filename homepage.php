<?php
require_once 'php/logicaNegocio/cargar_usuario_header.php';
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
    <script src="js/menu_hamburguesa.js" defer></script>
    <script src="js/abrir_popup_header.js" defer></script>
</head>
<body>

<header>
    <!-- Logo -->
    <div class="logo-container">
        <a href="#"><img src="src/logo/logo_con_inifito.png" alt="Imagen del logo"></a>
    </div>

    <!-- Menú principal (Escritorio) -->
    <nav class="menu-navegacion">
        <ul>
            <li><a href="#">¿QUÉ ES RESIGNIFIC<span>ARTE</span>?</a></li>
            <li><a href="obras.php">OBRAS</a></li>
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

    <!-- Menú hamburguesa (Móvil) -->
    <button id="btn-menu">
        <div></div>
        <div></div>
        <div></div>
    </button>

    <!-- Menú Desplegable (Móvil) -->
    <nav class="menu-navegacion-mobile" id="menu-mobile">
        <ul>
            <li><a href="#">¿QUÉ ES RESIGNIFICARTE?</a></li>
            <li><a href="obras.php">OBRAS</a></li>
            <li><a href="contacto.php">CONTACTO</a></li>

            <!-- Separador visual -->
            <hr class="separador-movil">

            <!-- Área de usuario para móvil -->
            <?php if ($usuario_logeado): ?>
                <li>
                    <a href="perfil_usuario.php">
                        <img src="<?= htmlspecialchars($ruta_foto) ?>" alt="Usuario" style="width: 25px; height: 25px; border-radius: 50%; object-fit: cover; vertical-align: middle; margin-right: 10px;">
                        Mi perfil (<?= htmlspecialchars($nombre_usuario) ?>)
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
    <!-- Etiqueta picture: cambia la imagen automáticamente según la pantalla (de menor a mayor) -->
    <picture class="img-fondo-container">
        <source media="(max-width: 500px)" srcset="src/images/img_fondo_movil.jpeg">
        <source media="(max-width: 600px)" srcset="src/images/img_fondo_medio.jpeg">
        <source media="(max-width: 1000px)" srcset="src/images/img_fondo_tablet.png">
        <img src="src/images/img_fondo.png" alt="imagen de fondo">
    </picture>

    <div class="info_principal">
        <h2>El arte jamás les habla a <br>las personas de la misma forma. <br><span>Y a ti, ¿qué te dice?</span></h2>
        <a href="obras.php">Descubre las obras</a>
    </div>

    <!-- Flecha para bajar -->
    <a href="#siguiente-seccion" class="btn-bajar" title="Bajar">
        <img src="src/iconos/down_arrow.png" alt="Bajar">
    </a>

    <!-- SECCIÓN: ACERCA DE Y EQUIPO -->
    <section id="siguiente-seccion" class="info-proyecto-section">
        <div class="info-layout-grid">

            <!-- Columna Izquierda: Acerca de -->
            <div class="acerca-column glass-card">
                <h2 class="titulo-seccion">Acerca de Resignific<span>ARTE</span></h2>
                <div class="texto-acerca">
                    <p><strong>ResignificARTE</strong> es una propuesta de mediación cultural transmedia centrada en la resignificación de la cultura a través del arte.</p>
                    <p>La experiencia parte de obras representativas de las tradiciones de México del pintor mexicano Antonio Nieto y construye un modelo de mediación que integra narrativa, participación y tecnología como partes de un mismo diseño.</p>
                    <p>El proyecto propone una relación activa con la obra. Quien se acerca a una pieza, además de ser un espectador, la interpreta desde su propio territorio, memoria y experiencia. Esa interpretación se incorpora al ecosistema cultural de ResignificARTE y contribuye a la construcción de un archivo colectivo de miradas sobre la obra y las tradiciones que representa.</p>
                    <p>ResignificARTE surge en el marco del Máster en Comunicación Transmedia y del Grado XXX de la Universitat Politècnica de València (UPV), como resultado de una investigación que articula la mediación cultural, la narrativa transmedia, la participación y el estudio de la memoria colectiva.</p>
                </div>
            </div>

            <!-- Columna Derecha: Equipo Creador apilado -->
            <div class="equipo-column">

                <!-- Miembro 1 -->
                <div class="miembro-card glass-card">
                    <img src="src/images/img_antonio_nieto.jpg" alt="Jessica López Escalera" class="miembro-img-fondo">
                    <div class="etiqueta-nombre">Jessica Lopez Escalera</div>

                    <div class="miembro-info-hover">
                        <h3>Jessica Lopez Escalera</h3>
                        <div class="texto-scroll">
                            <p>Es internacionalista y comunicadora especializada en el desarrollo y posicionamiento de proyectos de comunicación y vinculación de alcance internacional. Durante el Máster en Comunicación Transmedia de la UPV, encontró en el arte un elemento capaz de conectar personas, territorios y memorias. A partir de esta premisa, desarrolló ResignificARTE como un modelo de mediación cultural que utiliza la narrativa transmedia para generar nuevas formas de relación entre la comunicación y el arte. Jessica lidera el diseño conceptual y estratégico del proyecto.</p>
                        </div>
                    </div>
                </div>

                <!-- Miembro 2 -->
                <div class="miembro-card glass-card">
                    <img src="src/images/img_antonio_nieto.jpg" alt="Manuela Zazzaro" class="miembro-img-fondo">
                    <div class="etiqueta-nombre">Manuela Zazzaro</div>

                    <div class="miembro-info-hover">
                        <h3>Manuela Zazzaro</h3>
                        <div class="texto-scroll">
                            <p>Es (descripción de su formación y experiencia). En ResignificARTE desarrolla la dimensión tecnológica del proyecto y participa en la construcción de la experiencia digital y del prototipo del modelo de mediación cultural.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>

<a href="#" class="btn-volver-arriba" title="Volver arriba">
    <img src="src/iconos/up_arrow.png" alt="Subir">
</a>

<footer>
    <p>&copy; Todos los derechos reservados. 2026</p>
</footer>

</body>
</html>