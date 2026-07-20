<?php
// 1. Datos de conexión a la base de datos
$servidor   = "localhost";
$usuario    = "root";
$contrasena = "";
$base_datos = "resignificarte";

// 2. Crear la conexión
$conexion = new mysqli($servidor, $usuario, $contrasena, $base_datos);

// 3. Comprobar si la conexión ha fallado
if ($conexion->connect_error) {
    die("Error al conectar con la base de datos: " . $conexion->connect_error);
}

// 4. Configurar la codificación de caracteres a UTF-8 (para evitar símbolos raros en ñ y tildes)
if (!$conexion->set_charset("utf8")) {
    die("Error cargando el conjunto de caracteres utf8: " . $conexion->error);
}

?>