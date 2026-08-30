<?php
// 1. Datos de conexión comprobados
$servidor   = "localhost";
$usuario    = "manuela";
$contrasena = "elena2009plesk@";
$base_datos = "resignificarte";

// 2. Crear la conexión
$conexion = new mysqli($servidor, $usuario, $contrasena, $base_datos);

if ($conexion->connect_error) {
    die("DB_ERROR: " . $conexion->connect_error);
}

// Configurar caracteres para evitar errores con tildes
$conexion->set_charset("utf8");

// 3. Lógica para responder a Unity
if (isset($_POST['telefono'])) {
    $telefono = $conexion->real_escape_string($_POST['telefono']);

    // Busca en la tabla de credenciales y obtiene el nombre del usuario
    $sql = "SELECT u.nombre FROM usuarios u INNER JOIN usuarios_credenciales uc ON u.id = uc.usuario_id WHERE uc.telefono = '$telefono'";
    $result = $conexion->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "OK|" . $row["nombre"];
    } else {
        echo "ERROR";
    }
} else {
    echo "NO_POST";
}

$conexion->close();
?>