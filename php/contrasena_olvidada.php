<?php
header('Content-Type: application/json');

// 1. Incluimos la conexión (como están en la misma carpeta 'php')
require_once __DIR__ . '/conexion.php';

// 2. Incluimos la lógica de negocio bajando directamente a logicaNegocio
require_once __DIR__ . '/logicaNegocio/envio_email_contrasena_olvidada.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim(isset($_POST['email']) ? $_POST['email'] : ''), FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Correo electrónico no válido.']);
        exit;
    }

    try {
        global $conexion;

        $usuario = buscarUsuarioPorEmail($conexion, $email);

        if (!$usuario) {
            echo json_encode(['success' => true, 'message' => 'Si el correo está registrado, recibirás las instrucciones en breve.']);
            exit;
        }

        $token = generarTokenRecuperacion($conexion, $email);
        $enviado = enviarCorreoRecuperacion($email, $token);

        // Enlace directo en pantalla para testear sin depender del servidor de correo
        $enlace_prueba = "https://mzazzar.upv.edu.es/restablecer_contrasena.php?token=" . $token;

        echo json_encode([
            'success' => true,
            'message' => 'Token generado correctamente. <br><br><a href="' . $enlace_prueba . '" target="_blank" style="color: #000; font-weight: bold; text-decoration: underline;">Haz clic aquí para cambiar tu contraseña</a>'
        ]);
        exit;

    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error interno en el servidor: ' . $e->getMessage()]);
    }
}
?>