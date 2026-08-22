<?php
require_once 'php/logicaNegocio/cargar_usuario_header.php';
require_once 'php/conexion.php';

// Si no está logeado, lo ideal sería mandarlo a login o bloquear la página
if (!$usuario_logeado) {
    header("Location: login.php");
    exit();
}

$artista_id = 1; // ID de Antonio Nieto en la BD
$usuario_id = $_SESSION['usuario_id'];

// 1. Lógica de Desbloqueo (Comprobar si merece ver el cuadro secreto)
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

$ha_desbloqueado = ($comentadas >= $total_normales);

// 2. Extraer Obras a mostrar
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
    // Para cada obra, sacar sus comentarios (Ordenando primero por tus propios comentarios)
    $stmt_com = $conexion->prepare("
        SELECT c.comentario, c.usuario_id, u.nombre 
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
        if ($com['usuario_id'] == $usuario_id) {
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
        <?php if ($ha_desbloqueado): ?>
            <p style="color: #523479; font-family: Montserrat; font-weight: bold;">⭐ ¡Has desbloqueado la <a href="#obra-secreta" style="color: #523479; text-decoration: underline; cursor: pointer;">obra secreta</a>! ⭐</p>
        <?php endif; ?>
    </div>

    <div class="galeria-cuadros">
        <?php foreach ($cuadros as $index => $cuadro):
            // LÓGICA NUEVA: Si es recompensa -> layout vertical. Si no -> alterna normal
            $clase_layout = ($cuadro['es_recompensa'] == 1) ? 'layout-columna' : (($index % 2 !== 0) ? 'inversa' : '');

            // Estilo y ID
            $estilo_recompensa = ($cuadro['es_recompensa'] == 1) ? 'border: 3px solid #f1c40f; box-shadow: 0 0 20px rgba(241, 196, 15, 0.4);' : '';
            $id_seccion = ($cuadro['es_recompensa'] == 1) ? 'obra-secreta' : 'cuadro-' . $cuadro['id'];
            ?>
            <section id="<?= $id_seccion ?>" class="fila-cuadro <?= $clase_layout ?>" style="<?= $estilo_recompensa ?>">
                <div class="col-imagen">
                    <img src="<?= htmlspecialchars($cuadro['imagen']) ?>" alt="<?= htmlspecialchars($cuadro['titulo']) ?>" class="imagen-obra">
                </div>

                <div class="col-info">
                    <h2><?= mb_strtoupper(htmlspecialchars($cuadro['titulo']), 'UTF-8') ?></h2>

                    <div class="reproductor-audio">
                        <audio id="audio-<?= $cuadro['id'] ?>" src="<?= htmlspecialchars($cuadro['audio']) ?>"></audio>

                        <!-- Botón Play/Pause -->
                        <button class="btn-play-pause" data-audio-id="audio-<?= $cuadro['id'] ?>">
                            <svg class="icono-play" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                            <svg class="icono-pause" viewBox="0 0 24 24" fill="currentColor" style="display:none;"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                        </button>

                        <!-- NUEVO: Botón Reiniciar -->
                        <button class="btn-reiniciar" data-audio-id="audio-<?= $cuadro['id'] ?>" title="Reiniciar audio">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
                        </button>

                        <div class="onda-audio">Escuchar</div>

                        <!-- Botón Transcripción -->
                        <button class="btn-toggle-transcripcion" data-target="transcripcion-<?= $cuadro['id'] ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                    </div>

                    <div class="caja-transcripcion" id="transcripcion-<?= $cuadro['id'] ?>">
                        <?= $cuadro['transcripcion'] ?>
                    </div>

                    <hr class="separador-cuadro">

                    <div class="zona-escribir-comentario">
                        <form class="form-comentario" data-cuadro-id="<?= $cuadro['id'] ?>">
                            <input type="text" name="comentario" placeholder="Deja tu comentario sobre esta obra..." required class="input-comentario">
                            <button type="submit" class="btn-enviar-comentario">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            </button>
                        </form>
                    </div>

                    <div class="zona-ver-comentarios" id="zona-comentarios-<?= $cuadro['id'] ?>">
                        <?php
                        // Bloquear botón si NO ha comentado
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
                                    <?php if($com['usuario_id'] == $usuario_id): ?>
                                        <span style="color: #2ed573; font-size: 12px; margin-left: 5px; font-weight: bold;">(Tú) ✔</span>
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

<!-- ==============================
     POPUP RECOMPENSA SECRETA
     ============================== -->
<div id="popup-recompensa" class="popup-recompensa-overlay">
    <div class="popup-recompensa-content">
        <h2>Enhorabuena 🎉</h2>
        <p>Has comentado en todas las obras y has desbloqueado un <strong>CUADRO SECRETO</strong>.</p>
        <button id="btn-cerrar-popup-recompensa">Descubrir recompensa</button>
    </div>
</div>

<footer>
    <p>&copy; Todos los derechos reservados. 2026</p>
</footer>
</body>
</html>