<?php
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

        // ¡NUEVO! Capturamos la foto de perfil que nos manda Google
        $foto_perfil = $info_usuario->picture;

        // 4. Comprobar si este correo ya existe en nuestra base de datos
        $consulta = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
        $consulta->bind_param("s", $email);
        $consulta->execute();
        $resultado = $consulta->get_result();

        if ($resultado->num_rows > 0) {
            // EL USUARIO YA EXISTE -> Le hacemos LOGIN directo
            $usuario = $resultado->fetch_assoc();
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $nombre;

            // ¡NUEVO! Actualizamos la foto de perfil en la BD por si la ha cambiado en Google
            $update_foto = $conexion->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
            $update_foto->bind_param("si", $foto_perfil, $usuario['id']);
            $update_foto->execute();

            // Redirigir al panel principal o homepage
            header("Location: ../homepage_usuario_registrado.php?login=exito");
            exit();

        } else {
            // EL USUARIO NO EXISTE -> Lo REGISTRAMOS

            // Generamos datos genéricos para los campos obligatorios de tu BD
            $pais_generico = "ND";
            $fecha_generica = "2000-01-01";
            // Contraseña aleatoria e imposible de adivinar (entrará siempre por Google)
            $password_aleatoria = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT);

            // ¡NUEVO! Añadimos el campo foto_perfil a la consulta INSERT
            $insertar = $conexion->prepare("INSERT INTO usuarios (nombre, apellidos, pais, fecha_nacimiento, email, password, metodo_registro, foto_perfil) VALUES (?, ?, ?, ?, ?, ?, 'google', ?)");
            // Ahora pasamos 7 variables 's' (strings) en el bind_param
            $insertar->bind_param("sssssss", $nombre, $apellidos, $pais_generico, $fecha_generica, $email, $password_aleatoria, $foto_perfil);

            if ($insertar->execute()) {
                // Registro exitoso, iniciamos sesión
                $_SESSION['usuario_id'] = $insertar->insert_id;
                $_SESSION['usuario_nombre'] = $nombre;

                // Enviar correo electrónico al registrarse
                $asunto = "¡Bienvenido a Resignificarte!";

                // Puedes usar HTML básico para hacerlo más bonito
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

                // Cabeceras para que el correo se lea como HTML y tenga remitente
                $cabeceras  = 'MIME-Version: 1.0' . "\r\n";
                $cabeceras .= 'Content-type: text/html; charset=utf-8' . "\r\n";
                $cabeceras .= 'From: Resignificarte <hola@resignificarte.com>' . "\r\n";

                // Enviamos el correo
                mail($email, $asunto, $mensaje, $cabeceras);
                // ------------------------------------------

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
    // Si intentan entrar aquí sin código, los devolvemos al login
    header("Location: ../login.html");
    exit();
}
?>