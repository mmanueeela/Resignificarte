<?php
require_once 'php/logicaNegocio/cargar_usuario_header.php';
require_once 'php/conexion.php';

// PROTEGER LA PÁGINA: Solo si es admin (asumiendo que en tu login guardas $_SESSION['es_admin'])
// Si no es admin, lo echamos de vuelta a la homepage normal
if (!$usuario_logeado || !isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("Location: homepage.php");
    exit();
}

// Extraer todos los usuarios (excepto el propio admin actual) para poder gestionarlos
$stmt = $conexion->prepare("SELECT id, nombre, apellidos, email, fecha_registro FROM usuarios WHERE id != ? ORDER BY fecha_registro DESC");
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
    <title>Panel de Administración - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/admin.css"> <!-- Nuevo CSS -->
    <script src="js/menu_hamburguesa.js" defer></script>
    <script src="js/admin.js" defer></script> <!-- Nuevo JS -->
</head>
<body>
<header>
    <!-- Logo -->
    <div class="logo-container">
        <a href="homepage.php"><img src="src/logo/logo_con_inifito.png" alt="Imagen del logo"></a>
    </div>

    <nav class="menu-navegacion">
        <ul>
            <li><a href="obras.php">OBRAS</a></li>
            <li><a href="homepage_admin.php" style="color: #523479; text-decoration: underline;">PANEL ADMIN</a></li>
        </ul>
    </nav>

    <!-- Área de usuario (Con la etiqueta ADMIN) -->
    <div class="area-usuario-dropdown">
        <button class="area-usuario area-usuario-btn" id="btn-usuario">
            <span class="enlace-acceder" style="color: #523479; font-weight: bold;">
                <?= htmlspecialchars($nombre_usuario) ?> (admin)
            </span>
            <img src="<?= htmlspecialchars($ruta_foto) ?>" alt="Usuario" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
        </button>

        <div class="dropdown-menu" id="dropdown-usuario">
            <a href="perfil_usuario.php" class="dropdown-item">Ver perfil</a>
            <a href="php/cerrar_sesion.php" class="dropdown-item cerrar-sesion">Cerrar Sesión</a>
        </div>
    </div>
</header>

<main class="main-admin">
    <div class="cabecera-admin">
        <h1>Panel de Control</h1>
        <p>Gestiona la comunidad y las experiencias artísticas.</p>
    </div>

    <!-- SECCIÓN 1: GESTIÓN DE USUARIOS -->
    <section class="admin-seccion">
        <h2>👥 Gestión de Usuarios</h2>
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
                        <td><?= htmlspecialchars($user['nombre'] . ' ' . $user['apellidos']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= date('d/m/Y', strtotime($user['fecha_registro'])) ?></td>
                        <td>
                            <!-- Formulario que irá al backend para eliminar -->
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
        <h2>🎨 Añadir Nueva Experiencia</h2>
        <p class="subtitulo-seccion">Crea un nuevo artista y sube sus cuadros. El primer cuadro será la portada de la experiencia.</p>

        <form action="php/procesar_experiencia.php" method="POST" enctype="multipart/form-data" class="form-experiencia">

            <div class="bloque-formulario">
                <h3>Datos de la Experiencia</h3>
                <div class="inputs-fila">
                    <input type="text" name="nombre_artista" placeholder="Nombre del Artista (Ej. Antonio Nieto)" required class="input-admin">
                    <input type="text" name="pais" placeholder="País (Ej. México)" required class="input-admin">
                </div>
            </div>

            <!-- Contenedor dinámico de cuadros (Manejado por JS) -->
            <div id="contenedor-cuadros">
                <div class="bloque-cuadro" data-index="1">
                    <div class="cabecera-cuadro">
                        <h3>Cuadro 1 (Portada de Experiencia)</h3>
                    </div>
                    <div class="inputs-fila">
                        <input type="text" name="titulos[]" placeholder="Título del Cuadro" required class="input-admin">
                        <div class="input-file-custom">
                            <label>📸 Subir Imagen</label>
                            <input type="file" name="imagenes[]" accept="image/*" required>
                        </div>
                        <div class="input-file-custom">
                            <label>🎵 Subir Audio</label>
                            <input type="file" name="audios[]" accept="audio/*" required>
                        </div>
                    </div>
                    <textarea name="transcripciones[]" placeholder="Pega aquí el HTML de la transcripción (con <p>, <strong>, etc)..." required class="textarea-admin"></textarea>

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
</body>
</html>