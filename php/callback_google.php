<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexion.php';
require_once 'config_google.php';

if (isset($_GET['code'])) {
    $token = $cliente->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token['error'])) {
        $cliente->setAccessToken($token['access_token']);

        $servicio_oauth = new Google\Service\Oauth2($cliente);
        $info_usuario = $servicio_oauth->userinfo->get();

        $email = $info_usuario->email;
        $nombre = $info_usuario->givenName;
        $apellidos = isset($info_usuario->familyName) ? $info_usuario->familyName : '';
        $foto_perfil = $info_usuario->picture;

        // 1. Comprobar si este correo ya existe uniendo ambas tablas
        $consulta = $conexion->prepare("
            SELECT u.id, u.foto_perfil 
            FROM usuarios u 
            JOIN usuarios_credenciales c ON u.id = c.usuario_id 
            WHERE c.email = ?
        ");
        $consulta->bind_param("s", $email);
        $consulta->execute();
        $resultado = $consulta->get_result();

        if ($resultado->num_rows > 0) {
            // ==========================================
            // EL USUARIO YA EXISTE -> Login directo
            // ==========================================
            session_regenerate_id(true);

            $usuario = $resultado->fetch_assoc();
            $id_usuario = $usuario['id'];
            $foto_actual = isset($usuario['foto_perfil']) ? $usuario['foto_perfil'] : '';

            $_SESSION['usuario_id'] = $id_usuario;
            $_SESSION['usuario_nombre'] = $nombre;

            if (strpos($foto_actual, 'src/uploads/perfiles/') === false) {
                $update_foto = $conexion->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
                if ($update_foto) {
                    $update_foto->bind_param("si", $foto_perfil, $id_usuario);
                    $update_foto->execute();
                    $update_foto->close();
                }
            }

            // Lógica de Recuérdame (en usuarios_credenciales)
            $token_cookie = bin2hex(random_bytes(32));
            $token_hasheado = hash('sha256', $token_cookie);

            $update_token = $conexion->prepare("UPDATE usuarios_credenciales SET remember_token = ? WHERE usuario_id = ?");
            $update_token->bind_param("si", $token_hasheado, $id_usuario);
            $update_token->execute();
            $update_token->close();

            setcookie("recuerdame_token", $token_cookie, [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            header("Location: ../homepage_usuario_registrado.php?login=exito");
            exit();

        } else {
            // ==========================================
            // EL USUARIO NO EXISTE -> Lo registramos
            // ==========================================
            $pais_generico = "ND";
            $fecha_generica = "2000-01-01";
            $password_aleatoria = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT);

            // A. Insertar en tabla 'usuarios'
            $insertar_user = $conexion->prepare("INSERT INTO usuarios (nombre, apellidos, pais, fecha_nacimiento, foto_perfil) VALUES (?, ?, ?, ?, ?)");
            $insertar_user->bind_param("sssss", $nombre, $apellidos, $pais_generico, $fecha_generica, $foto_perfil);

            if ($insertar_user->execute()) {
                $nuevo_id = $insertar_user->insert_id;
                $insertar_user->close();

                // B. Insertar en tabla 'usuarios_credenciales'
                $insertar_cred = $conexion->prepare("INSERT INTO usuarios_credenciales (usuario_id, email, password, metodo_registro) VALUES (?, ?, ?, 'google')");
                $insertar_cred->bind_param("iss", $nuevo_id, $email, $password_aleatoria);
                $insertar_cred->execute();
                $insertar_cred->close();

                session_regenerate_id(true);

                $_SESSION['usuario_id'] = $nuevo_id;
                $_SESSION['usuario_nombre'] = $nombre;

                // C. Lógica de Recuérdame
                $token_cookie = bin2hex(random_bytes(32));
                $token_hasheado = hash('sha256', $token_cookie);

                $update_token = $conexion->prepare("UPDATE usuarios_credenciales SET remember_token = ? WHERE usuario_id = ?");
                $update_token->bind_param("si", $token_hasheado, $nuevo_id);
                $update_token->execute();
                $update_token->close();

                setcookie("recuerdame_token", $token_cookie, [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);

                // Correo electrónico al registrarse con Google
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
                $cabeceras .= 'From: Resignificarte <hola@mzazzar.upv.edu.es>' . "\r\n";

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