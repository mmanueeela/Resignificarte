<?php
require_once 'php/logicaNegocio/verificar_sesion.php';
require_once 'php/conexion.php';

$usuario_id = $_SESSION['usuario_id'];

$consulta = "SELECT nombre, foto_perfil FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($consulta);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();

$resultado = $stmt->get_result();
$usuario_bd = $resultado->fetch_assoc();

$stmt->close();

$nombre_usuario = !empty($usuario_bd['nombre']) ? $usuario_bd['nombre'] : 'Usuario';
$foto_bd = isset($usuario_bd['foto_perfil']) ? trim($usuario_bd['foto_perfil']) : '';

if (empty($foto_bd) || strtolower($foto_bd) === 'null') {
    $ruta_foto = 'src/iconos/usuario.png';
} else {
    $ruta_foto = $foto_bd;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Resignificarte</title>

    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/homepage.css">
    <link rel="stylesheet" href="css/homepage_usuario_registrado.css">
    <link rel="icon" href="favicon.ico">

    <script src="js/retardo_cambio_pagina.js" defer></script>
    <script src="js/abrir_popup_homepage_usuario_registrado.js" defer></script>
</head>
<body>

<header>
    <!-- Logo -->
    <div class="logo-container">
        <a href="homepage_usuario_registrado.php">
            <img src="src/logo/logo_con_inifito.png" alt="Logo">
        </a>
    </div>

    <!-- Menú principal -->
    <nav class="menu-navegacion">
        <ul>
            <li>
                <a href="#">
                    ¿QUÉ ES RESIGNIFIC<span>ARTE</span>?
                </a>
            </li>
            <li>
                <a href="obras.php">
                    OBRAS
                </a>
            </li>
            <li>
                <a href="contacto.php">
                    CONTACTO
                </a>
            </li>
        </ul>
    </nav>

    <!-- Usuario -->
    <div class="area-usuario-dropdown">
        <button class="area-usuario area-usuario-btn" id="btn-usuario">
            <span class="enlace-acceder">
                <?php echo htmlspecialchars($nombre_usuario); ?>
            </span>
            <img
                    src="<?php echo htmlspecialchars($ruta_foto); ?>"
                    alt="Usuario"
                    style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;"
            >
        </button>

        <div class="dropdown-menu" id="dropdown-usuario">
            <a href="perfil_usuario.php" class="dropdown-item">
                Ver perfil
            </a>
            <a href="php/cerrar_sesion.php" class="dropdown-item cerrar-sesion">
                Cerrar sesión
            </a>
        </div>
    </div>
</header>

<main>
    <!-- TÍTULO + BUSCADOR -->
    <div class="contenido-inicial">
        <h1>EXPERIENCIAS</h1>
        <div class="buscador">
            <input type="text" placeholder="Busca el artista...">
            <button type="button" class="boton-busqueda" aria-label="Buscar">
                <img src="src/iconos/lupa.png" alt="Buscar">
            </button>
        </div>
    </div>

    <!-- EXPERIENCIA ARTISTA -->
    <section class="experiencia-1">
        <div class="contenedor-imagen-mas-texto">
            <img src="src/images/img_antonio_nieto.jpg" alt="Antonio Nieto">
            <div class="contenedor-texto">
                <h2>Experiencia #1</h2>
                <p>
                    <img src="src/iconos/bandera_mexico.png" alt="México">
                    Obras de Antonio Nieto, México
                </p>
            </div>
        </div>
        <a href="obras.php">Ver las obras</a>
    </section>

    <!-- Flecha bajar -->
    <a href="#siguiente-seccion" class="btn-bajar" title="Bajar">
        <img src="src/iconos/down_arrow.png" alt="Bajar">
    </a>

    <!-- FUTURA ZONA DE OBRAS -->
    <section class="zona-obras" id="siguiente-seccion">
        <h2>Obras destacadas</h2>
        <!-- Aquí irán las obras dinámicas -->
    </section>
</main>

<footer>
    <p>Todos los derechos reservados. 2026 &copy;</p>
</footer>

</body>
</html>