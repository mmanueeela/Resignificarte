<?php
require_once "conexion.php";

// 1. Comprobar que viene por POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../contacto.php");
    exit;
}

// 2. Recoger datos
$nombre = trim($_POST["nombre"]);
$email = trim($_POST["email"]);
$asunto = trim($_POST["asunto"]);
$mensaje = trim($_POST["mensaje"]);

// 3. Validar campos vacíos
if (empty($nombre) || empty($email) || empty($asunto) || empty($mensaje)) {
    header("Location: ../contacto.php?error=campos");
    exit;
}

// 4. Configurar el correo
$destinatario = "mzazzar@epsg.upv.es";
$titulo = "Contacto Resignificarte: " . $asunto;

$cuerpo = "Nombre:\n$nombre\n\nEmail:\n$email\n\nMensaje:\n$mensaje";

// ========================================================
// EL ARREGLO ESTÁ AQUÍ (CABECERAS SEGURAS)
// ========================================================
$headers = "From: mzazzar@epsg.upv.es\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// 5. Enviar el email
if (mail($destinatario, $titulo, $cuerpo, $headers, "-f mzazzar@epsg.upv.es")) {
    header("Location: ../contacto.php?exito=enviado");
} else {
    header("Location: ../contacto.php?error=email");
}

exit;
?>