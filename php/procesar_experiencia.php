<?php
session_start();
require_once 'conexion.php';

// 1. SEGURIDAD: Solo Administradores
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("Location: ../homepage.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_artista = trim($_POST['nombre_artista']);
    $pais = trim($_POST['pais']);

    // Reemplazamos espacios por guiones bajos para que la ruta sea limpia
    $nombre_carpeta = preg_replace('/[^a-zA-Z0-9_]/', '_', str_replace(' ', '_', $nombre_artista));

    // --- NUEVO: PREPARAR CARPETAS Y SUBIR FOTO DEL ARTISTA ---
    $dir_artistas = "../src/uploads/artistas/";
    if (!file_exists($dir_artistas)) mkdir($dir_artistas, 0777, true);

    $ruta_foto_artista = "";
    if (isset($_FILES['foto_artista']['name']) && $_FILES['foto_artista']['error'] === 0) {
        $ext = pathinfo($_FILES['foto_artista']['name'], PATHINFO_EXTENSION);
        $nombre_img_artista = "artista_" . time() . "." . $ext;
        $ruta_fisica = $dir_artistas . $nombre_img_artista;

        if (move_uploaded_file($_FILES['foto_artista']['tmp_name'], $ruta_fisica)) {
            $ruta_foto_artista = "src/uploads/artistas/" . $nombre_img_artista;
        }
    }

    // 2. CREAR EL ARTISTA EN LA BASE DE DATOS (AHORA CON LA IMAGEN DE PERFIL)
    $stmt = $conexion->prepare("INSERT INTO artistas (nombre, pais, imagen_perfil) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre_artista, $pais, $ruta_foto_artista);
    if (!$stmt->execute()) {
        die("Error al guardar el artista en la base de datos.");
    }
    $artista_id = $stmt->insert_id; // Obtenemos el ID que se le acaba de asignar
    $stmt->close();

    // 3. PREPARAR CARPETAS PARA LOS CUADROS Y AUDIOS
    $dir_cuadros = "../src/uploads/cuadros/" . $nombre_carpeta . "/";
    $dir_audios = "../src/uploads/audios/" . $nombre_carpeta . "/";

    // Si no existen las carpetas, las creamos
    if (!file_exists($dir_cuadros)) mkdir($dir_cuadros, 0777, true);
    if (!file_exists($dir_audios)) mkdir($dir_audios, 0777, true);

    // 4. PROCESAR CADA CUADRO DEL FORMULARIO
    $titulos = $_POST['titulos'];
    $transcripciones = $_POST['transcripciones'];
    $recompensas = isset($_POST['es_recompensa']) ? $_POST['es_recompensa'] : [];

    for ($i = 0; $i < count($titulos); $i++) {
        $titulo = trim($titulos[$i]);
        $transcripcion = trim($transcripciones[$i]);
        // Si el checkbox de esta posición está marcado, es recompensa
        $es_recompensa = isset($recompensas[$i]) ? 1 : 0;

        // --- SUBIR IMAGEN DEL CUADRO ---
        $imagen_path = "";
        if (isset($_FILES['imagenes']['name'][$i]) && $_FILES['imagenes']['error'][$i] === 0) {
            $ext = pathinfo($_FILES['imagenes']['name'][$i], PATHINFO_EXTENSION);
            $nombre_img = "cuadro_" . ($i + 1) . "_" . time() . "." . $ext;
            $ruta_fisica_img = $dir_cuadros . $nombre_img;

            if (move_uploaded_file($_FILES['imagenes']['tmp_name'][$i], $ruta_fisica_img)) {
                // Ruta relativa para la base de datos
                $imagen_path = "src/uploads/cuadros/" . $nombre_carpeta . "/" . $nombre_img;
            }
        }

        // --- SUBIR AUDIO DEL CUADRO ---
        $audio_path = "";
        if (isset($_FILES['audios']['name'][$i]) && $_FILES['audios']['error'][$i] === 0) {
            $ext = pathinfo($_FILES['audios']['name'][$i], PATHINFO_EXTENSION);
            $nombre_audio = "audio_" . ($i + 1) . "_" . time() . "." . $ext;
            $ruta_fisica_audio = $dir_audios . $nombre_audio;

            if (move_uploaded_file($_FILES['audios']['tmp_name'][$i], $ruta_fisica_audio)) {
                // Ruta relativa para la base de datos
                $audio_path = "src/uploads/audios/" . $nombre_carpeta . "/" . $nombre_audio;
            }
        }

        // 5. GUARDAR LA OBRA SI HAY DATOS BÁSICOS
        if (!empty($titulo) && !empty($imagen_path) && !empty($audio_path)) {
            $stmt_obra = $conexion->prepare("INSERT INTO obras (artista_id, titulo, imagen, audio, transcripcion, es_recompensa) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_obra->bind_param("issssi", $artista_id, $titulo, $imagen_path, $audio_path, $transcripcion, $es_recompensa);
            $stmt_obra->execute();
            $stmt_obra->close();
        }
    }

    // Al terminar, volvemos al panel con éxito
    header("Location: ../homepage_administrador.php?msg=experiencia_creada");
    exit();
} else {
    header("Location: ../homepage_administrador.php");
    exit();
}
?>