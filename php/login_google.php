<?php
require_once 'config_google.php';

// Genera la URL de autenticación de Google y redirige al usuario
$url_autorizacion = $cliente->createAuthUrl();
header('Location: ' . $url_autorizacion);
exit();
?>