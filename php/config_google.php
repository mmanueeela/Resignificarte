<?php
// Cargar la librería de Google instalada por Composer
require_once '../vendor/autoload.php';

// Iniciar sesión para guardar variables temporales si fuera necesario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cliente = new Google\Client();
// Ruta a tu archivo JSON con las credenciales
$cliente->setAuthConfig(__DIR__ . '/credenciales_google.json');

// Qué datos le pedimos a Google
$cliente->addScope("email");
$cliente->addScope("profile");

// URI configurada en la consola de Google Cloud.
$cliente->setRedirectUri('http://localhost/Resignificarte/php/callback_google.php');
?>