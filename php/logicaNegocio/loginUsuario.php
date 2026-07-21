<?php
if (!defined('ACCESO_PERMITIDO')) {
    die('Acceso directo denegado');
}

function verificarLogin($conexion, $email, $password_ingresada) {

    // 1. Buscar al usuario por su email
    $consulta = "SELECT id, nombre, password FROM usuarios WHERE email = ?";
    $stmt = $conexion->prepare($consulta);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // 2. Si el correo existe en la base de datos
    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // 3. Comparamos la contraseña encriptada con la que ha escrito el usuario
        if (password_verify($password_ingresada, $usuario['password'])) {
            $stmt->close();
            // ¡Éxito! Devolvemos los datos del usuario
            return ["exito" => true, "id" => $usuario['id'], "nombre" => $usuario['nombre']];
        } else {
            $stmt->close();
            return ["exito" => false, "mensaje" => "La contraseña es incorrecta."];
        }
    } else {
        // El correo no existe
        $stmt->close();
        return ["exito" => false, "mensaje" => "No existe ninguna cuenta con este email."];
    }
}

function guardarTokenRecuerdame($conexion, $id_usuario, $token) {
    $consulta = "UPDATE usuarios SET remember_token = ? WHERE id = ?";
    $stmt = $conexion->prepare($consulta);
    $stmt->bind_param("si", $token, $id_usuario);
    $stmt->execute();
    $stmt->close();
}
?>