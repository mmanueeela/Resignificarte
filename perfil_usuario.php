<?php
require_once 'php/logicaNegocio/verificar_sesion.php';
require_once 'php/conexion.php';
require_once 'php/logicaNegocio/datos_perfil_usuario.php';

// Proteger la página
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// --- PROCESAR LA ACTUALIZACIÓN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $actualizado = actualizarPerfilUsuario($conexion, $usuario_id, $_POST, isset($_FILES['nueva_foto']) ? $_FILES['nueva_foto'] : null);
    if ($actualizado) {
        $_SESSION['usuario_nombre'] = $_POST['nombre'];
        header("Location: perfil_usuario.php?actualizado=1");
        exit();
    }
}
// ---------------------------------

$usuario = obtenerDatosUsuario($conexion, $usuario_id);

$fecha_partes = explode('-', $usuario['fecha_nacimiento']);
$ano_bd = isset($fecha_partes[0]) ? $fecha_partes[0] : '';
$mes_bd = isset($fecha_partes[1]) ? $fecha_partes[1] : '';
$dia_bd = isset($fecha_partes[2]) ? $fecha_partes[2] : '';

$foto_bd = isset($usuario['foto_perfil']) ? trim($usuario['foto_perfil']) : '';
$ruta_foto = (empty($foto_bd) || strtolower($foto_bd) === 'null') ? 'src/iconos/usuario.png' : $foto_bd;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Editar Perfil - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/perfil_2.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="js/perfil_usuario.js" defer></script>
</head>
<body>

<?php if(isset($_GET['actualizado'])): ?>
    <div class="alerta-exito">Perfil guardado correctamente</div>
<?php endif; ?>

<!-- Todo envuelto en un form para poder guardar -->
<form id="form-perfil" method="POST" action="perfil_usuario.php" autocomplete="off" enctype="multipart/form-data">
    <input type="hidden" name="accion" value="actualizar">

    <main>
        <!-- Menú Superior -->
        <div class="menu">
            <a href="homepage.php"><img src="src/iconos/atras.svg" alt="Icono atras"></a>
            <!-- El botón guardar es un submit camuflado de texto -->
            <button type="submit" class="btn-guardar">Guardar</button>
        </div>

        <h2>Editar Perfil</h2>

        <!-- Zona Imagen -->
        <div class="contenedor-imagen-y-editar">
            <!-- El label acciona el input file oculto al tocar el icono -->
            <label for="input-foto" class="icono-editar-wrapper">
                <img src="src/iconos/edit.svg" alt="Icono editar">
            </label>

            <div class="img-mas-texto">
                <img src="<?php echo htmlspecialchars($ruta_foto); ?>" alt="Imagen de perfil" id="avatar-preview" class="avatar-circular">
                <h4>IMAGEN PERFIL</h4>
            </div>

            <!-- Input file oculto -->
            <input type="file" name="nueva_foto" id="input-foto" accept="image/jpeg, image/png, image/webp" style="display: none;">
        </div>

        <!-- Zona Info Usuario -->
        <div class="contenedor-info-usuario">

            <!-- Nombre -->
            <div class="campo-info">
                <h4>NOMBRE</h4>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
            </div>
            <span class="separador"></span>

            <!-- Apellidos -->
            <div class="campo-info">
                <h4>APELLIDOS</h4>
                <input type="text" name="apellidos" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" required>
            </div>
            <span class="separador"></span>

            <!-- País -->
            <div class="campo-info">
                <h4>PAÍS / REGIÓN</h4>
                <select id="pais" name="pais" required>
                    <option value="" disabled hidden>Selecciona tu país</option>
                    <!-- Aquí pones todos tus options de países, pongo unos pocos de ejemplo -->
                    <option value="ESP" <?php echo ($usuario['pais'] === 'ESP') ? 'selected' : ''; ?>>España</option>
                    <option value="MEX" <?php echo ($usuario['pais'] === 'MEX') ? 'selected' : ''; ?>>México</option>
                    <option value="ARG" <?php echo ($usuario['pais'] === 'ARG') ? 'selected' : ''; ?>>Argentina</option>
                    <option value="COL" <?php echo ($usuario['pais'] === 'COL') ? 'selected' : ''; ?>>Colombia</option>
                </select>
            </div>
            <span class="separador"></span>

            <!-- Fecha Nacimiento -->
            <div class="campo-info campo-fecha">
                <h4>FECHA DE NACIMIENTO</h4>
                <div class="selects-fecha">
                    <select name="dia" required>
                        <option value="">Día</option>
                        <?php
                        for($i=1; $i<=31; $i++){
                            $val = str_pad($i, 2, "0", STR_PAD_LEFT);
                            $sel = ($val === $dia_bd) ? 'selected' : '';
                            echo "<option value='$val' $sel>$i</option>";
                        }
                        ?>
                    </select>
                    <select name="mes" required>
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
                    <select name="ano" required>
                        <option value="">Año</option>
                        <?php
                        $ano_actual = date('Y');
                        for($i = $ano_actual; $i >= 1930; $i--){
                            $sel = ((string)$i === $ano_bd) ? 'selected' : '';
                            echo "<option value='$i' $sel>$i</option>";
                        }
                        ?>
                </div>
            </div>
        </div>
        <span class="separador"></span>

        <!-- Email -->
        <div class="campo-info">
            <h4>CORREO ELECTRÓNICO</h4>
            <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
        </div>
        <span class="separador"></span>

        <!-- Teléfono -->
        <div class="campo-info">
            <h4>NÚMERO DE TELÉFONO</h4>
            <input type="tel" name="telefono" value="<?php echo htmlspecialchars(isset($usuario['telefono']) ? $usuario['telefono'] : ''); ?>" placeholder="Opcional">
        </div>
        <span class="separador"></span>

        </div>
    </main>
</form>
</body>
</html>