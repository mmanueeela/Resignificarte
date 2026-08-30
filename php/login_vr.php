<?php
$servidor   = "localhost";
$usuario    = "manuela";
$contrasena = "elena2009plesk@";
$base_datos = "resignificarte";

$conn = new mysqli($servername, $username, $password, $dbname);

// Si falla la conexión a la base de datos, devuelve un error específico
if ($conn->connect_error) {
    die("DB_ERROR");
}

if (isset($_POST['telefono'])) {
    $telefono = $conn->real_escape_string($_POST['telefono']);

    // Unimos las dos tablas para obtener el nombre a partir del teléfono
    $sql = "SELECT u.nombre FROM usuarios u INNER JOIN usuarios_credenciales uc ON u.id = uc.usuario_id WHERE uc.telefono = '$telefono'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "OK|" . $row["nombre"];
    } else {
        echo "ERROR";
    }
} else {
    echo "NO_POST";
}
$conn->close();
?>