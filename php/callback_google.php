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

            $insertar = $conexion->prepare("INSERT INTO usuarios (nombre, apellidos, pais, fecha_nacimiento, email, password) VALUES (?, ?, ?, ?, ?, ?)");
            $insertar->bind_param("ssssss", $nombre, $apellidos, $pais_generico, $fecha_generica, $email, $password_aleatoria);

            if ($insertar->execute()) {
                // Registro exitoso, iniciamos sesión
                $_SESSION['usuario_id'] = $insertar->insert_id;
                $_SESSION['usuario_nombre'] = $nombre;
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