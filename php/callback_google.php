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
            SELECT
                u.id,
                u.foto_perfil,
                u.apellidos,
                u.pais,
                u.fecha_nacimiento,
                u.es_admin, /* AÑADIDO PARA ADMIN */
                c.telefono
            FROM usuarios u
            JOIN usuarios_credenciales c
                ON u.id = c.usuario_id
            WHERE c.email = ?
        ");

        if (!$consulta) {
            die("Error al preparar la consulta: " . $conexion->error);
        }

        $consulta->bind_param("s", $email);
        $consulta->execute();

        $resultado = $consulta->get_result();

        if ($resultado->num_rows > 0) {

            // ==========================================
            // EL USUARIO YA EXISTE
            // ==========================================
            $usuario = $resultado->fetch_assoc();

            $id_usuario = $usuario['id'];

            $foto_actual = trim($usuario['foto_perfil'] ?? '');
            $apellidos_actuales = trim($usuario['apellidos'] ?? '');
            $telefono_actual = trim($usuario['telefono'] ?? '');
            $pais_actual = trim($usuario['pais'] ?? '');
            $fecha_actual = trim($usuario['fecha_nacimiento'] ?? '');

            // ==========================================
            // COMPROBAR QUÉ DATOS FALTAN
            // ==========================================
            $falta_apellidos = empty($apellidos_actuales);
            $falta_telefono = empty($telefono_actual);
            $falta_pais = empty($pais_actual);
            $falta_fecha = empty($fecha_actual);

            // ==========================================
            // SI FALTA ALGÚN DATO
            // ==========================================
            if (
                $falta_apellidos ||
                $falta_telefono ||
                $falta_pais ||
                $falta_fecha
            ) {

                $_SESSION['registro_google'] = [
                    'usuario_existente_id' => $id_usuario,
                    'usuario_existente' => true,
                    'email' => $email,
                    'nombre' => $nombre,
                    'apellidos' => $apellidos_actuales,
                    'foto_perfil' => $foto_perfil,
                    'telefono' => $telefono_actual,
                    'pais' => $pais_actual,
                    'fecha_nacimiento' => $fecha_actual
                ];

                $consulta->close();

                header("Location: ../telefono_google.php");
                exit();
            }

            // ==========================================
            // TODOS LOS DATOS ESTÁN COMPLETOS
            // -> LOGIN
            // ==========================================
            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $id_usuario;
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['es_admin'] = $usuario['es_admin']; /* AÑADIDO PARA ADMIN */

            // ==========================================
            // ACTUALIZAR FOTO DE GOOGLE SI PROCEDE
            // ==========================================
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

            if ($update_token) {

                $update_token->bind_param(
                    "si",
                    $token_hasheado,
                    $id_usuario
                );

                $update_token->execute();
                $update_token->close();
            }

            setcookie("recuerdame_token", $token_cookie, [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            $consulta->close();

            if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1) {
                header("Location: ../homepage_administrador.php?login=exito");
            } else {
                header("Location: ../homepage.php?login=exito");
            }
            exit();

        } else {

            // ==========================================
            // USUARIO NUEVO
            // ==========================================
            $_SESSION['registro_google'] = [
                'usuario_existente_id' => null,
                'usuario_existente' => false,
                'email' => $email,
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'foto_perfil' => $foto_perfil,
                'telefono' => '',
                'pais' => '',
                'fecha_nacimiento' => ''
            ];

            $consulta->close();

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