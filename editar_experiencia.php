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
    <link rel="stylesheet" href="css/admin.css"> <!-- Inputs y botones -->
    <link rel="stylesheet" href="css/obras_general.css"> <!-- MAGIA: La estructura del layout alternado -->
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="js/menu_hamburguesa.js" defer></script>
    <script src="js/abrir_popup_header.js" defer></script>
    <script src="js/admin.js" defer></script> <!-- Para que funcionen los botones de subir archivo -->
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
            <li><a href="obras.php" class="<?= ($pagina_actual == 'obras.php' || $pagina_actual == 'Obras_Artista.php') ? 'activo' : '' ?>">OBRAS</a></li>
            <li><a href="homepage_administrador.php" class="activo">PANEL ADMIN</a></li>
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
            <li><a href="<?= (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1) ? 'homepage_administrador.php' : 'homepage.php' ?>">INICIO</a></li>
            <li><a href="obras.php">OBRAS</a></li>
            <li><a href="homepage_administrador.php" class="activo">PANEL ADMIN</a></li>
            <hr class="separador-movil">
            <li>
                <a href="perfil_usuario.php">
                    <img src="<?= htmlspecialchars($ruta_foto) ?>" alt="Usuario" style="width: 25px; height: 25px; border-radius: 50%; object-fit: cover; vertical-align: middle; margin-right: 10px;">
                    Mi perfil (<?= htmlspecialchars(explode(' ', trim($nombre_usuario))[0]) ?>)
                </a>
            </li>
            <li><a href="php/cerrar_sesion.php" style="color: #ff8787;">Cerrar Sesión</a></li>
        </ul>
    </nav>
</header>

<!-- USAMOS EL CONTENEDOR DE OBRAS PARA HEREDAR EL DISEÑO -->
<main class="main-obras-artista">
    <div class="cabecera-artista" style="margin-bottom: 40px;">
        <a href="obras.php" class="btn-volver-obras">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            Volver a Obras
        </a>
        <h1 style="font-family: 'AnsteryScript', cursive; font-size: 70px; color: #523479; margin: 15px 0;">Editar Experiencia</h1>
        <p style="color: #523479; font-family: Montserrat; font-weight: bold; text-align: center;">Modifica los datos y mira cómo se reflejarán en la galería.</p>
    </div>

    <form action="php/procesar_edicion.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="artista_id" value="<?= $artista_id ?>">

        <!-- BLOQUE ARTISTA CENTRADO -->
        <div class="bloque-formulario" style="max-width: 900px; margin: 0 auto 60px auto; background-color: rgba(255, 255, 255, 0.8);">
            <h3 style="font-family: Montserrat; color: #523479; font-size: 22px; border-bottom: 1px solid rgba(82, 52, 121, 0.1); padding-bottom: 10px; margin-bottom: 20px;">Datos Principales del Artista</h3>
            <div class="inputs-fila">
                <input type="text" name="nombre_artista" value="<?= htmlspecialchars($nombre_artista) ?>" required class="input-admin">
                <input type="text" name="pais" value="<?= htmlspecialchars($pais_artista) ?>" required class="input-admin">
            </div>
            <div class="input-file-custom">
                <label>👤 Cambiar Foto del Artista (Opcional)</label>
                <span class="btn-falso">Seleccionar archivo</span>
                <input type="file" name="foto_artista" accept="image/*">
                <span class="nombre-archivo">Actual: <?= !empty($imagen_artista) ? basename($imagen_artista) : 'Ninguna' ?></span>
            </div>
        </div>

        <!-- APLICAMOS LA GALERÍA CON EFECTO ZIG-ZAG -->
        <div class="galeria-cuadros">
            <?php foreach ($cuadros as $index => $cuadro):
                // Misma lógica: Recompensa en vertical, el resto alterna normal e inversa
                $clase_layout = ($cuadro['es_recompensa'] == 1) ? 'layout-columna' : (($index % 2 !== 0) ? 'inversa' : '');

                // Estilo especial si es la recompensa
                $estilo_recompensa = ($cuadro['es_recompensa'] == 1) ? 'border: 3px solid #f1c40f; box-shadow: 0 0 20px rgba(241, 196, 15, 0.4); background-color: rgba(255, 249, 230, 0.9);' : 'background-color: rgba(255, 255, 255, 0.8);';
                ?>
                <section class="fila-cuadro <?= $clase_layout ?>" style="<?= $estilo_recompensa ?>">
                    <!-- ID oculto para saber qué cuadro estamos actualizando -->
                    <input type="hidden" name="obra_id[]" value="<?= $cuadro['id'] ?>">

                    <!-- Lado: IMAGEN -->
                    <div class="col-imagen" style="flex-direction: column; gap: 20px; align-items: center;">
                        <img src="<?= htmlspecialchars($cuadro['imagen']) ?>" alt="Cuadro actual" class="imagen-obra">

                        <div class="input-file-custom" style="width: 100%;">
                            <label>📸 Cambiar Imagen (Opcional)</label>
                            <span class="btn-falso">Seleccionar archivo</span>
                            <input type="file" name="imagenes[]" accept="image/*">
                            <span class="nombre-archivo">Actual: <?= basename($cuadro['imagen']) ?></span>
                        </div>
                    </div>

                    <!-- Lado: INFO Y AUDIO -->
                    <div class="col-info">
                        <h3 style="color: #523479; font-family: Montserrat; font-size: 18px; text-transform: uppercase; margin-top: 0; margin-bottom: 10px;">Cuadro <?= $index + 1 ?> <?= ($index == 0) ? '(Portada)' : '' ?></h3>

                        <input type="text" name="titulos[]" value="<?= htmlspecialchars($cuadro['titulo']) ?>" required class="input-admin" style="font-size: 24px; font-weight: bold; margin-bottom: 20px; width: 100%;">

                        <!-- Reproductor de audio nativo para escuchar lo que hay guardado -->
                        <div style="background-color: rgba(82, 52, 121, 0.1); padding: 10px; border-radius: 15px; margin-bottom: 15px;">
                            <audio src="<?= htmlspecialchars($cuadro['audio']) ?>" controls style="width: 100%; height: 40px;"></audio>
                        </div>

                        <div class="input-file-custom" style="margin-bottom: 20px;">
                            <label>🎵 Cambiar Audio (Opcional)</label>
                            <span class="btn-falso">Seleccionar archivo</span>
                            <input type="file" name="audios[]" accept="audio/*">
                            <span class="nombre-archivo">Actual: <?= basename($cuadro['audio']) ?></span>
                        </div>

                        <textarea name="transcripciones[]" required class="textarea-admin" style="height: 160px; font-size: 14px;"><?= htmlspecialchars($cuadro['transcripcion']) ?></textarea>

                        <label class="checkbox-recompensa">
                            <input type="checkbox" name="es_recompensa[<?= $index ?>]" value="1" <?= ($cuadro['es_recompensa'] == 1) ? 'checked' : '' ?>>
                            ¿Es el cuadro secreto de recompensa final?
                        </label>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>

        <div class="acciones-form" style="max-width: 900px; margin: 60px auto 0 auto;">
            <button type="submit" class="btn-principal" style="width: 100%; font-size: 22px; padding: 25px; border-radius: 40px;">💾 Guardar Todos los Cambios</button>
        </div>
    </form>
</main>
</body>
</html>