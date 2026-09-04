<?php
require_once 'php/logicaNegocio/cargar_usuario_header.php';
require_once 'php/conexion.php';

// ¡ELIMINADA LA REDIRECCIÓN AL LOGIN! Ahora los invitados pueden entrar.

// 1. COMPROBAR QUÉ ARTISTA QUEREMOS VER POR LA URL
if (!isset($_GET['id'])) {
    header("Location: obras.php");
    exit();
}

// Detectar en qué página estamos actualmente
$pagina_actual = basename($_SERVER['PHP_SELF']);

$artista_id = intval($_GET['id']);

// CLAVE: Si está logeado, cogemos su ID. Si no, le asignamos 0 para que la base de datos no dé error.
$usuario_id = $usuario_logeado ? $_SESSION['usuario_id'] : 0;

// Obtener el nombre del artista para el título
$stmt = $conexion->prepare("SELECT nombre FROM artistas WHERE id = ?");
$stmt->bind_param("i", $artista_id);
$stmt->execute();
$stmt->bind_result($nombre_artista_bd);
$stmt->fetch();
$stmt->close();

// Si el artista no existe, volver a obras
if (!$nombre_artista_bd) {
    header("Location: obras.php");
    exit();
}

// 2. Lógica de Desbloqueo (Comprobar si merece ver el cuadro secreto)
$stmt = $conexion->prepare("SELECT COUNT(*) FROM obras WHERE artista_id = ? AND es_recompensa = 0");
$stmt->bind_param("i", $artista_id);
$stmt->execute();
$stmt->bind_result($total_normales);
$stmt->fetch();
$stmt->close();

$stmt = $conexion->prepare("
    SELECT COUNT(DISTINCT c.obra_id) 
    FROM comentarios c 
    JOIN obras o ON c.obra_id = o.id 
    WHERE o.artista_id = ? AND o.es_recompensa = 0 AND c.usuario_id = ?
");
$stmt->bind_param("ii", $artista_id, $usuario_id);
$stmt->execute();
$stmt->bind_result($comentadas);
$stmt->fetch();
$stmt->close();

// Añadimos $total_normales > 0 para evitar que desbloquee sin haber cuadros normales
$ha_desbloqueado = ($comentadas >= $total_normales && $total_normales > 0);

// 3. Extraer Obras a mostrar
$sql = "SELECT * FROM obras WHERE artista_id = ?";
if (!$ha_desbloqueado) {
    $sql .= " AND es_recompensa = 0"; // Ocultar recompensas si no cumple
}
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $artista_id);
$stmt->execute();
$result_obras = $stmt->get_result();

$cuadros = [];
while ($obra = $result_obras->fetch_assoc()) {
    // Para cada obra, sacar sus comentarios
    $stmt_com = $conexion->prepare("
        SELECT c.id AS id_comentario, c.comentario, c.usuario_id, u.nombre 
        FROM comentarios c 
        JOIN usuarios u ON c.usuario_id = u.id 
        WHERE c.obra_id = ? 
        ORDER BY CASE WHEN c.usuario_id = ? THEN 1 ELSE 0 END DESC, c.fecha DESC
    ");
    $stmt_com->bind_param("ii", $obra['id'], $usuario_id);
    $stmt_com->execute();
    $res_com = $stmt_com->get_result();

    $comentarios = [];
    $usuario_ya_comento = false;

    while ($com = $res_com->fetch_assoc()) {
        if ($com['usuario_id'] == $usuario_id && $usuario_id != 0) {
            $usuario_ya_comento = true;
        }
        $comentarios[] = $com;
    }
    $stmt_com->close();

    $obra['comentarios_lista'] = $comentarios;
    $obra['usuario_ya_comento'] = $usuario_ya_comento;
    $cuadros[] = $obra;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Obras <?= htmlspecialchars($nombre_artista_bd) ?> - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/obras.css">
    <link rel="stylesheet" href="css/obras_general.css">
    <link rel="stylesheet" href="css/info_antonio_nieto_obras.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="js/retardo_cambio_pagina.js" defer></script>
    <script src="js/menu_hamburguesa.js" defer></script>
    <script src="js/abrir_popup_header.js" defer></script>
    <script src="js/interaccion_obras.js" defer></script>
    <script src="js/btn-arriba.js" defer></script>
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
            <li><a href="<?= (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1) ? 'homepage_administrador.php' : 'homepage.php' ?>" class="<?= ($pagina_actual == 'homepage.php' || $pagina_actual == 'homepage_administrador.php') ? 'activo' : '' ?>">INICIO</a></li>

            <li><a href="obras.php" class="<?= ($pagina_actual == 'obras.php' || $pagina_actual == 'Obras_Artista.php') ? 'activo' : '' ?>">ARTISTAS</a></li>

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
            <li><a href="<?= (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1) ? 'homepage_administrador.php' : 'homepage.php' ?>" class="<?= ($pagina_actual == 'homepage.php' || $pagina_actual == 'homepage_administrador.php') ? 'activo' : '' ?>">INICIO</a></li>

            <li><a href="obras.php" class="<?= ($pagina_actual == 'obras.php' || $pagina_actual == 'Obras_Artista.php') ? 'activo' : '' ?>">ARTISTAS</a></li>

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
                        Mi perfil (<?= htmlspecialchars(explode(' ', trim($nombre_usuario))[0]) ?>)
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
            Volver a Artistas
        </a>

        <!-- NOMBRE DEL ARTISTA DINÁMICO -->
        <h1><?= htmlspecialchars($nombre_artista_bd) ?></h1>

        <!-- NUEVO: BIOGRAFÍA SOLO PARA ANTONIO NIETO (ID 1) -->
        <?php if ($artista_id == 1): ?>
            <div class="info-antonio-nieto">
                <img src="src/images/img_antonio_nieto.jpg" alt="Retrato de Antonio Nieto">
                <div class="texto-biografia">
                    <p>
                        <strong>Antonio Nieto</strong>, artista mexicano, pintor, muralista y académico, teje en su obra un diálogo vivo entre el rigor de las técnicas tradicionales y la fuerza del lenguaje contemporáneo. Con una formación académica que culmina con el grado de Doctor en Artes y Diseño por la UNAM y una estancia en la Universidad Complutense de Madrid, su universo plástico se expande tanto en el lienzo como en la monumentalidad del muro. Ha participado en la realización de más de 15 murales en importantes recintos de México y el extranjero y ha compaginado su práctica profesional con su labor docente por más de 18 años. Cuenta con una sólida presencia internacional en más de treinta exposiciones que han recorrido recintos emblemáticos de México, España, Italia, Alemania y Cuba.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($ha_desbloqueado): ?>
            <p style="color: #523479; font-family: Montserrat; font-weight: bold; margin-bottom: 10px;">⭐ ¡Has desbloqueado la <a href="#obra-secreta" style="color: #523479; text-decoration: underline; cursor: pointer;">obra secreta</a>! ⭐</p>
        <?php endif; ?>
    </div>

    <div class="galeria-cuadros">
        <?php foreach ($cuadros as $index => $cuadro):
            // LÓGICA CORRECTA: Si es recompensa -> layout vertical. Si no -> alterna normal (inversa)
            $clase_layout = ($cuadro['es_recompensa'] == 1) ? 'layout-columna' : (($index % 2 !== 0) ? 'inversa' : '');

            // Estilo y ID
            $estilo_recompensa = ($cuadro['es_recompensa'] == 1) ? 'border: 3px solid #f1c40f; box-shadow: 0 0 20px rgba(241, 196, 15, 0.4);' : '';
            $id_seccion = ($cuadro['es_recompensa'] == 1) ? 'obra-secreta' : 'cuadro-' . $cuadro['id'];
            ?>
            <section id="<?= $id_seccion ?>" class="fila-cuadro <?= $clase_layout ?>" style="<?= $estilo_recompensa ?>">

                <!-- Lado: Imagen -->
                <div class="col-imagen">
                    <div class="wrapper-imagen">
                        <img src="<?= htmlspecialchars($cuadro['imagen']) ?>" alt="<?= htmlspecialchars($cuadro['titulo']) ?>" class="imagen-obra">

                        <!-- Etiqueta Comentado (Solo se muestra si es usuario y ha comentado) -->
                        <div id="badge-comentado-<?= $cuadro['id'] ?>" class="badge-comentado <?= ($cuadro['usuario_ya_comento']) ? 'visible' : '' ?>">
                            Comentado
                        </div>
                    </div>
                </div>

                <div class="col-info">
                    <h2><?= mb_strtoupper(htmlspecialchars($cuadro['titulo']), 'UTF-8') ?></h2>

                    <div class="reproductor-audio">
                        <audio id="audio-<?= $cuadro['id'] ?>" src="<?= htmlspecialchars($cuadro['audio']) ?>"></audio>

                        <button class="btn-play-pause" data-audio-id="audio-<?= $cuadro['id'] ?>">
                            <svg class="icono-play" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                            <svg class="icono-pause" viewBox="0 0 24 24" fill="currentColor" style="display:none;"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                        </button>

                        <button class="btn-reiniciar" data-audio-id="audio-<?= $cuadro['id'] ?>" title="Reiniciar audio">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
                        </button>

                        <div class="onda-audio">Escuchar</div>

                        <button class="btn-toggle-transcripcion" data-target="transcripcion-<?= $cuadro['id'] ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                    </div>

                    <div class="caja-transcripcion" id="transcripcion-<?= $cuadro['id'] ?>">
                        <?= $cuadro['transcripcion'] ?>
                    </div>

                    <hr class="separador-cuadro">

                    <div class="zona-escribir-comentario">
                        <!-- CLAVE: Si está logeado, mostramos el formulario. Si no, mostramos el mensaje -->
                        <?php if ($usuario_logeado): ?>
                            <form class="form-comentario" data-cuadro-id="<?= $cuadro['id'] ?>">
                                <input type="text" name="comentario" placeholder="Deja tu comentario sobre esta obra..." required class="input-comentario">
                                <button type="submit" class="btn-enviar-comentario">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="mensaje-registro-comentario">
                                <p>🔒 Debes <a href="login.php">iniciar sesión</a> para poder comentar las obras y así desbloquear la obra secreta.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="zona-ver-comentarios" id="zona-comentarios-<?= $cuadro['id'] ?>">
                        <?php
                        // Bloquear botón si NO ha comentado (los invitados tampoco han comentado)
                        $clase_bloqueado = (!$cuadro['usuario_ya_comento']) ? 'bloqueado' : '';
                        ?>
                        <button class="btn-desplegar-comentarios <?= $clase_bloqueado ?>" data-target="lista-comentarios-<?= $cuadro['id'] ?>">
                            <span>Comentarios de la comunidad</span>
                            <?php if(!$cuadro['usuario_ya_comento']): ?>
                                <svg class="candado" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <?php else: ?>
                                <svg class="flecha" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                            <?php endif; ?>
                        </button>

                        <div class="lista-comentarios-oculta" id="lista-comentarios-<?= $cuadro['id'] ?>">
                            <?php foreach($cuadro['comentarios_lista'] as $com): ?>
                                <div class="comentario-item">
                                    <strong><?= htmlspecialchars($com['nombre']) ?></strong>

                                    <!-- AQUI ESTÁ LA ETIQUETA EN VERDE PARA EL USUARIO -->
                                    <?php if($usuario_logeado && $com['usuario_id'] == $usuario_id): ?>
                                        <span style="color: #2ed573; font-size: 12px; margin-left: 5px; font-weight: bold;">(Tú)</span>
                                    <?php endif; ?>

                                    <!-- BOTÓN DE ELIMINAR COMENTARIO (SOLO PARA ADMIN) -->
                                    <?php if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1): ?>
                                        <form action="php/eliminar_comentario.php" method="POST" style="display:inline; float:right;" onsubmit="return confirm('¿Seguro que quieres borrar este comentario permanentemente?');">
                                            <input type="hidden" name="id_comentario" value="<?= $com['id_comentario'] ?>">
                                            <input type="hidden" name="id_artista" value="<?= $artista_id ?>">
                                            <button type="submit" style="background:none; border:none; color:#ff4757; cursor:pointer; font-size: 12px; text-decoration:underline; font-family: Montserrat, sans-serif;">Eliminar</button>
                                        </form>
                                    <?php endif; ?>

                                    <p style="margin: 5px 0 0 0;"><?= htmlspecialchars($com['comentario']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </section>
        <?php endforeach; ?>
    </div>
</main>

<div id="popup-recompensa" class="popup-recompensa-overlay">
    <div class="popup-recompensa-content">
        <h2>Enhorabuena 🎉</h2>
        <p>Has comentado en todas las obras y has desbloqueado un <strong>CUADRO SECRETO</strong>.</p>
        <button id="btn-cerrar-popup-recompensa">Descubrir recompensa</button>
    </div>
</div>

<!-- Flecha para volver arriba -->
<a href="#" class="btn-volver-arriba" title="Volver arriba">
    <img src="src/iconos/up_arrow.png" alt="Subir">
</a>

<footer>
    <p>&copy; Todos los derechos reservados. 2026</p>
</footer>
</body>
</html>