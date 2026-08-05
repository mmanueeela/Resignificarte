<?php
function obtenerDatosUsuario($conexion, $usuario_id) {
    // Unimos las dos tablas para recoger toda la información del perfil
    $consulta = "SELECT u.nombre, u.apellidos, c.email, c.telefono, u.pais, u.fecha_nacimiento, u.foto_perfil FROM usuarios u JOIN usuarios_credenciales c ON u.id = c.usuario_id WHERE u.id = ?";

    $stmt = $conexion->prepare($consulta);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
    $stmt->close();

    return $usuario;
}

function actualizarPerfilUsuario($conexion, $usuario_id, $datos_post, $archivo_foto = null) {
    $nuevo_nombre = $datos_post['nombre'];
    $nuevo_apellidos = $datos_post['apellidos'];
    $nuevo_pais = $datos_post['pais'];

    // El email se mantiene en la función por compatibilidad,
    // pero NO se modifica desde el perfil.
    $nuevo_telefono = isset($datos_post['telefono']) ? trim($datos_post['telefono']) : '';

    $nueva_fecha = $datos_post['ano'] . '-' . str_pad($datos_post['mes'], 2, "0", STR_PAD_LEFT) . '-' . str_pad($datos_post['dia'], 2, "0", STR_PAD_LEFT);

    $ruta_foto_final = null;

    // ==========================================
    // SUBIDA DE FOTO
    // ==========================================
    if ($archivo_foto && $archivo_foto['error'] === UPLOAD_ERR_OK) {
        $permitidos = ['image/jpeg', 'image/png', 'image/webp'];

        if (in_array($archivo_foto['type'], $permitidos) && $archivo_foto['size'] <= 2097152) {
            $extension = pathinfo($archivo_foto['name'], PATHINFO_EXTENSION);
            $nombre_archivo = "user_" . $usuario_id . "_" . time() . "." . $extension;
            $directorio_destino = __DIR__ . '/../../src/uploads/perfiles/';

            if (!file_exists($directorio_destino)) {
                mkdir($directorio_destino, 0777, true);
            }

            $ruta_fisica = $directorio_destino . $nombre_archivo;
            $ruta_db = 'src/uploads/perfiles/' . $nombre_archivo;

            if (move_uploaded_file($archivo_foto['tmp_name'], $ruta_fisica)) {
                $ruta_foto_final = $ruta_db;
            }
        }
    }

    // ==========================================
    // ACTUALIZAR TABLA USUARIOS
    // ==========================================
    if ($ruta_foto_final) {
        $update_user = $conexion->prepare("UPDATE usuarios SET nombre = ?, apellidos = ?, pais = ?, fecha_nacimiento = ?, foto_perfil = ? WHERE id = ?");
        $update_user->bind_param("sssssi", $nuevo_nombre, $nuevo_apellidos, $nuevo_pais, $nueva_fecha, $ruta_foto_final, $usuario_id);
    } else {
        $update_user = $conexion->prepare("UPDATE usuarios SET nombre = ?, apellidos = ?, pais = ?, fecha_nacimiento = ? WHERE id = ?");
        $update_user->bind_param("ssssi", $nuevo_nombre, $nuevo_apellidos, $nuevo_pais, $nueva_fecha, $usuario_id);
    }

    $update_user->execute();
    $update_user->close();

    // ==========================================
    // ACTUALIZAR TELÉFONO
    // ==========================================
    $update_cred = $conexion->prepare("UPDATE usuarios_credenciales SET telefono = ? WHERE usuario_id = ?");
    $update_cred->bind_param("si", $nuevo_telefono, $usuario_id);
    $exito = $update_cred->execute();
    $update_cred->close();

    return $exito;
}
?>