<?php
require_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['token']) ? $_POST['token'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (empty($token) || empty($password) || empty($confirm_password)) {
        die("Por favor, completa todos los campos.");
    }

    if (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[0-9]/', $password)
    ) {
        die("La contraseña debe tener al menos 8 caracteres, una letra mayúscula y un número.");
    }

    if ($password !== $confirm_password) {
        die("Las contraseñas no coinciden.");
    }

    // Verificamos de nuevo el token y obtenemos el usuario_id
    $stmt = $conexion->prepare("SELECT usuario_id FROM usuarios_credenciales WHERE reset_token = ? AND reset_expiracion > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();
        $usuario_id = $fila['usuario_id'];
        $stmt->close();

        // Encriptamos la nueva contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Actualizamos la contraseña y BORRAMOS el token y su expiración para que sea de un solo uso
        $update = $conexion->prepare("UPDATE usuarios_credenciales SET password = ?, reset_token = NULL, reset_expiracion = NULL WHERE usuario_id = ?");
        $update->bind_param("si", $password_hash, $usuario_id);

        if ($update->execute()) {
            $update->close();
            // Redirigir al login con éxito
            header("Location: ../login.php?exito=" . urlencode("Contraseña actualizada con éxito. Ya puedes iniciar sesión."));
            exit();
        } else {
            die("Error al actualizar la contraseña en la base de datos.");
        }

    } else {
        die("Token inválido o expirado.");
    }
} else {
    header("Location: ../login.php");
    exit();
}