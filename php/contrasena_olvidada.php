<?php
header('Content-Type: application/json');

// Requerimos las funciones de la lógica de negocio
require_once 'php/logicaNegocio/envio_email_contrasena_olvidada.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim(isset($_POST['email']) ? $_POST['email'] : ''), FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Correo electrónico no válido.']);
        exit;
    }

    try {
        // La variable global $conexion ya viene creada desde tu conexion.php incluido en las funciones
        global $conexion;

        $usuario = buscarUsuarioPorEmail($conexion, $email);

        // Seguridad: Si el usuario no existe, respondemos con éxito genérico para evitar que descubran qué emails están registrados
        if (!$usuario) {
            echo json_encode(['success' => true, 'message' => 'Si el correo está registrado, recibirás las instrucciones en breve.']);
            exit;
        }

        $token = generarTokenRecuperacion($conexion, $email);
        $enviado = enviarCorreoRecuperacion($email, $token);

        if ($enviado) {
            echo json_encode(['success' => true, 'message' => 'Si el correo está registrado, recibirás las instrucciones en breve.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al enviar el correo. Inténtalo más tarde.']);
        }

    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error interno en el servidor.']);
    }
}