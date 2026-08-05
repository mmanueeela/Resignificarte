<?php
require_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['token']) ? $_POST['token'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Comprobar campos vacíos
    if (empty($token) || empty($password) || empty($confirm_password)) {
        header("Location: restablecer_contrasena.php?token=" . urlencode($token) . "&error=" . urlencode("Por favor, completa todos los campos."));
        exit();
    }

    // Comprobar requisitos de la contraseña
    if (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[0-9]/', $password)
    ) {
        header("Location: restablecer_contrasena.php?token=" . urlencode($token) . "&error=" . urlencode("La contraseña debe tener al menos 8 caracteres, una letra mayúscula y un número."));
        exit();
    }

    // Comprobar que las contraseñas coinciden
    if ($password !== $confirm_password) {
        header("Location: restablecer_contrasena.php?token=" . urlencode($token) . "&error=" . urlencode("Las contraseñas no coinciden."));
        exit();
    }

    // Verificamos el token y obtenemos el usuario_id y la contraseña actual
    $stmt = $conexion->prepare("SELECT usuario_id, password FROM usuarios_credenciales WHERE reset_token = ? AND reset_expiracion > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();
        $usuario_id = $fila['usuario_id'];
        $password_actual_hash = $fila['password'];
        $stmt->close();

        // Comprobar que la nueva contraseña NO sea igual a la actual
        if (password_verify($password, $password_actual_hash)) {
            header("Location: restablecer_contrasena.php?token=" . urlencode($token) . "&error=" . urlencode("La nueva contraseña no puede ser igual a la contraseña actual."));
            exit();
        }

        // Encriptamos la nueva contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Actualizamos la contraseña y eliminamos el token
        $update = $conexion->prepare("UPDATE usuarios_credenciales SET password = ?, reset_token = NULL, reset_expiracion = NULL WHERE usuario_id = ?");
        $update->bind_param("si", $password_hash, $usuario_id);

        if ($update->execute()) {
            $update->close();

            // Redirigir al login con mensaje de éxito
            header("Location: ../login.php?exito=" . urlencode("Contraseña actualizada con éxito. Ya puedes iniciar sesión."));
            exit();
        } else {
            header("Location: restablecer_contrasena.php?token=" . urlencode($token) . "&error=" . urlencode("Error al actualizar la contraseña en la base de datos."));
            exit();
        }
    } else {
        header("Location: ../login.php?error=" . urlencode("Token inválido o expirado."));
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}
?>