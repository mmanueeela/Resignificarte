<?php

function obtenerDatosUsuario($conexion, $usuario_id) {
    // Añadimos 'foto_perfil' a la consulta
    $consulta = "SELECT nombre, apellidos, email, pais, fecha_nacimiento, foto_perfil FROM usuarios WHERE id = ?";
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
    $nuevo_email = $datos_post['email'];
    $nueva_fecha = $datos_post['ano'] . '-' . str_pad($datos_post['mes'], 2, "0", STR_PAD_LEFT) . '-' . str_pad($datos_post['dia'], 2, "0", STR_PAD_LEFT);

    $ruta_foto_final = null;

    // --- PROCESAR LA IMAGEN SI SE HA SUBIDO UNA NUEVA ---
    if ($archivo_foto && $archivo_foto['error'] === UPLOAD_ERR_OK) {
        // 1. Validaciones de seguridad backend
        $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
        if (in_array($archivo_foto['type'], $permitidos) && $archivo_foto['size'] <= 2097152) { // 2MB max

            // 2. Extraer extensión y generar nombre único para evitar sobreescribir fotos (ej: user_5_168430.jpg)
            $extension = pathinfo($archivo_foto['name'], PATHINFO_EXTENSION);
            $nombre_archivo = "user_" . $usuario_id . "_" . time() . "." . $extension;

            // 3. Ruta de destino
            $ruta_fisica = __DIR__ . '/../../src/uploads/perfiles/' . $nombre_archivo;
            $ruta_db = 'src/uploads/perfiles/' . $nombre_archivo;

            // 4. Movemos el archivo de la memoria temporal a nuestra carpeta
            if (move_uploaded_file($archivo_foto['tmp_name'], $ruta_fisica)) {
                $ruta_foto_final = $ruta_db;
            }
        }
    }

    // --- ACTUALIZAR LA BASE DE DATOS ---
    if ($ruta_foto_final) {
        // Si hay foto nueva, actualizamos todo incluyendo la foto
        $update = $conexion->prepare("UPDATE usuarios SET nombre=?, apellidos=?, pais=?, fecha_nacimiento=?, email=?, foto_perfil=? WHERE id=?");
        $update->bind_param("ssssssi", $nuevo_nombre, $nuevo_apellidos, $nuevo_pais, $nueva_fecha, $nuevo_email, $ruta_foto_final, $usuario_id);
    } else {
        // Si no subió foto, actualizamos solo los datos de texto (dejando la foto intacta)
        $update = $conexion->prepare("UPDATE usuarios SET nombre=?, apellidos=?, pais=?, fecha_nacimiento=?, email=? WHERE id=?");
        $update->bind_param("sssssi", $nuevo_nombre, $nuevo_apellidos, $nuevo_pais, $nueva_fecha, $nuevo_email, $usuario_id);
    }

    $exito = $update->execute();
    $update->close();

    return $exito;
}
?>