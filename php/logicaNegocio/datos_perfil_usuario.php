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

function actualizarPerfilUsuario($conexion, $usuario_id, $datos_post) {
    $nuevo_nombre = $datos_post['nombre'];
    $nuevo_apellidos = $datos_post['apellidos'];
    $nuevo_pais = $datos_post['pais'];
    $nuevo_email = $datos_post['email'];

    // Juntamos el día, mes y año para guardarlo en la base de datos (Formato YYYY-MM-DD)
    $nueva_fecha = $datos_post['ano'] . '-' . str_pad($datos_post['mes'], 2, "0", STR_PAD_LEFT) . '-' . str_pad($datos_post['dia'], 2, "0", STR_PAD_LEFT);

    // Preparamos y ejecutamos la actualización
    $update = $conexion->prepare("UPDATE usuarios SET nombre=?, apellidos=?, pais=?, fecha_nacimiento=?, email=? WHERE id=?");
    $update->bind_param("sssssi", $nuevo_nombre, $nuevo_apellidos, $nuevo_pais, $nueva_fecha, $nuevo_email, $usuario_id);

    $exito = $update->execute();
    $update->close();

    return $exito;
}
?>