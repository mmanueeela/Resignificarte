<?php
require_once 'php/logicaNegocio/cargar_usuario_header.php';
require_once 'php/conexion.php';

// 1. SEGURIDAD: Solo admins
if (!$usuario_logeado || !isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("Location: homepage.php");
    exit();
}

// 2. COMPROBAR QUÉ ARTISTA VAMOS A EDITAR
if (!isset($_GET['id'])) {
    header("Location: obras.php");
    exit();
}
$artista_id = intval($_GET['id']);
$pagina_actual = 'obras.php'; // Para que en el header salga subrayado "OBRAS"

// 3. OBTENER LOS DATOS DEL ARTISTA
$stmt = $conexion->prepare("SELECT nombre, pais, imagen_perfil FROM artistas WHERE id = ?");
$stmt->bind_param("i", $artista_id);
$stmt->execute();
$stmt->bind_result($nombre_artista, $pais_artista, $imagen_artista);
if (!$stmt->fetch()) {
    header("Location: obras.php");
    exit();
}
$stmt->close();

// 4. OBTENER TODOS LOS CUADROS DE ESTE ARTISTA
$stmt_obras = $conexion->prepare("SELECT * FROM obras WHERE artista_id = ? ORDER BY id ASC");
$stmt_obras->bind_param("i", $artista_id);
$stmt_obras->execute();
$result_obras = $stmt_obras->get_result();
$cuadros = $result_obras->fetch_all(MYSQLI_ASSOC);
$stmt_obras->close();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Experiencia - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="js/menu_hamburguesa.js" defer></script>
    <script src="js/abrir_popup_header.js" defer></script>
    <script src="js/btn-arriba.js" defer></script>
</head>
<body>
<header>
    <!-- Logo dinámico -->
    <div class="logo-container">
        <a href="homepage_administrador.php">
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

<main class="main-obra">

    <div style="margin-bottom: 20px;">
        <a href="obras.php" style="color: #523479; text-decoration: none; font-family: Montserrat; font-weight: bold;">
            ← Volver a Artistas
        </a>
    </div>

    <section class="admin-seccion">
        <h2>Editar Experiencia</h2>
        <p class="subtitulo-seccion">Modifica los datos del artista o actualiza sus cuadros.</p>

        <form action="php/procesar_edicion.php" method="POST" enctype="multipart/form-data" class="form-experiencia">

            <input type="hidden" name="artista_id" value="<?= $artista_id ?>">

            <!-- BLOQUE ARTISTA -->
            <div class="bloque-formulario">
                <h3>Datos de la Experiencia</h3>
                <div class="inputs-fila">
                    <input type="text" name="nombre_artista" value="<?= htmlspecialchars($nombre_artista) ?>" required class="input-admin">
                    <input type="text" name="pais" value="<?= htmlspecialchars($pais_artista) ?>" required class="input-admin">
                </div>
                <!-- NUEVO INPUT EDICIÓN FOTO ARTISTA -->
                <div class="input-file-custom" style="margin-top: 15px;">
                    <label>👤 Cambiar Foto del Artista (Opcional)</label>
                    <input type="file" name="foto_artista" accept="image/*">
                    <?php if(!empty($imagen_artista)): ?>
                        <small style="color:#666;">Actual: <?= basename($imagen_artista) ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- BLOQUE CUADROS (Se cargan los que ya existen) -->
            <div id="contenedor-cuadros">
                <?php foreach ($cuadros as $index => $cuadro): ?>
                    <div class="bloque-cuadro">
                        <div class="cabecera-cuadro">
                            <h3>Cuadro <?= $index + 1 ?> <?= ($index == 0) ? '(Portada)' : '' ?></h3>
                        </div>

                        <!-- ID oculto para saber qué cuadro estamos actualizando -->
                        <input type="hidden" name="obra_id[]" value="<?= $cuadro['id'] ?>">

                        <div class="inputs-fila">
                            <input type="text" name="titulos[]" value="<?= htmlspecialchars($cuadro['titulo']) ?>" required class="input-admin">

                            <div class="input-file-custom">
                                <label>📸 Cambiar Imagen (Opcional)</label>
                                <input type="file" name="imagenes[]" accept="image/*">
                                <small style="color:#666;">Actual: <?= basename($cuadro['imagen']) ?></small>
                            </div>

                            <div class="input-file-custom">
                                <label>🎵 Cambiar Audio (Opcional)</label>
                                <input type="file" name="audios[]" accept="audio/*">
                                <small style="color:#666;">Actual: <?= basename($cuadro['audio']) ?></small>
                            </div>
                        </div>

                        <textarea name="transcripciones[]" required class="textarea-admin"><?= htmlspecialchars($cuadro['transcripcion']) ?></textarea>

                        <label class="checkbox-recompensa">
                            <input type="checkbox" name="es_recompensa[<?= $index ?>]" value="1" <?= ($cuadro['es_recompensa'] == 1) ? 'checked' : '' ?>>
                            ¿Es el cuadro secreto de recompensa final?
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="acciones-form">
                <button type="submit" class="btn-principal" style="width: 100%;">Guardar Cambios</button>
            </div>
        </form>
    </section>
</main>

<!-- Flecha para volver arriba -->
<a href="#" class="btn-volver-arriba" title="Volver arriba">
    <img src="src/iconos/up_arrow.png" alt="Subir">
</a>

<footer>
    <p>&copy; Todos los derechos reservados. 2026</p>
</footer>
</body>
</html>