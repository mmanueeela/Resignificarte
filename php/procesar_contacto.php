<?php
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../contacto.php");
    exit;
}

$nombre = trim($_POST["nombre"]);
$email = trim($_POST["email"]);
$asunto = trim($_POST["asunto"]);
$mensaje = trim($_POST["mensaje"]);

if (empty($nombre) || empty($email) || empty($asunto) || empty($mensaje)) {
    header("Location: ../contacto.php?error=campos");
    exit;
}

$destinatario = "mzazzar@epsg.upv.es";
$titulo = "Contacto Resignificarte: " . $asunto;

$cuerpo = "Nombre:\n$nombre\n\nEmail:\n$email\n\nMensaje:\n$mensaje";

$headers = "From: " . $email . "\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

if (mail($destinatario, $titulo, $cuerpo, $headers)) {
    header("Location: ../contacto.php?exito=enviado");
} else {
    header("Location: ../contacto.php?error=email");
}

exit;
?>