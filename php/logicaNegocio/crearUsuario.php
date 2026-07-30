<?php
if (!defined('ACCESO_PERMITIDO')) {
    die('Acceso directo denegado');
}

function registrarUsuario($conexion, $nombre, $apellidos, $pais, $fecha_nacimiento, $email, $password) {

    // 1. Verificar si el correo electrónico ya está registrado en credenciales
    $consulta_email = "SELECT id FROM usuarios_credenciales WHERE email = ?";
    $stmt = $conexion->prepare($consulta_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        return ["exito" => false, "mensaje" => "Este correo electrónico ya está registrado."];
    }
    $stmt->close();

    // 2. Encriptar la contraseña
    $password_encriptada = password_hash($password, PASSWORD_DEFAULT);

    // 3. Insertar datos en la tabla `usuarios`
    $consulta_insert_user = "INSERT INTO usuarios (nombre, apellidos, pais, fecha_nacimiento) VALUES (?, ?, ?, ?)";
    $stmt_user = $conexion->prepare($consulta_insert_user);
    $stmt_user->bind_param("ssss", $nombre, $apellidos, $pais, $fecha_nacimiento);

    if (!$stmt_user->execute()) {
        $error = $conexion->error;
        $stmt_user->close();
        return ["exito" => false, "mensaje" => "Error al guardar el usuario: " . $error];
    }

    $nuevo_id = $stmt_user->insert_id;
    $stmt_user->close();

    // 4. Insertar datos en la tabla `usuarios_credenciales` usando el ID anterior
    $consulta_insert_cred = "INSERT INTO usuarios_credenciales (usuario_id, email, password) VALUES (?, ?, ?)";
    $stmt_cred = $conexion->prepare($consulta_insert_cred);
    $stmt_cred->bind_param("iss", $nuevo_id, $email, $password_encriptada);

    if ($stmt_cred->execute()) {
        $stmt_cred->close();
        return ["exito" => true, "mensaje" => "Registro completado con éxito.", "id" => $nuevo_id];
    } else {
        $error = $conexion->error;
        $stmt_cred->close();
        return ["exito" => false, "mensaje" => "Error al guardar credenciales: " . $error];
    }
}
?>