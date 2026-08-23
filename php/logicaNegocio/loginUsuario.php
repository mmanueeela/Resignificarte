<?php
if (!defined('ACCESO_PERMITIDO')) {
    die('Acceso directo denegado');
}

function verificarLogin($conexion, $email, $password_ingresada) {

    // 1. Buscamos al usuario uniendo ambas tablas (AÑADIMOS u.es_admin)
    $consulta = "SELECT u.id, u.nombre, u.es_admin, c.password, c.metodo_registro 
                 FROM usuarios_credenciales c
                 JOIN usuarios u ON c.usuario_id = u.id
                 WHERE c.email = ?";
    $stmt = $conexion->prepare($consulta);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        if ($usuario['metodo_registro'] === 'google') {
            $stmt->close();
            return ["exito" => false, "mensaje" => "Esta cuenta se creó con Google. Por favor, usa el botón de 'Continuar con Google' para entrar."];
        }

        if (password_verify($password_ingresada, $usuario['password'])) {
            $stmt->close();
            // AÑADIMOS "es_admin" AL RETURN DE ÉXITO
            return ["exito" => true, "id" => $usuario['id'], "nombre" => $usuario['nombre'], "es_admin" => $usuario['es_admin']];
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
    // El token de recuerdo ahora vive en 'usuarios_credenciales'
    $consulta = "UPDATE usuarios_credenciales SET remember_token = ? WHERE usuario_id = ?";
    $stmt = $conexion->prepare($consulta);
    $stmt->bind_param("si", $token, $id_usuario);
    $stmt->execute();
    $stmt->close();
}
?>