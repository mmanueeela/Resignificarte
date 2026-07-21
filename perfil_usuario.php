<?php
require_once 'php/verificar_sesion.php';
require_once 'php/conexion.php'; // Tu conexión inteligente (local/plesk)

// Proteger la página: si no hay sesión, al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener los datos actuales del usuario de la base de datos
$consulta = "SELECT nombre, apellidos, email, pais, fecha_nacimiento FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($consulta);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <!-- Hoja de estilos específica para el perfil -->
    <link rel="stylesheet" href="css/perfil_usuario.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body>

<header>
    <!-- Logo -->
    <div class="logo-container">
        <a href="homepage_usuario_registrado.php"><img src="src/logo/logo_con_inifito.png" alt="Logo"></a>
    </div>

    <!-- Menú principal -->
    <nav class="menu-navegacion">
        <ul>
            <li><a href="homepage_usuario_registrado.php">Inicio</a></li>
            <li><a href="#">Servicios</a></li>
        </ul>
    </nav>

    <!-- Área de usuario (Botón simple de Cerrar Sesión para salir rápido desde el perfil) -->
    <a href="php/cerrar_sesion.php" class="area-usuario">
        <span class="enlace-acceder">Cerrar Sesión</span>
    </a>
</header>

<main>
    <div class="perfil-contenedor">
        <!-- CABECERA DEL PERFIL -->
        <div class="perfil-cabecera">
            <div class="perfil-info">
                <!-- Foto por defecto -->
                <img src="src/iconos/usuario.png" alt="Avatar del usuario" class="avatar">
                <div class="perfil-textos">
                    <h2><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></h2>
                    <p><?php echo htmlspecialchars($usuario['email']); ?></p>
                </div>
            </div>
            <button class="btn-editar">Edit</button>
        </div>

        <!-- FORMULARIO DE DATOS -->
        <form class="perfil-formulario">
            <div class="grid-2-columnas">
                <!-- Fila 1 -->
                <div class="grupo-input">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                </div>
                <div class="grupo-input">
                    <label>Apellidos</label>
                    <input type="text" name="apellidos" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>">
                </div>

                <!-- Fila 2 -->
                <div class="grupo-input">
                    <label>País</label>
                    <input type="text" name="pais" value="<?php echo htmlspecialchars($usuario['pais']); ?>">
                </div>
                <div class="grupo-input">
                    <label>Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" value="<?php echo htmlspecialchars($usuario['fecha_nacimiento']); ?>">
                </div>
            </div>

            <!-- SECCIÓN DE EMAIL -->
            <div class="seccion-email">
                <h3>My email Address</h3>
                <div class="email-actual">
                    <div class="icono-email">
                        <img src="src/iconos/correo.png" alt="Icono correo" style="width: 20px; filter: invert(1);">
                    </div>
                    <div class="textos-email">
                        <p class="email-direccion"><?php echo htmlspecialchars($usuario['email']); ?></p>
                        <p class="email-tiempo">1 month ago</p>
                    </div>
                </div>
                <button type="button" class="btn-add-email">+ Add Email Address</button>
            </div>
        </form>
    </div>
</main>

</body>
</html>