<?php
require_once 'php/logicaNegocio/cargar_usuario_header.php';
require_once 'php/conexion.php';

// PROTEGER LA PÁGINA: Solo si es admin
if (!$usuario_logeado || !isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("Location: homepage.php");
    exit();
}

// Detectar en qué página estamos actualmente
$pagina_actual = basename($_SERVER['PHP_SELF']);

$stmt = $conexion->prepare("
    SELECT u.id, u.nombre, u.apellidos, c.email, u.fecha_registro 
    FROM usuarios u
    JOIN usuarios_credenciales c ON u.id = c.usuario_id
    WHERE u.id != ? 
    ORDER BY u.fecha_registro DESC
");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$result_usuarios = $stmt->get_result();
$usuarios = $result_usuarios->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no, date=no, email=no, address=no">
    <title>Panel de Administración - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="js/abrir_popup_header.js" defer></script>
    <script src="js/menu_hamburguesa.js" defer></script>
    <script src="js/admin.js" defer></script>
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
            <li><a href="artistas.php" class="<?= ($pagina_actual == 'artistas.php' || $pagina_actual == 'Obras_Artista.php') ? 'activo' : '' ?>">ARTISTAS</a></li>

            <?php if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1): ?>
                <li><a href="contacto.php" class="<?= ($pagina_actual == 'contacto.php') ? 'activo' : '' ?>">CONTACTO</a></li>
            <?php endif; ?>

            <?php if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1): ?>
                <li><a href="homepage_administrador.php" class="<?= ($pagina_actual == 'homepage_administrador.php') ? 'activo' : '' ?>">PANEL ADMIN</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Área de usuario (Ya trae el admin automáticamente) -->
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

    <button id="btn-menu">
        <div></div>
        <div></div>
        <div></div>
    </button>

    <!-- Menú Desplegable (Móvil) -->
    <nav class="menu-navegacion-mobile" id="menu-mobile">
        <ul>
            <li><a href="artistas.php" class="<?= ($pagina_actual == 'artistas.php' || $pagina_actual == 'Obras_Artista.php') ? 'activo' : '' ?>">ARTISTAS</a></li>

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

<main class="main-admin">
    <!-- (El resto del código del main sigue exactamente igual) -->
    <div class="cabecera-admin">
        <h1>Panel de Control</h1>
        <p>Gestiona la comunidad y las experiencias artísticas.</p>
    </div>

    <!-- SECCIÓN 1: GESTIÓN DE USUARIOS -->
    <section class="admin-seccion">
        <h2><span>👥</span> Gestión de Usuarios</h2>
        <div class="tabla-contenedor">
            <table class="tabla-usuarios">
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Fecha Registro</th>
                    <th>Acción</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($usuarios as $user): ?>
                    <tr>
                        <td data-label="Nombre:"><?= htmlspecialchars($user['nombre'] . ' ' . $user['apellidos']) ?></td>
                        <td data-label="Email:"><?= htmlspecialchars($user['email']) ?></td>
                        <td data-label="Fecha:"><?= date('d/m/Y', strtotime($user['fecha_registro'])) ?></td>
                        <td data-label="Acción:">
                            <form action="php/eliminar_usuario.php" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar a este usuario de forma permanente?');">
                                <input type="hidden" name="id_usuario" value="<?= $user['id'] ?>">
                                <button type="submit" class="btn-eliminar">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <hr class="separador-admin">

    <!-- SECCIÓN 2: AÑADIR EXPERIENCIA -->
    <section class="admin-seccion">
        <h2><span>🎨</span> Añadir Nueva Experiencia</h2>
        <p class="subtitulo-seccion">Crea un nuevo artista y sube sus cuadros. El primer cuadro será la portada de la experiencia.</p>

        <form action="php/procesar_experiencia.php" method="POST" enctype="multipart/form-data" class="form-experiencia">

            <div class="bloque-formulario">
                <h3>Datos de la Experiencia</h3>
                <div class="inputs-fila">
                    <input type="text" name="nombre_artista" placeholder="Nombre del Artista (Ej. Antonio Nieto)" required class="input-admin">
                    <input type="text" name="pais" placeholder="País (Ej. México)" required class="input-admin">
                </div>
                <!-- NUEVO INPUT PARA LA FOTO DEL ARTISTA -->
                <div class="input-file-custom" style="margin-top: 15px;">
                    <label>👤 Subir Foto del Artista (Portada de la Experiencia)</label>
                    <span class="btn-falso">Seleccionar archivo</span>
                    <input type="file" id="foto-artista" name="foto_artista" accept="image/*" required>
                    <span class="nombre-archivo">Ningún archivo seleccionado</span>
                </div>
            </div>

            <div id="contenedor-cuadros">
                <div class="bloque-cuadro" data-index="1">
                    <div class="cabecera-cuadro">
                        <h3>Cuadro 1 (Portada de Experiencia)</h3>
                    </div>
                    <div class="inputs-fila">
                        <input type="text" name="titulos[]" placeholder="Título del Cuadro" required class="input-admin">
                        <div class="input-file-custom">
                            <label>📸 Subir Imagen</label>
                            <span class="btn-falso">Seleccionar archivo</span>
                            <input type="file" name="imagenes[]" accept="image/*" required>
                            <span class="nombre-archivo">Ningún archivo seleccionado</span>
                        </div>
                        <div class="input-file-custom">
                            <label>🎵 Subir Audio</label>
                            <span class="btn-falso">Seleccionar archivo</span>
                            <input type="file" name="audios[]" accept="audio/*" required>
                            <span class="nombre-archivo">Ningún archivo seleccionado</span>
                        </div>
                    </div>
                    <textarea name="transcripciones[]" placeholder="Pega aquí la transcripción de tu obra..." required class="textarea-admin"></textarea>

                    <label class="checkbox-recompensa">
                        <input type="checkbox" name="es_recompensa[0]" value="1"> ¿Es el cuadro secreto de recompensa final?
                    </label>
                </div>
            </div>

            <div class="acciones-form">
                <button type="button" id="btn-add-cuadro" class="btn-secundario">+ Añadir otro cuadro a esta experiencia</button>
                <button type="submit" class="btn-principal">Guardar Experiencia Completa</button>
            </div>
        </form>
    </section>
</main>

<!-- Flecha para volver arriba -->
<a href="#" class="btn-volver-arriba" title="Volver arriba">
    <img src="src/iconos/up_arrow.png" alt="Subir">
</a>

</body>
</html>