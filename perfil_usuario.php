<?php
require_once 'php/logicaNegocio/verificar_sesion.php';
require_once 'php/conexion.php';
require_once 'php/logicaNegocio/datos_perfil_usuario.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $actualizado = actualizarPerfilUsuario($conexion, $usuario_id, $_POST, isset($_FILES['nueva_foto']) ? $_FILES['nueva_foto'] : null);
    if ($actualizado) {
        $_SESSION['usuario_nombre'] = $_POST['nombre'];
        header("Location: perfil_usuario.php?actualizado=1");
        exit();
    }
}

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
    <title>Mi Perfil - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/perfil_usuario.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="js/perfil_usuario.js" defer></script>
</head>
<body>

<main>
    <?php if(isset($_GET['actualizado'])): ?>
        <div class="alerta-exito">Perfil guardado correctamente</div>
    <?php endif; ?>

    <form id="form-perfil" class="perfil-container" method="POST" action="perfil_usuario.php" autocomplete="off" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="actualizar">

        <!-- Botón volver atrás -->
        <a href="javascript:history.back()" class="btn-volver-atras" title="Volver atrás">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </a>

        <div class="menu">
            <!-- Este div vacío mantiene el logo perfectamente centrado con Flexbox -->
            <div style="flex: 1;"></div>

            <a href="<?= (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1) ? 'homepage_administrador.php' : 'homepage.php' ?>" class="logo-perfil">
                <img src="src/logo/logo_con_inifito.png" alt="Logo Resignificarte">
            </a>

            <button type="button" id="btn-editar-perfil" class="btn-lapiz" title="Editar Perfil">
                <img src="src/iconos/edit.svg" alt="Editar" class="icono-blanco">
            </button>
        </div>

        <div class="contenedor-imagen-y-editar">
            <div class="icono-editar-wrapper">
                <img src="src/iconos/edit.svg" alt="Editar foto" class="icono-blanco">
            </div>
            <div class="avatar-wrapper">
                <label for="input-foto" id="label-foto">
                    <img src="<?php echo htmlspecialchars($ruta_foto); ?>" alt="Imagen de perfil" id="avatar-preview" class="avatar-circular">
                </label>
            </div>
            <h4>FOTO DE PERFIL</h4>
            <input type="file" name="nueva_foto" id="input-foto" accept="image/jpeg, image/png, image/webp" disabled style="display: none;">
        </div>

        <div class="contenedor-info-usuario">

            <!-- 1. NOMBRE -->
            <div class="campo-info">
                <div class="campo-contenido">
                    <h4>NOMBRE</h4>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" disabled required>
                </div>
                <svg class="icono-chevron" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>

            <!-- 2. APELLIDOS -->
            <div class="campo-info">
                <div class="campo-contenido">
                    <h4>APELLIDOS</h4>
                    <input type="text" name="apellidos" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" disabled required>
                </div>
                <svg class="icono-chevron" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>

            <!-- 3. EMAIL -->
            <div class="campo-info campo-bloqueado">
                <div class="campo-contenido">
                    <h4>CORREO ELECTRÓNICO</h4>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled>
                </div>
            </div>

            <!-- 4. TELÉFONO -->
            <div class="campo-info">
                <div class="campo-contenido">
                    <h4>TELÉFONO</h4>
                    <input type="tel" name="telefono" value="<?php echo htmlspecialchars(isset($usuario['telefono']) ? $usuario['telefono'] : ''); ?>" disabled placeholder="Añadir teléfono">
                </div>
                <svg class="icono-chevron" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>

            <!-- 5. FECHA DE NACIMIENTO -->
            <div class="campo-info fecha-info">
                <div class="campo-contenido" style="width: 100%;">
                    <h4>FECHA DE NACIMIENTO</h4>
                    <div class="selects-fecha">
                        <select id="dia" name="dia" disabled required>
                            <option value="" disabled hidden>Día</option>
                            <?php
                            for($i=1; $i<=31; $i++){
                                $val = str_pad($i, 2, "0", STR_PAD_LEFT);
                                $sel = ($val === $dia_bd) ? 'selected' : '';
                                echo "<option value='$val' $sel>$i</option>";
                            }
                            ?>
                        </select>
                        <select id="mes" name="mes" disabled required>
                            <option value="" disabled hidden>Mes</option>
                            <?php
                            $meses = [1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr', 5=>'May', 6=>'Jun', 7=>'Jul', 8=>'Ago', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dic'];
                            foreach($meses as $num => $nom) {
                                $val = str_pad($num, 2, "0", STR_PAD_LEFT);
                                $sel = ($val === $mes_bd) ? 'selected' : '';
                                echo "<option value='$val' $sel>$nom</option>";
                            }
                            ?>
                        </select>
                        <select id="ano" name="ano" disabled required>
                            <option value="" disabled hidden>Año</option>
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

            <!-- 6. PAÍS -->
            <div class="campo-info">
                <div class="campo-contenido">
                    <h4>UBICACIÓN</h4>
                    <select id="pais" name="pais" disabled required>
                        <option value="" disabled hidden>País/Región</option>
                        <?php
                        $paises = [
                            'AFG'=>'Afganistán','ALB'=>'Albania','DEU'=>'Alemania','AND'=>'Andorra','AGO'=>'Angola','AIA'=>'Anguila','ATA'=>'Antártida','ATG'=>'Antigua y Barbuda','SAU'=>'Arabia Saudí','IOT'=>'Archipiélago de Chagos','DZA'=>'Argelia','ARG'=>'Argentina','ARM'=>'Armenia','ABW'=>'Aruba','AUS'=>'Australia','AUT'=>'Austria','AZE'=>'Azerbaiyán','BHS'=>'Bahamas','BGD'=>'Bangladés','BRB'=>'Barbados','BHR'=>'Baréin','BEL'=>'Bélgica','BLZ'=>'Belice','BEN'=>'Benín','BMU'=>'Bermudas','BLR'=>'Bielorrusia','BOL'=>'Bolivia','BIH'=>'Bosnia y Herzegovina','BWA'=>'Botsuana','BRA'=>'Brasil','BRN'=>'Brunéi','BGR'=>'Bulgaria','BFA'=>'Burkina Faso','BDI'=>'Burundi','BTN'=>'Bután','CPV'=>'Cabo Verde','KHM'=>'Camboya','CMR'=>'Camerún','CAN'=>'Canadá','BES'=>'Caribe Neerlandés','QAT'=>'Catar','TCD'=>'Chad','CZE'=>'Chequia','CHL'=>'Chile','CHN'=>'China continental','CYP'=>'Chipre','VAT'=>'Ciudad del Vaticano','COL'=>'Colombia','COM'=>'Comoras','KOR'=>'Corea del Sur','CIV'=>'Costa de Marfil','CRI'=>'Costa Rica','HRV'=>'Croacia','CUW'=>'Curazao','DNK'=>'Dinamarca','DMA'=>'Dominica','ECU'=>'Ecuador','EGY'=>'Egipto','SLV'=>'El Salvador','ARE'=>'Emiratos Árabes Unidos','ERI'=>'Eritrea','SVK'=>'Eslovaquia','SVN'=>'Eslovenia','ESP'=>'España','USA'=>'Estados Unidos','EST'=>'Estonia','SWZ'=>'Esuatini','ETH'=>'Etiopía','PHL'=>'Filipinas','FIN'=>'Finlandia','FJI'=>'Fiyi','FRA'=>'Francia','GAB'=>'Gabón','GMB'=>'Gambia','GEO'=>'Georgia','GHA'=>'Ghana','GIB'=>'Gibraltar','GRD'=>'Granada','GRC'=>'Grecia','GRL'=>'Groenlandia','GLP'=>'Guadalupe','GUM'=>'Guam','GTM'=>'Guatemala','GUF'=>'Guayana Francesa','GGY'=>'Guernsey','GIN'=>'Guinea','GNB'=>'Guinea-Bissau','GNQ'=>'Guinea Ecuatorial','GUY'=>'Guyana','HTI'=>'Haití','HND'=>'Honduras','HKG'=>'Hong Kong','HUN'=>'Hungría','IND'=>'India','IDN'=>'Indonesia','IRQ'=>'Irak','IRL'=>'Irlanda','BVT'=>'Isla Bouvet','IMN'=>'Isla de Man','CXR'=>'Isla de Navidad','ISL'=>'Islandia','NFK'=>'Isla Norfolk','ALA'=>'Islas Åland','CYM'=>'Islas Caimán','CCK'=>'Islas Cocos','COK'=>'Islas Cook','FRO'=>'Islas Feroe','SGS'=>'Islas Georgia del Sur y Sandwich del Sur','FLK'=>'Islas Malvinas','MNP'=>'Islas Marianas del Norte','MHL'=>'Islas Marshall','UMI'=>'Islas menores alejadas de EE. UU.','SLB'=>'Islas Salomón','TCA'=>'Islas Turcas y Caicos','VGB'=>'Islas Vírgenes Británicas','VIR'=>'Islas Vírgenes de EE. UU.','ISR'=>'Israel','ITA'=>'Italia','JAM'=>'Jamaica','JPN'=>'Japón','JEY'=>'Jersey','JOR'=>'Jordania','KAZ'=>'Kazajistán','KEN'=>'Kenia','KGZ'=>'Kirguistán','KIR'=>'Kiribati','XKS'=>'Kosovo','KWT'=>'Kuwait','LAO'=>'Laos','LSO'=>'Lesoto','LVA'=>'Letonia','LBN'=>'Líbano','LBR'=>'Liberia','LBY'=>'Libia','LIE'=>'Liechtenstein','LTU'=>'Lituania','LUX'=>'Luxemburgo','MAC'=>'Macao','MKD'=>'Macedonia del Norte','MDG'=>'Madagascar','MYS'=>'Malasia','MWI'=>'Malaui','MDV'=>'Maldivas','MLI'=>'Mali','MLT'=>'Malta','MAR'=>'Marruecos','MTQ'=>'Martinica','MUS'=>'Mauricio','MRT'=>'Mauritania','MYT'=>'Mayotte','MEX'=>'México','FSM'=>'Micronesia','MDA'=>'Moldavia','MCO'=>'Mónaco','MNG'=>'Mongolia','MNE'=>'Montenegro','MSR'=>'Montserrat','MOZ'=>'Mozambique','MMR'=>'Myanmar','NAM'=>'Namibia','NRU'=>'Nauru','NPL'=>'Nepal','NIC'=>'Nicaragua','NER'=>'Níger','NGA'=>'Nigeria','NIU'=>'Niue','NOR'=>'Noruega','NCL'=>'Nueva Caledonia','NZL'=>'Nueva Zelanda','OMN'=>'Omán','NLD'=>'Países Bajos','PAK'=>'Pakistán','PLW'=>'Palaos','PAN'=>'Panamá','PNG'=>'Papúa Nueva Guinea','PRY'=>'Paraguay','PER'=>'Perú','PCN'=>'Pitcairn','PYF'=>'Polinesia Francesa','POL'=>'Polonia','PRT'=>'Portugal','PRI'=>'Puerto Rico','GBR'=>'Reino Unido','CAF'=>'República Centroafricana','COG'=>'República del Congo','COD'=>'República Democrática del Congo','DOM'=>'República Dominicana','REU'=>'Reunión','RWA'=>'Ruanda','ROU'=>'Rumanía','RUS'=>'Rusia','ESH'=>'Sáhara Occidental','WSM'=>'Samoa','ASM'=>'Samoa Americana','BLM'=>'San Bartolomé','KNA'=>'San Cristóbal y Nieves','SMR'=>'San Marino','MAF'=>'San Martín','SPM'=>'San Pedro y Miquelón','SHN'=>'Santa Elena','LCA'=>'Santa Lucía','STP'=>'Santo Tomé y Príncipe','VCT'=>'San Vicente y las Granadinas','SEN'=>'Senegal','SRB'=>'Serbia','SYC'=>'Seychelles','SLE'=>'Sierra Leona','SGP'=>'Singapur','SXM'=>'Sint Maarten','SOM'=>'Somalia','LKA'=>'Sri Lanka','ZAF'=>'Sudáfrica','SDN'=>'Sudán','SSD'=>'Sudán del Sur','SWE'=>'Suecia','CHE'=>'Suiza','SUR'=>'Surinam','SJM'=>'Svalbard y Jan Mayen','THA'=>'Tailandia','TWN'=>'Taiwán','TZA'=>'Tanzania','TJK'=>'Tayikistán','ATF'=>'Territorios Australes Franceses','PSE'=>'Territorios Palestinos','TLS'=>'Timor Oriental','TGO'=>'Togo','TKL'=>'Tokelau','TON'=>'Tonga','TTO'=>'Trinidad y Tobago','TUN'=>'Túnez','TKM'=>'Turkmenistán','TUR'=>'Turquía','TUV'=>'Tuvalu','UKR'=>'Ucrania','UGA'=>'Uganda','URY'=>'Uruguay','UZB'=>'Uzbekistán','VUT'=>'Vanuatu','VEN'=>'Venezuela','VNM'=>'Vietnam','WLF'=>'Wallis y Futuna','YEM'=>'Yemen','DJI'=>'Yibuti','ZMB'=>'Zambia','ZWE'=>'Zimbabue'
                        ];
                        foreach ($paises as $codigo => $nombre_pais) {
                            $sel = ($usuario['pais'] === $codigo) ? 'selected' : '';
                            echo "<option value=\"$codigo\" $sel>$nombre_pais</option>";
                        }
                        ?>
                    </select>
                </div>
                <svg class="icono-chevron" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>

            <!-- Botones de Acción -->
            <div class="acciones-edicion">
                <button type="button" id="btn-cancelar" class="btn-circulo btn-rojo" title="Cancelar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
                <button type="submit" id="btn-guardar" class="btn-circulo btn-verde" title="Guardar cambios">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
            </div>

        </div>
    </form>
</main>
</body>
</html>