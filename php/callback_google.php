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
        $apellidos = isset($info_usuario->familyName)
            ? $info_usuario->familyName
            : '';

        $foto_perfil = isset($info_usuario->picture)
            ? $info_usuario->picture
            : '';

        // ==========================================
        // COMPROBAR SI EL USUARIO YA EXISTE
        // ==========================================
        $consulta = $conexion->prepare("
            SELECT u.id, u.foto_perfil, c.telefono
            FROM usuarios u
            JOIN usuarios_credenciales c
                ON u.id = c.usuario_id
            WHERE c.email = ?
        ");

        $consulta->bind_param("s", $email);
        $consulta->execute();

        $resultado = $consulta->get_result();

        if ($resultado->num_rows > 0) {

            // ==========================================
            // EL USUARIO YA EXISTE -> LOGIN
            // ==========================================
            session_regenerate_id(true);

            $usuario = $resultado->fetch_assoc();

            $id_usuario = $usuario['id'];
            $foto_actual = isset($usuario['foto_perfil'])
                ? $usuario['foto_perfil']
                : '';

            $telefono_actual = isset($usuario['telefono'])
                ? $usuario['telefono']
                : '';

            $_SESSION['usuario_id'] = $id_usuario;
            $_SESSION['usuario_nombre'] = $nombre;

            // Actualizar foto de Google si procede
            if (
                !empty($foto_perfil) &&
                strpos($foto_actual, 'src/uploads/perfiles/') === false
            ) {
                $update_foto = $conexion->prepare("
                    UPDATE usuarios
                    SET foto_perfil = ?
                    WHERE id = ?
                ");

                if ($update_foto) {
                    $update_foto->bind_param(
                        "si",
                        $foto_perfil,
                        $id_usuario
                    );

                    $update_foto->execute();
                    $update_foto->close();
                }
            }

            // ==========================================
            // RECUÉRDAME
            // ==========================================
            $token_cookie = bin2hex(random_bytes(32));
            $token_hasheado = hash('sha256', $token_cookie);

            $update_token = $conexion->prepare("
                UPDATE usuarios_credenciales
                SET remember_token = ?
                WHERE usuario_id = ?
            ");

            $update_token->bind_param(
                "si",
                $token_hasheado,
                $id_usuario
            );

            $update_token->execute();
            $update_token->close();

            setcookie("recuerdame_token", $token_cookie, [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            header("Location: ../homepage.php?login=exito");
            exit();

        } else {

            // ==========================================
            // USUARIO NUEVO
            // ==========================================
            // NO LO CREAMOS TODAVÍA.
            // Primero necesitamos su teléfono.
            // ==========================================
            $_SESSION['registro_google'] = [
                'email' => $email,
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'foto_perfil' => $foto_perfil
            ];

            header("Location: ../telefono_google.php");
            exit();
        }

    } else {
        die("Error al obtener el token de Google.");
    }

} else {
    header("Location: ../login.php");
    exit();
}
?>