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
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Inicio - Resignificarte</title>

    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/homepage.css">
    <link rel="stylesheet" href="css/homepage_usuario_registrado.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">

    <script src="js/retardo_cambio_pagina.js" defer></script>
    <script src="js/abrir_popup_homepage_usuario_registrado.js" defer></script>
</head>
<body>

<header>
    <!-- Logo -->
    <div class="logo-container">
        <a href="homepage_usuario_registrado.php">
            <img src="src/logo/logo_con_inifito.png" alt="Imagen del logo">
        </a>
    </div>

    <!-- Menú principal -->
    <nav class="menu-navegacion">
        <ul>
            <li>
                <a href="#">¿QUÉ ES RESIGNIFIC<span>ARTE</span>?</a>
            </li>
            <li>
                <a href="obras.php">OBRAS</a>
            </li>
            <li>
                <a href="contacto.php">CONTACTO</a>
            </li>
        </ul>
    </nav>

    <!-- Área de usuario -->
    <div class="area-usuario-dropdown">

        <button class="area-usuario area-usuario-btn" id="btn-usuario">

            <span class="enlace-acceder">
                <?php echo htmlspecialchars($nombre_usuario); ?>
            </span>

            <img
                    src="<?php echo htmlspecialchars($ruta_foto); ?>"
                    alt="Icono de usuario"
                    style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;"
            >

        </button>

        <!-- Popup usuario -->
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
    <img src="src/images/img_fondo.png" alt="imagen de fondo">

    <div class="info_principal">

        <h2>El arte es que jamás le habla a <br>dos personas de la misma forma. <br><span>Y a ti, ¿qué te dice?</span></h2>

        <a href="obras.php">Descubre las obras</a>

    </div>

    <!-- Flecha para bajar -->
    <a href="#siguiente-seccion" class="btn-bajar" title="Bajar">
        <img src="src/iconos/down_arrow.png" alt="Bajar">
    </a>

    <div class="hola" id="siguiente-seccion">
        asjdfjlajslfñ
    </div>
</main>

<a href="#" class="btn-volver-arriba" title="Volver arriba">
    <img src="src/iconos/up_arrow.png" alt="Subir">
</a>

<footer>
    <p>&copy; Todos los derechos reservados. 2026</p>
</footer>

</body>
</html>