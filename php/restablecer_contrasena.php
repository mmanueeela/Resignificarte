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
    <title>Restablecer Contraseña - Resignificarte</title>
    <link rel="stylesheet" href="../css/estilos_comunes.css">
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    <script src="../js/retardo_cambio_pagina.js" defer></script>
</head>
<body>
<div class="container-auth">
    <h2>Nueva Contraseña</h2>

    <?php if ($valido): ?>
        <form action="procesar_nueva_contrasena.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <div class="form-group">
                <label for="password">Introduce tu nueva contraseña:</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>

            <div class="form-group">
                <label for="confirm_password">Repite la contraseña:</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
            </div>

            <button type="submit" class="btn-submit">Actualizar contraseña</button>
        </form>
    <?php else: ?>
        <div class="message error" style="display:block;">
            <?php echo $mensaje_error; ?>
        </div>
        <p><a href="../login.php">Volver al inicio de sesión</a></p>
    <?php endif; ?>
</div>
</body>
</html>