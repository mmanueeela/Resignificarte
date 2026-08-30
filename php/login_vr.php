<?php
$servidor   = "localhost";
$usuario    = "manuela";
$contrasena = "elena2009plesk@";
$base_datos = "resignificarte";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Conexion fallida");

if (isset($_POST['telefono'])) {
    $telefono = $conn->real_escape_string($_POST['telefono']);

    // Cruzamos las dos tablas para obtener el nombre si el teléfono coincide
    $sql = "SELECT u.nombre 
            FROM usuarios_credenciales uc 
            INNER JOIN usuarios u ON uc.usuario_id = u.id 
            WHERE uc.telefono = '$telefono'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "OK|" . $row["nombre"];
    } else {
        echo "ERROR";
    }
}
$conn->close();
?>