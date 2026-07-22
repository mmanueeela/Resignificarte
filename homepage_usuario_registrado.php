<?php

require_once 'php/logicaNegocio/verificar_sesion.php';
require_once 'php/conexion.php';

$usuario_id = $_SESSION['usuario_id'];

// 2. Consultamos SIEMPRE la base de datos para tener los datos reales (nombre, foto)
$consulta = "SELECT nombre, foto_perfil FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($consulta);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario_bd = $resultado->fetch_assoc();
$stmt->close();

// 3. Preparamos el nombre y la foto para mostrarlos en el HTML
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
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Resignificarte - Inicio</title>
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
        <a href="#"><img src="src/logo/logo_con_inifito.png" alt="Imagen del logo"></a>
    </div>

    <!-- Menú principal -->
    <nav class="menu-navegacion">
        <ul>
            <li><a href="#">Servicios</a></li>
            <li><a href="#">Nosotros</a></li>
            <li><a href="#">Contacto</a></li>
        </ul>
    </nav>

    <!-- Área de usuario con Dropdown -->
    <div class="area-usuario-dropdown">
        <button class="area-usuario area-usuario-btn" id="btn-usuario">
            <span class="enlace-acceder"><?php echo htmlspecialchars($nombre_usuario); ?></span>

            <!-- Mostramos la foto real del usuario redondeada -->
            <img src="<?php echo htmlspecialchars($ruta_foto); ?>" alt="Icono de usuario" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">
        </button>

        <!-- El pequeño popup flotante -->
        <div class="dropdown-menu" id="dropdown-usuario">
            <a href="perfil_usuario.php" class="dropdown-item">Ver perfil</a>
            <a href="php/cerrar_sesion.php" class="dropdown-item cerrar-sesion">Cerrar Sesión</a>
        </div>
    </div>
</header>

<main>

</main>

<footer>
    <p>Todos los derechos reservados. 2026 &copy;</p>
</footer>

</body>
</html>