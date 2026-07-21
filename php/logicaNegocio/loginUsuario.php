<?php
if (!defined('ACCESO_PERMITIDO')) {
    die('Acceso directo denegado');
}

function verificarLogin($conexion, $email, $password_ingresada) {

    // 1. Buscamos al usuario, pidiendo también su metodo_registro
    $consulta = "SELECT id, nombre, password, metodo_registro FROM usuarios WHERE email = ?";
    $stmt = $conexion->prepare($consulta);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // --- NUEVO: COMPROBAR SI ES USUARIO DE GOOGLE ---
        if ($usuario['metodo_registro'] === 'google') {
            $stmt->close();
            return ["exito" => false, "mensaje" => "Esta cuenta se creó con Google. Por favor, usa el botón de 'Continuar con Google' para entrar."];
        }
        // ------------------------------------------------

        // 3. Comparamos la contraseña normal
        if (password_verify($password_ingresada, $usuario['password'])) {
            $stmt->close();
            return ["exito" => true, "id" => $usuario['id'], "nombre" => $usuario['nombre']];
        } else {
            $stmt->close();
            return ["exito" => false, "mensaje" => "La contraseña es incorrecta."];
        }
    } else {
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