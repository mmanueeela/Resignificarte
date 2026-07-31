<?php
require_once 'conexion.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
$valido = false;
$mensaje_error = "";

if (!empty($token)) {
    // Buscamos si existe un usuario con este token y si la fecha de expiración es mayor a la actual
    $stmt = $conexion->prepare("SELECT usuario_id FROM usuarios_credenciales WHERE reset_token = ? AND reset_expiracion > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $valido = true;
    } else {
        $mensaje_error = "El enlace de recuperación no es válido o ha expirado.";
    }
    $stmt->close();
} else {
    $mensaje_error = "No se ha proporcionado ningún token de recuperación.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Resignificarte</title>
    <link rel="stylesheet" href="../css/estilos_comunes.css">
    <link rel="stylesheet" href="../css/restablecer_contrasena.css">
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    <script src="../js/retardo_cambio_pagina.js" defer></script>
</head>
<body>

<main>
    <div class="forgot-container">

        <div class="logo-container" style="text-align: center; margin-bottom: 20px;">
            <a href="../homepage.php"><img src="../src/logo/logo_con_inifito.png" alt="Logo"></a>
        </div>

        <h2>Nueva Contraseña</h2>
        <p>Introduce tu nueva clave de acceso.</p>

        <?php if ($valido): ?>
            <form action="procesar_nueva_contrasena.php" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="input-group">
                    <img src="../src/iconos/candado_login.png" alt="Icono candado" class="input-icon">
                    <input type="password" id="password" name="password" placeholder="Nueva contraseña" required minlength="8">
                </div>

                <div class="input-group">
                    <img src="../src/iconos/candado_login.png" alt="Icono candado" class="input-icon">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite la contraseña" required minlength="8">
                </div>

                <button type="submit" class="btn-login">Actualizar contraseña</button>
            </form>
        <?php else: ?>
            <div class="message error">
                <?php echo $mensaje_error; ?>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="../login.php" style="color: white; text-decoration: underline; font-family: Montserrat, sans-serif;">Volver al inicio de sesión</a>
            </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>