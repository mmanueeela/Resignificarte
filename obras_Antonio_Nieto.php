<?php
require_once 'php/logicaNegocio/cargar_usuario_header.php';

// Array dinámico de cuadros (Escalable a los cuadros que quieras)
$cuadros = [
    [
        'id' => 1,
        'titulo' => 'Cuadro 1',
        'imagen' => 'src/uploads/cuadros/Antonio_Nieto/cuadro_1.png',
        'audio' => 'src/uploads/audios/Antonio_Nieto/cuadro_1.mp3',
        'transcripcion' => 'Esta es la transcripción detallada del Cuadro 1. En esta obra, Antonio Nieto explora la profundidad de...',
        'comentarios_ejemplo' => ['Impresionante uso del color.', 'Me transmite mucha paz.']
    ],
    [
        'id' => 2,
        'titulo' => 'Cuadro 2',
        'imagen' => 'src/uploads/cuadros/Antonio_Nieto/cuadro_2.png',
        'audio' => 'src/uploads/audios/Antonio_Nieto/cuadro_2.mp3',
        'transcripcion' => 'En el Cuadro 2, el artista rompe con la simetría tradicional para enfocarse en la textura...',
        'comentarios_ejemplo' => ['No paro de mirarlo.', 'Una obra maestra.']
    ],
    [
        'id' => 3,
        'titulo' => 'Cuadro 3',
        'imagen' => 'src/uploads/cuadros/Antonio_Nieto/cuadro_3.png',
        'audio' => 'src/uploads/audios/Antonio_Nieto/cuadro_3.mp3',
        'transcripcion' => 'La culminación de su etapa azul. El Cuadro 3 representa un viaje hacia el interior del ser humano...',
        'comentarios_ejemplo' => ['Muy profundo.', 'Me encanta la historia detrás del lienzo.']
    ]
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Obras Antonio Nieto - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/obras.css">
    <link rel="stylesheet" href="css/obras_general.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="js/retardo_cambio_pagina.js" defer></script>
    <script src="js/menu_hamburguesa.js" defer></script>
    <script src="js/abrir_popup_header.js" defer></script>
    <script src="js/interaccion_obras.js" defer></script>
</head>
<body>
<header>
    <!-- Logo -->
    <div class="logo-container">
        <a href="homepage.php"><img src="src/logo/logo_con_inifito.png" alt="Imagen del logo"></a>
    </div>

    <!-- Menú principal (Escritorio) -->
    <nav class="menu-navegacion">
        <ul>
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
            <li><a href="#">OBRAS</a></li>
            <li><a href="contacto.php">CONTACTO</a></li>
            <hr class="separador-movil">
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

<main class="main-obras-artista">
    <div class="cabecera-artista">
        <a href="obras.php" class="btn-volver-obras">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            Volver a Obras
        </a>
        <h1>Antonio Nieto</h1>
    </div>

    <!-- BUCLE DINÁMICO DE CUADROS -->
    <div class="galeria-cuadros">
        <?php foreach ($cuadros as $index => $cuadro):
            // Si el índice es impar (1, 3, 5...), le añadimos la clase 'inversa' para que el cuadro salga a la derecha
            $clase_inversa = ($index % 2 !== 0) ? 'inversa' : '';
            ?>
            <section class="fila-cuadro <?= $clase_inversa ?>">
                <!-- Lado: Imagen -->
                <div class="col-imagen">
                    <img src="<?= $cuadro['imagen'] ?>" alt="<?= $cuadro['titulo'] ?>" class="imagen-obra">
                </div>

                <!-- Lado: Interacción (Audio, Transcripción, Comentarios) -->
                <div class="col-info">
                    <h2><?= $cuadro['titulo'] ?></h2>

                    <!-- Audio Player Custom -->
                    <div class="reproductor-audio">
                        <audio id="audio-<?= $cuadro['id'] ?>" src="<?= $cuadro['audio'] ?>"></audio>
                        <button class="btn-play-pause" data-audio-id="audio-<?= $cuadro['id'] ?>" aria-label="Reproducir">
                            <!-- Icono Play -->
                            <svg class="icono-play" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                            <!-- Icono Pause (oculto por defecto) -->
                            <svg class="icono-pause" viewBox="0 0 24 24" fill="currentColor" style="display:none;"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                        </button>
                        <div class="onda-audio">Escuchar explicación del autor</div>

                        <!-- Botón Transcripción -->
                        <button class="btn-toggle-transcripcion" data-target="transcripcion-<?= $cuadro['id'] ?>" aria-label="Ver transcripción">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                    </div>

                    <!-- Texto Transcripción (Oculto por defecto) -->
                    <div class="caja-transcripcion" id="transcripcion-<?= $cuadro['id'] ?>">
                        <p><?= $cuadro['transcripcion'] ?></p>
                    </div>

                    <hr class="separador-cuadro">

                    <!-- Zona de Comentar -->
                    <div class="zona-escribir-comentario">
                        <form class="form-comentario" data-cuadro-id="<?= $cuadro['id'] ?>">
                            <input type="text" placeholder="Deja tu comentario sobre esta obra..." required class="input-comentario">
                            <button type="submit" class="btn-enviar-comentario">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            </button>
                        </form>
                    </div>

                    <!-- Zona Ver Comentarios (Bloqueado) -->
                    <div class="zona-ver-comentarios" id="zona-comentarios-<?= $cuadro['id'] ?>">
                        <!-- Este botón está semitransparente hasta que el usuario comenta -->
                        <button class="btn-desplegar-comentarios bloqueado" data-target="lista-comentarios-<?= $cuadro['id'] ?>">
                            <span>Comentarios de la comunidad</span>
                            <svg class="candado" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </button>

                        <div class="lista-comentarios-oculta" id="lista-comentarios-<?= $cuadro['id'] ?>">
                            <?php foreach($cuadro['comentarios_ejemplo'] as $comentario): ?>
                                <div class="comentario-item">
                                    <strong>Usuario:</strong> <?= $comentario ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </section>
        <?php endforeach; ?>
    </div>
</main>

<footer>
    <p>&copy; Todos los derechos reservados. 2026</p>
</footer>
</body>
</html>