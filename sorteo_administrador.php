<?php
require_once 'php/logicaNegocio/cargar_usuario_header.php';
require_once 'php/conexion.php';

// Solo administradores
if (!$usuario_logeado || !isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("Location: homepage.php");
    exit();
}

$pagina_actual = basename($_SERVER['PHP_SELF']);

// Obtener SOLO usuarios que participan en el sorteo porque han subido imagen
$stmt = $conexion->prepare("
    SELECT
        s.id AS participacion_id,
        s.imagen,
        s.fecha,
        u.id AS usuario_id,
        u.nombre,
        u.apellidos,
        uc.email
    FROM sorteo_antonio_nieto s
    JOIN usuarios u ON s.usuario_id = u.id
    JOIN usuarios_credenciales uc ON u.id = uc.usuario_id
    JOIN obras o ON s.obra_id = o.id
    WHERE o.artista_id = 1
      AND o.es_recompensa = 1
      AND s.imagen IS NOT NULL
      AND s.imagen != ''
    ORDER BY s.fecha DESC
");

$stmt->execute();
$resultado = $stmt->get_result();
$participantes = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no, date=no, email=no, address=no">
    <title>Sorteo Antonio Nieto - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/sorteo_administrador.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="js/abrir_popup_header.js" defer></script>
    <script src="js/menu_hamburguesa.js" defer></script>
</head>
<body>
<header>
    <div class="logo-container">
        <a href="homepage_administrador.php">
            <img src="src/logo/logo_con_inifito.png" alt="Imagen del logo">
        </a>
    </div>
    <nav class="menu-navegacion">
        <ul>
            <li>
                <a href="artistas.php">ARTISTAS</a>
            </li>
            <li>
                <a href="homepage_administrador.php">PANEL ADMIN</a>
            </li>
            <li>
                <a href="sorteo_administrador.php" class="activo">SORTEO</a>
            </li>
        </ul>
    </nav>
    <div class="area-usuario-dropdown">
        <button class="area-usuario area-usuario-btn" id="btn-usuario">
            <span class="enlace-acceder">
                <?= htmlspecialchars($nombre_usuario) ?>
            </span>
            <img
                src="<?= htmlspecialchars($ruta_foto) ?>"
                alt="Usuario"
                style="width:35px;height:35px;border-radius:50%;object-fit:cover;"
            >
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
    <nav class="menu-navegacion-mobile" id="menu-mobile">
        <ul>
            <li><a href="artistas.php">ARTISTAS</a></li>
            <li><a href="homepage_administrador.php">PANEL ADMIN</a></li>
            <li><a href="sorteo_administrador.php" class="activo">SORTEO</a></li>
            <hr class="separador-movil">
            <li>
                <a href="perfil_usuario.php">
                    <img
                        src="<?= htmlspecialchars($ruta_foto) ?>"
                        alt="Usuario"
                        style="width:25px;height:25px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-right:10px;"
                    >
                    Mi perfil (<?= htmlspecialchars(explode(' ', trim($nombre_usuario))[0]) ?>)
                </a>
            </li>
            <li>
                <a href="php/cerrar_sesion.php" style="color:#ff8787;">Cerrar Sesión</a>
            </li>
        </ul>
    </nav>
</header>
<main class="main-sorteo-admin">
    <div class="cabecera-sorteo-admin">
        <a href="homepage_administrador.php" class="btn-volver-admin">
            ← Volver al panel de administración
        </a>
        <h1>Sorteo Antonio Nieto</h1>
        <p>
            Participantes que han comentado la obra final de Antonio Nieto y han subido una imagen para participar en el sorteo.
        </p>
        <div class="contador-participantes">
            <?= count($participantes) ?>
            <?= count($participantes) === 1 ? 'participante' : 'participantes' ?>
        </div>
    </div>
    <?php if (empty($participantes)): ?>
        <div class="sin-participantes">
            <h2>Todavía no hay participantes</h2>
            <p>Ningún usuario ha enviado una imagen para participar en el sorteo.</p>
        </div>
    <?php else: ?>
        <div class="grid-participantes">
            <?php foreach ($participantes as $participante): ?>
                <article class="tarjeta-participante">
                    <div class="imagen-participante">
                        <img
                                src="<?= htmlspecialchars($participante['imagen']) ?>"
                                alt="Imagen enviada por <?= htmlspecialchars($participante['nombre'] . ' ' . $participante['apellidos']) ?>"
                                loading="lazy"
                        >
                    </div>
                    <div class="info-participante">
                        <h2>
                            <?= htmlspecialchars($participante['nombre'] . ' ' . $participante['apellidos']) ?>
                        </h2>
                        <div class="dato-participante">
                            <span class="etiqueta-dato">Email</span>
                            <a href="mailto:<?= htmlspecialchars($participante['email']) ?>">
                                <?= htmlspecialchars($participante['email']) ?>
                            </a>
                        </div>
                        <div class="fecha-participante">
                            Participación enviada el
                            <?= date('d/m/Y H:i', strtotime($participante['fecha'])) ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>