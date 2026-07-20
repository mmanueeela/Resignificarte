<?php
// Evita que este archivo se pueda ejecutar directamente desde el navegador
if (!defined('ACCESO_PERMITIDO')) {
    die('Acceso directo denegado');
}

function registrarUsuario($conexion, $nombre, $apellidos, $pais, $fecha_nacimiento, $email, $password) {

    // 1. Verificar si el correo electrónico ya está registrado
    $consulta_email = "SELECT id FROM usuarios WHERE email = ?";
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

    // 3. Insertar el nuevo usuario
    $consulta_insert = "INSERT INTO usuarios (nombre, apellidos, pais, fecha_nacimiento, email, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conexion->prepare($consulta_insert);

    $stmt_insert->bind_param("ssssss", $nombre, $apellidos, $pais, $fecha_nacimiento, $email, $password_encriptada);

    if ($stmt_insert->execute()) {
        $stmt_insert->close();
        return ["exito" => true, "mensaje" => "Registro completado con éxito."];
    } else {
        $error = $conexion->error;
        $stmt_insert->close();
        return ["exito" => false, "mensaje" => "Error interno al guardar: " . $error];
    }
}
?>