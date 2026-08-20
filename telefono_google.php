<?php
session_start();

// Comprobamos que el usuario realmente viene del registro con Google
if (!isset($_SESSION['registro_google'])) {
    header("Location: login.php");
    exit();
}

$datos_google = $_SESSION['registro_google'];
$nombre = htmlspecialchars($datos_google['nombre']);

// Determinamos qué datos faltan por rellenar
$falta_apellidos = empty(trim($datos_google['apellidos']));
$falta_telefono = true; // Google no suele dar el teléfono
$falta_pais = true;     // Google no da el país por defecto
$falta_fecha = true;    // Google no da la fecha por defecto
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Completar registro - Resignificarte</title>
    <link rel="stylesheet" href="css/estilos_comunes.css">
    <link rel="stylesheet" href="css/telefono_google.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
<main>
    <div class="forgot-container">
        <!-- Logo -->
        <div class="logo-container">
            <a href="homepage.php">
                <img src="src/logo/logo_con_inifito.png" alt="Logo de Resignificarte">
            </a>
        </div>

        <h2>¡Bienvenido, <?= $nombre ?>!</h2>
        <p>Para terminar tu registro necesitamos algunos datos adicionales.</p>

        <form action="php/procesar_registro_google.php" method="POST" id="form-completar-google" novalidate>

            <!-- APELLIDOS (Solo si Google no nos los dio) -->
            <?php if ($falta_apellidos): ?>
                <div class="input-group">
                    <img src="src/iconos/usuario.png" alt="Icono usuario" class="input-icon">
                    <input type="text" name="apellidos" placeholder="Apellidos" required>
                </div>
            <?php endif; ?>

            <!-- TELÉFONO (Siempre falta) -->
            <?php if ($falta_telefono): ?>
                <div class="input-group">
                    <img src="src/iconos/telefono.png" alt="Icono teléfono" class="input-icon">
                    <input type="tel" name="telefono" placeholder="Teléfono" autocomplete="tel" maxlength="20" required>
                </div>
            <?php endif; ?>

            <!-- PAÍS (Siempre falta) -->
            <?php if ($falta_pais): ?>
                <div class="input-group">
                    <img src="src/iconos/usuario.png" alt="Icono país" class="input-icon" onerror="this.style.opacity='0'">
                    <select name="pais" required class="select-transparente">
                        <option value="" disabled selected hidden>País/Región</option>
                        <option value="ESP">España</option>
                        <option value="MEX">México</option>
                        <option value="ARG">Argentina</option>
                        <option value="COL">Colombia</option>
                        <!-- Añade el resto de países que necesites -->
                    </select>
                </div>
            <?php endif; ?>

            <!-- FECHA DE NACIMIENTO (Siempre falta) -->
            <?php if ($falta_fecha): ?>
                <div class="input-group" style="gap: 10px;">
                    <img src="src/iconos/usuario.png" alt="Icono fecha" class="input-icon" onerror="this.style.opacity='0'">

                    <select name="dia" required class="select-transparente" style="flex: 1;">
                        <option value="" disabled selected hidden>Día</option>
                        <?php for($i=1; $i<=31; $i++) echo "<option value='".str_pad($i,2,'0',STR_PAD_LEFT)."'>$i</option>"; ?>
                    </select>

                    <select name="mes" required class="select-transparente" style="flex: 1;">
                        <option value="" disabled selected hidden>Mes</option>
                        <?php
                        $meses = [1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr', 5=>'May', 6=>'Jun', 7=>'Jul', 8=>'Ago', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dic'];
                        foreach($meses as $num => $nom) echo "<option value='".str_pad($num,2,'0',STR_PAD_LEFT)."'>$nom</option>";
                        ?>
                    </select>

                    <select name="ano" required class="select-transparente" style="flex: 1;">
                        <option value="" disabled selected hidden>Año</option>
                        <?php for($i=date('Y'); $i>=1930; $i--) echo "<option value='$i'>$i</option>"; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="message error" style="display: block;">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <!-- Botón -->
            <button type="submit" class="btn-login">CONTINUAR</button>
        </form>

        <div class="registro-link">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
        </div>
    </div>
</main>
</body>
</html>