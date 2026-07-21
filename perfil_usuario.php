<?php
require_once 'php/verificar_sesion.php';
require_once 'php/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// --- LÓGICA PARA ACTUALIZAR LOS DATOS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $nuevo_nombre = $_POST['nombre'];
    $nuevo_apellidos = $_POST['apellidos'];
    $nuevo_pais = $_POST['pais'];
    $nuevo_email = $_POST['email']; // Actualizamos también el email

    // Juntamos el día, mes y año para guardarlo en la base de datos (Formato YYYY-MM-DD)
    $nueva_fecha = $_POST['ano'] . '-' . str_pad($_POST['mes'], 2, "0", STR_PAD_LEFT) . '-' . str_pad($_POST['dia'], 2, "0", STR_PAD_LEFT);

    // Preparamos y ejecutamos la actualización
    $update = $conexion->prepare("UPDATE usuarios SET nombre=?, apellidos=?, pais=?, fecha_nacimiento=?, email=? WHERE id=?");
    $update->bind_param("sssssi", $nuevo_nombre, $nuevo_apellidos, $nuevo_pais, $nueva_fecha, $nuevo_email, $usuario_id);
    $update->execute();

    // Recargamos la página para ver los cambios aplicados
    header("Location: perfil.php?actualizado=1");
    exit();
}
// ----------------------------------------

// Obtener los datos actuales del usuario
$consulta = "SELECT nombre, apellidos, email, pais, fecha_nacimiento FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($consulta);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

// Separar la fecha de nacimiento en partes para los 3 selects
$fecha_partes = explode('-', $usuario['fecha_nacimiento']);
$ano_bd = $fecha_partes[0] ?? '';
$mes_bd = $fecha_partes[1] ?? '';
$dia_bd = $fecha_partes[2] ?? '';
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
                <img src="src/iconos/usuario.png" alt="Avatar del usuario" class="avatar">
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
        <form class="perfil-formulario" id="form-perfil" method="POST" action="perfil.php">
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
                        <select id="pais" name="pais" disabled required>
                            <option value="" disabled hidden>País/Región</option>
                            <option value="AFG">Afganistán</option><option value="ALB">Albania</option><option value="DEU">Alemania</option><option value="AND">Andorra</option><option value="AGO">Angola</option><option value="AIA">Anguila</option><option value="ATA">Antártida</option><option value="ATG">Antigua y Barbuda</option><option value="SAU">Arabia Saudí</option><option value="IOT">Archipiélago de Chagos</option><option value="DZA">Argelia</option><option value="ARG">Argentina</option><option value="ARM">Armenia</option><option value="ABW">Aruba</option><option value="AUS">Australia</option><option value="AUT">Austria</option><option value="AZE">Azerbaiyán</option><option value="BHS">Bahamas</option><option value="BGD">Bangladés</option><option value="BRB">Barbados</option><option value="BHR">Baréin</option><option value="BEL">Bélgica</option><option value="BLZ">Belice</option><option value="BEN">Benín</option><option value="BMU">Bermudas</option><option value="BLR">Bielorrusia</option><option value="BOL">Bolivia</option><option value="BIH">Bosnia y Herzegovina</option><option value="BWA">Botsuana</option><option value="BRA">Brasil</option><option value="BRN">Brunéi</option><option value="BGR">Bulgaria</option><option value="BFA">Burkina Faso</option><option value="BDI">Burundi</option><option value="BTN">Bután</option><option value="CPV">Cabo Verde</option><option value="KHM">Camboya</option><option value="CMR">Camerún</option><option value="CAN">Canadá</option><option value="BES">Caribe Neerlandés</option><option value="QAT">Catar</option><option value="TCD">Chad</option><option value="CZE">Chequia</option><option value="CHL">Chile</option><option value="CHN">China continental</option><option value="CYP">Chipre</option><option value="VAT">Ciudad del Vaticano</option><option value="COL">Colombia</option><option value="COM">Comoras</option><option value="KOR">Corea del Sur</option><option value="CIV">Costa de Marfil</option><option value="CRI">Costa Rica</option><option value="HRV">Croacia</option><option value="CUW">Curazao</option><option value="DNK">Dinamarca</option><option value="DMA">Dominica</option><option value="ECU">Ecuador</option><option value="EGY">Egipto</option><option value="SLV">El Salvador</option><option value="ARE">Emiratos Árabes Unidos</option><option value="ERI">Eritrea</option><option value="SVK">Eslovaquia</option><option value="SVN">Eslovenia</option><option value="ESP">España</option><option value="USA">Estados Unidos</option><option value="EST">Estonia</option><option value="SWZ">Esuatini</option><option value="ETH">Etiopía</option><option value="PHL">Filipinas</option><option value="FIN">Finlandia</option><option value="FJI">Fiyi</option><option value="FRA">Francia</option><option value="GAB">Gabón</option><option value="GMB">Gambia</option><option value="GEO">Georgia</option><option value="GHA">Ghana</option><option value="GIB">Gibraltar</option><option value="GRD">Granada</option><option value="GRC">Grecia</option><option value="GRL">Groenlandia</option><option value="GLP">Guadalupe</option><option value="GUM">Guam</option><option value="GTM">Guatemala</option><option value="GUF">Guayana Francesa</option><option value="GGY">Guernsey</option><option value="GIN">Guinea</option><option value="GNB">Guinea-Bissau</option><option value="GNQ">Guinea Ecuatorial</option><option value="GUY">Guyana</option><option value="HTI">Haití</option><option value="HND">Honduras</option><option value="HKG">Hong Kong</option><option value="HUN">Hungría</option><option value="IND">India</option><option value="IDN">Indonesia</option><option value="IRQ">Irak</option><option value="IRL">Irlanda</option><option value="BVT">Isla Bouvet</option><option value="IMN">Isla de Man</option><option value="CXR">Isla de Navidad</option><option value="ISL">Islandia</option><option value="NFK">Isla Norfolk</option><option value="ALA">Islas Åland</option><option value="CYM">Islas Caimán</option><option value="CCK">Islas Cocos</option><option value="COK">Islas Cook</option><option value="FRO">Islas Feroe</option><option value="SGS">Islas Georgia del Sur y Sandwich del Sur</option><option value="FLK">Islas Malvinas</option><option value="MNP">Islas Marianas del Norte</option><option value="MHL">Islas Marshall</option><option value="UMI">Islas menores alejadas de EE. UU.</option><option value="SLB">Islas Salomón</option><option value="TCA">Islas Turcas y Caicos</option><option value="VGB">Islas Vírgenes Británicas</option><option value="VIR">Islas Vírgenes de EE. UU.</option><option value="ISR">Israel</option><option value="ITA">Italia</option><option value="JAM">Jamaica</option><option value="JPN">Japón</option><option value="JEY">Jersey</option><option value="JOR">Jordania</option><option value="KAZ">Kazajistán</option><option value="KEN">Kenia</option><option value="KGZ">Kirguistán</option><option value="KIR">Kiribati</option><option value="XKS">Kosovo</option><option value="KWT">Kuwait</option><option value="LAO">Laos</option><option value="LSO">Lesoto</option><option value="LVA">Letonia</option><option value="LBN">Líbano</option><option value="LBR">Liberia</option><option value="LBY">Libia</option><option value="LIE">Liechtenstein</option><option value="LTU">Lituania</option><option value="LUX">Luxemburgo</option><option value="MAC">Macao</option><option value="MKD">Macedonia del Norte</option><option value="MDG">Madagascar</option><option value="MYS">Malasia</option><option value="MWI">Malaui</option><option value="MDV">Maldivas</option><option value="MLI">Mali</option><option value="MLT">Malta</option><option value="MAR">Marruecos</option><option value="MTQ">Martinica</option><option value="MUS">Mauricio</option><option value="MRT">Mauritania</option><option value="MYT">Mayotte</option><option value="MEX">México</option><option value="FSM">Micronesia</option><option value="MDA">Moldavia</option><option value="MCO">Mónaco</option><option value="MNG">Mongolia</option><option value="MNE">Montenegro</option><option value="MSR">Montserrat</option><option value="MOZ">Mozambique</option><option value="MMR">Myanmar</option><option value="NAM">Namibia</option><option value="NRU">Nauru</option><option value="NPL">Nepal</option><option value="NIC">Nicaragua</option><option value="NER">Níger</option><option value="NGA">Nigeria</option><option value="NIU">Niue</option><option value="NOR">Noruega</option><option value="NCL">Nueva Caledonia</option><option value="NZL">Nueva Zelanda</option><option value="OMN">Omán</option><option value="NLD">Países Bajos</option><option value="PAK">Pakistán</option><option value="PLW">Palaos</option><option value="PAN">Panamá</option><option value="PNG">Papúa Nueva Guinea</option><option value="PRY">Paraguay</option><option value="PER">Perú</option><option value="PCN">Pitcairn</option><option value="PYF">Polinesia Francesa</option><option value="POL">Polonia</option><option value="PRT">Portugal</option><option value="PRI">Puerto Rico</option><option value="GBR">Reino Unido</option><option value="CAF">República Centroafricana</option><option value="COG">República del Congo</option><option value="COD">República Democrática del Congo</option><option value="DOM">República Dominicana</option><option value="REU">Reunión</option><option value="RWA">Ruanda</option><option value="ROU">Rumanía</option><option value="RUS">Rusia</option><option value="ESH">Sáhara Occidental</option><option value="WSM">Samoa</option><option value="ASM">Samoa Americana</option><option value="BLM">San Bartolomé</option><option value="KNA">San Cristóbal y Nieves</option><option value="SMR">San Marino</option><option value="MAF">San Martín</option><option value="SPM">San Pedro y Miquelón</option><option value="SHN">Santa Elena</option><option value="LCA">Santa Lucía</option><option value="STP">Santo Tomé y Príncipe</option><option value="VCT">San Vicente y las Granadinas</option><option value="SEN">Senegal</option><option value="SRB">Serbia</option><option value="SYC">Seychelles</option><option value="SLE">Sierra Leona</option><option value="SGP">Singapur</option><option value="SXM">Sint Maarten</option><option value="SOM">Somalia</option><option value="LKA">Sri Lanka</option><option value="ZAF">Sudáfrica</option><option value="SDN">Sudán</option><option value="SSD">Sudán del Sur</option><option value="SWE">Suecia</option><option value="CHE">Suiza</option><option value="SUR">Surinam</option><option value="SJM">Svalbard y Jan Mayen</option><option value="THA">Tailandia</option><option value="TWN">Taiwán</option><option value="TZA">Tanzania</option><option value="TJK">Tayikistán</option><option value="ATF">Territorios Australes Franceses</option><option value="PSE">Territorios Palestinos</option><option value="TLS">Timor Oriental</option><option value="TGO">Togo</option><option value="TKL">Tokelau</option><option value="TON">Tonga</option><option value="TTO">Trinidad y Tobago</option><option value="TUN">Túnez</option><option value="TKM">Turkmenistán</option><option value="TUR">Turquía</option><option value="TUV">Tuvalu</option><option value="UKR">Ucrania</option><option value="UGA">Uganda</option><option value="URY">Uruguay</option><option value="UZB">Uzbekistán</option><option value="VUT">Vanuatu</option><option value="VEN">Venezuela</option><option value="VNM">Vietnam</option><option value="WLF">Wallis y Futuna</option><option value="YEM">Yemen</option><option value="DJI">Yibuti</option><option value="ZMB">Zambia</option><option value="ZWE">Zimbabue</option>
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