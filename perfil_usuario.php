<?php
require_once 'php/logicaNegocio/verificar_sesion.php';
require_once 'php/conexion.php';
require_once 'php/logicaNegocio/datos_perfil_usuario.php'; // Importamos las funciones de lógica de negocio

// Proteger la página
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// --- PROCESAR LA ACTUALIZACIÓN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    // Llamamos a la función de la lógica de negocio
    $actualizado = actualizarPerfilUsuario($conexion, $usuario_id, $_POST);

    if ($actualizado) {
        // Actualizamos el dato del nombre de la sesión
        $_SESSION['usuario_nombre'] = $_POST['nombre'];

        header("Location: perfil_usuario.php?actualizado=1");
        exit();
    }
}
// ---------------------------------

// Obtener los datos llamando a la función
$usuario = obtenerDatosUsuario($conexion, $usuario_id);

// Separar la fecha de nacimiento para pintar los 3 selects en el HTML
$fecha_partes = explode('-', $usuario['fecha_nacimiento']);
$ano_bd = isset($fecha_partes[0]) ? $fecha_partes[0] : '';
$mes_bd = isset($fecha_partes[1]) ? $fecha_partes[1] : '';
$dia_bd = isset($fecha_partes[2]) ? $fecha_partes[2] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/perfil_usuario.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="js/perfil_usuario.js" defer></script>
</head>
<body>

<header>
    <div class="logo-container">
        <a href="homepage_usuario_registrado.php"><img src="src/logo/logo_con_inifito.png" alt="Logo"></a>
    </div>
    <nav class="menu-navegacion">
        <ul>
            <li><a href="homepage_usuario_registrado.php">Inicio</a></li>
            <li><a href="#">Servicios</a></li>
        </ul>
    </nav>
    <a href="php/cerrar_sesion.php" class="area-usuario">
        <span class="enlace-acceder">Cerrar Sesión</span>
    </a>
</header>

<main>
    <div class="perfil-contenedor">
        <!-- CABECERA ALEGRE DEL PERFIL -->
        <div class="perfil-banner"></div>

        <div class="perfil-cabecera">
            <div class="perfil-info">
                <?php
                // Limpiamos espacios y comprobamos si está vacío o si pone literalmente la palabra "NULL"
                $foto_bd = trim(isset($usuario['foto_perfil']) ? $usuario['foto_perfil'] : '');

                if (empty($foto_bd) || $foto_bd === 'NULL') {
                    $ruta_foto = 'src/iconos/usuario.png';
                } else {
                    $ruta_foto = $foto_bd;
                }
                ?>
                <img src="<?php echo htmlspecialchars($ruta_foto); ?>" alt="Avatar del usuario" class="avatar" style="object-fit: cover;">
                <div class="perfil-textos">
                    <h2><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></h2>
                    <p class="rol-usuario">Miembro de Resignificarte</p>
                </div>
            </div>
            <!-- Botones de Acción -->
            <div class="botones-accion">
                <button type="button" class="btn-editar" id="btn-edit">Editar Perfil</button>
                <button type="submit" form="form-perfil" class="btn-guardar" id="btn-save" style="display: none;">Aceptar</button>
                <button type="button" class="btn-cancelar" id="btn-cancel" style="display: none;">Cancelar</button>
            </div>
        </div>

        <?php if(isset($_GET['actualizado'])): ?>
            <div class="alerta-exito">¡Tus datos se han guardado correctamente!</div>
        <?php endif; ?>

        <!-- FORMULARIO DE DATOS -->
        <form class="perfil-formulario" id="form-perfil" method="POST" action="perfil_usuario.php" autocomplete="off">
            <input type="hidden" name="accion" value="actualizar">

            <div class="grid-2-columnas">
                <div class="grupo-input">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" disabled required>
                </div>
                <div class="grupo-input">
                    <label>Apellidos</label>
                    <input type="text" name="apellidos" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" disabled required>
                </div>

                <div class="grupo-input">
                    <label>País / Región</label>
                    <div class="input-group">
                        <select id="pais" name="pais" disabled required data-pais-guardado="<?php echo htmlspecialchars($usuario['pais']); ?>">                            <option value="" disabled hidden>País/Región</option>
                            <option value="AFG">Afganistán</option><option value="ALB">Albania</option><option value="DEU">Alemania</option><option value="AND">Andorra</option><option value="AGO">Angola</option><option value="AIA">Anguila</option><option value="ATA">Antártida</option><option value="ATG">Antigua y Barbuda</option><option value="SAU">Arabia Saudí</option><option value="DZA">Argelia</option><option value="ARG">Argentina</option><option value="ARM">Armenia</option><option value="AUS">Australia</option><option value="AUT">Austria</option><option value="AZE">Azerbaiyán</option><option value="BHS">Bahamas</option><option value="BEL">Bélgica</option><option value="BLZ">Belice</option><option value="BOL">Bolivia</option><option value="BRA">Brasil</option><option value="BGR">Bulgaria</option><option value="CAN">Canadá</option><option value="CHL">Chile</option><option value="CHN">China continental</option><option value="COL">Colombia</option><option value="KOR">Corea del Sur</option><option value="CRI">Costa Rica</option><option value="HRV">Croacia</option><option value="CUB">Cuba</option><option value="DNK">Dinamarca</option><option value="ECU">Ecuador</option><option value="EGY">Egipto</option><option value="SLV">El Salvador</option><option value="ARE">Emiratos Árabes Unidos</option><option value="SVK">Eslovaquia</option><option value="SVN">Eslovenia</option><option value="ESP">España</option><option value="USA">Estados Unidos</option><option value="EST">Estonia</option><option value="PHL">Filipinas</option><option value="FIN">Finlandia</option><option value="FRA">Francia</option><option value="GRC">Grecia</option><option value="GTM">Guatemala</option><option value="HND">Honduras</option><option value="IND">India</option><option value="IRL">Irlanda</option><option value="ISL">Islandia</option><option value="ISR">Israel</option><option value="ITA">Italia</option><option value="JAM">Jamaica</option><option value="JPN">Japón</option><option value="MAR">Marruecos</option><option value="MEX">México</option><option value="NIC">Nicaragua</option><option value="NOR">Noruega</option><option value="NZL">Nueva Zelanda</option><option value="PAN">Panamá</option><option value="PRY">Paraguay</option><option value="PER">Perú</option><option value="POL">Polonia</option><option value="PRT">Portugal</option><option value="PRI">Puerto Rico</option><option value="GBR">Reino Unido</option><option value="DOM">República Dominicana</option><option value="RUS">Rusia</option><option value="SWE">Suecia</option><option value="CHE">Suiza</option><option value="TUR">Turquía</option><option value="URY">Uruguay</option><option value="VEN">Venezuela</option>
                        </select>
                    </div>
                </div>

                <div class="grupo-input">
                    <label>Fecha de Nacimiento</label>
                    <div class="grupo-fecha">
                        <select name="dia" disabled required>
                            <option value="">Día</option>
                            <?php
                            for($i=1; $i<=31; $i++){
                                $val = str_pad($i, 2, "0", STR_PAD_LEFT);
                                $sel = ($val === $dia_bd) ? 'selected' : '';
                                echo "<option value='$val' $sel>$i</option>";
                            }
                            ?>
                        </select>
                        <select name="mes" disabled required>
                            <option value="">Mes</option>
                            <?php
                            $meses = [1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr', 5=>'May', 6=>'Jun', 7=>'Jul', 8=>'Ago', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dic'];
                            foreach($meses as $num => $nombre) {
                                $val = str_pad($num, 2, "0", STR_PAD_LEFT);
                                $sel = ($val === $mes_bd) ? 'selected' : '';
                                echo "<option value='$val' $sel>$nombre</option>";
                            }
                            ?>
                        </select>
                        <select name="ano" disabled required>
                            <option value="">Año</option>
                            <?php
                            $ano_actual = date('Y');
                            for($i = $ano_actual; $i >= 1930; $i--){
                                $sel = ((string)$i === $ano_bd) ? 'selected' : '';
                                echo "<option value='$i' $sel>$i</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <hr class="separador">

            <!-- APARTADO PARA EL CAMBIO DE EMAIL -->
            <div class="seccion-email">
                <h3>Información de Contacto</h3>
                <p class="email-descripcion">Este es el correo que usas para iniciar sesión y recibir notificaciones.</p>
                <div class="grupo-input">
                    <label>Dirección de Correo Electrónico</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled required class="input-email-destacado">
                </div>
            </div>

        </form>
    </div>
</main>

</body>
</html>