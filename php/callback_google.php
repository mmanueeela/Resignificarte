<?php
// ¡IMPORTANTE! Añadimos session_start() para que el login funcione correctamente
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexion.php';
require_once 'config_google.php';

// 1. Verificar si Google nos devolvió un código de éxito
if (isset($_GET['code'])) {

    // 2. Intercambiar el código por un token de acceso real
    $token = $cliente->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token['error'])) {
        $cliente->setAccessToken($token['access_token']);

        // 3. Obtener los datos del perfil de Google
        $servicio_oauth = new Google\Service\Oauth2($cliente);
        $info_usuario = $servicio_oauth->userinfo->get();

        $email = $info_usuario->email;
        $nombre = $info_usuario->givenName;
        $apellidos = isset($info_usuario->familyName) ? $info_usuario->familyName : '';
        $foto_perfil = $info_usuario->picture;

        // 4. Comprobar si este correo ya existe en nuestra base de datos
        $consulta = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
        $consulta->bind_param("s", $email);
        $consulta->execute();
        $resultado = $consulta->get_result();

        if ($resultado->num_rows > 0) {
            // ==========================================
            // EL USUARIO YA EXISTE -> Le hacemos LOGIN directo
            // ==========================================
            session_regenerate_id(true); // Seguridad

            $usuario = $resultado->fetch_assoc();
            $id_usuario = $usuario['id'];

            $_SESSION['usuario_id'] = $id_usuario;
            $_SESSION['usuario_nombre'] = $nombre;

            // Actualizamos la foto de perfil
            $update_foto = $conexion->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
            $update_foto->bind_param("si", $foto_perfil, $id_usuario);
            $update_foto->execute();

            // --- LÓGICA DE RECUÉRDAME ---
            $token_cookie = bin2hex(random_bytes(32));
            $token_hasheado = hash('sha256', $token_cookie);

            $update_token = $conexion->prepare("UPDATE usuarios SET remember_token = ? WHERE id = ?");
            $update_token->bind_param("si", $token_hasheado, $id_usuario);
            $update_token->execute();

            setcookie("recuerdame_token", $token_cookie, [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            // -----------------------------

            header("Location: ../homepage_usuario_registrado.php?login=exito");
            exit();

        } else {
            // ==========================================
            // EL USUARIO NO EXISTE -> Lo REGISTRAMOS
            // ==========================================
            $pais_generico = "ND";
            $fecha_generica = "2000-01-01";
            $password_aleatoria = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT);

            $insertar = $conexion->prepare("INSERT INTO usuarios (nombre, apellidos, pais, fecha_nacimiento, email, password, metodo_registro, foto_perfil) VALUES (?, ?, ?, ?, ?, ?, 'google', ?)");
            $insertar->bind_param("sssssss", $nombre, $apellidos, $pais_generico, $fecha_generica, $email, $password_aleatoria, $foto_perfil);

            if ($insertar->execute()) {
                session_regenerate_id(true); // Seguridad

                $nuevo_id = $insertar->insert_id;

                $_SESSION['usuario_id'] = $nuevo_id;
                $_SESSION['usuario_nombre'] = $nombre;

                // --- LÓGICA DE RECUÉRDAME ---
                $token_cookie = bin2hex(random_bytes(32));
                $token_hasheado = hash('sha256', $token_cookie);

                $update_token = $conexion->prepare("UPDATE usuarios SET remember_token = ? WHERE id = ?");
                $update_token->bind_param("si", $token_hasheado, $nuevo_id);
                $update_token->execute();

                setcookie("recuerdame_token", $token_cookie, [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
                // -----------------------------

                // Enviar correo electrónico al registrarse
                $asunto = "¡Bienvenido a Resignificarte!";
                $mensaje = "
                <html>
                <head><title>Bienvenido</title></head>
                <body>
                    <h2>¡Hola $nombre!</h2>
                    <p>Gracias por registrarte en <b>Resignificarte</b> usando tu cuenta de Google.</p>
                    <p>Estamos muy felices de tenerte con nosotros.</p>
                    <br>
                    <p>Un abrazo,</p>
                    <p>El equipo de Resignificarte</p>
                </body>
                </html>
                ";

                $cabeceras  = 'MIME-Version: 1.0' . "\r\n";
                $cabeceras .= 'Content-type: text/html; charset=utf-8' . "\r\n";
                $cabeceras .= 'From: Resignificarte <hola@resignificarte.com>' . "\r\n";

                mail($email, $asunto, $mensaje, $cabeceras);

                header("Location: ../homepage_usuario_registrado.php?registro=google_exito");
                exit();
            } else {
                die("Error al registrar con Google: " . $conexion->error);
            }
        }
    } else {
        die("Error al obtener el token de Google.");
    }
} else {
    header("Location: ../login.php");
    exit();
}
?>