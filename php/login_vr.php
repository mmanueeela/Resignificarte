<?php

$servidor   = "localhost";
$usuario    = "manuela";
$contrasena = "elena2009plesk@";
$base_datos = "resignificarte";

$conexion = new mysqli(
    $servidor,
    $usuario,
    $contrasena,
    $base_datos
);

if ($conexion->connect_error) {
    die("DB_ERROR");
}

$conexion->set_charset("utf8mb4");

if (!isset($_POST["telefono"])) {
    echo "NO_POST";
    exit;
}

$telefono = trim($_POST["telefono"]);

$sql = "
    SELECT 
        u.id,
        u.nombre
    FROM usuarios u
    INNER JOIN usuarios_credenciales uc
        ON u.id = uc.usuario_id
    WHERE uc.telefono = ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $telefono);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $row = $result->fetch_assoc();

    echo "OK|" . $row["id"] . "|" . $row["nombre"];

}
else if ($result->num_rows > 1) {

    // Esto detectará precisamente teléfonos duplicados
    echo "ERROR|TELEFONO_DUPLICADO";

}
else {

    echo "ERROR|NO_ENCONTRADO";
}

$stmt->close();
$conexion->close();

?>