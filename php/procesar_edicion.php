<?php
session_start();
require_once 'conexion.php';

// 1. SEGURIDAD
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("Location: ../homepage.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $artista_id = intval($_POST['artista_id']);
    $nombre_artista = trim($_POST['nombre_artista']);
    $pais = trim($_POST['pais']);

    // --- COMPROBAR SI HAY FOTO DE ARTISTA NUEVA ---
    $dir_artistas = "../src/uploads/artistas/";
    if (!file_exists($dir_artistas)) mkdir($dir_artistas, 0777, true);

    if (isset($_FILES['foto_artista']['name']) && $_FILES['foto_artista']['error'] === 0) {
        $ext = pathinfo($_FILES['foto_artista']['name'], PATHINFO_EXTENSION);
        $nombre_img_artista = "artista_" . time() . "." . $ext;
        $ruta_fisica = $dir_artistas . $nombre_img_artista;

        if (move_uploaded_file($_FILES['foto_artista']['tmp_name'], $ruta_fisica)) {
            $ruta_foto_artista = "src/uploads/artistas/" . $nombre_img_artista;

            // Actualizamos la foto en la BD
            $stmt_img_art = $conexion->prepare("UPDATE artistas SET imagen_perfil = ? WHERE id = ?");
            $stmt_img_art->bind_param("si", $ruta_foto_artista, $artista_id);
            $stmt_img_art->execute();
            $stmt_img_art->close();
        }
    }

    // 2. ACTUALIZAR DATOS TEXTUALES DEL ARTISTA
    $stmt = $conexion->prepare("UPDATE artistas SET nombre = ?, pais = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nombre_artista, $pais, $artista_id);
    $stmt->execute();
    $stmt->close();

    // Preparar carpetas por si suben archivos nuevos
    $nombre_carpeta = preg_replace('/[^a-zA-Z0-9_]/', '_', str_replace(' ', '_', $nombre_artista));
    $dir_cuadros = "../src/uploads/cuadros/" . $nombre_carpeta . "/";
    $dir_audios = "../src/uploads/audios/" . $nombre_carpeta . "/";

    if (!file_exists($dir_cuadros)) mkdir($dir_cuadros, 0777, true);
    if (!file_exists($dir_audios)) mkdir($dir_audios, 0777, true);

    // 3. ACTUALIZAR CUADROS
    $obras_ids = $_POST['obra_id'];
    $titulos = $_POST['titulos'];
    $transcripciones = $_POST['transcripciones'];
    $recompensas = isset($_POST['es_recompensa']) ? $_POST['es_recompensa'] : [];

    for ($i = 0; $i < count($obras_ids); $i++) {
        $obra_id = intval($obras_ids[$i]);
        $titulo = trim($titulos[$i]);
        $transcripcion = trim($transcripciones[$i]);
        $es_recompensa = isset($recompensas[$i]) ? 1 : 0;

        // Primero actualizamos los datos de texto
        $stmt_texto = $conexion->prepare("UPDATE obras SET titulo = ?, transcripcion = ?, es_recompensa = ? WHERE id = ?");
        $stmt_texto->bind_param("ssii", $titulo, $transcripcion, $es_recompensa, $obra_id);
        $stmt_texto->execute();
        $stmt_texto->close();

        // Si han subido una IMAGEN nueva para este cuadro
        if (isset($_FILES['imagenes']['name'][$i]) && $_FILES['imagenes']['error'][$i] === 0) {
            $ext = pathinfo($_FILES['imagenes']['name'][$i], PATHINFO_EXTENSION);
            $nombre_img = "cuadro_" . $obra_id . "_" . time() . "." . $ext;
            $ruta_fisica_img = $dir_cuadros . $nombre_img;

            if (move_uploaded_file($_FILES['imagenes']['tmp_name'][$i], $ruta_fisica_img)) {
                $imagen_path = "src/uploads/cuadros/" . $nombre_carpeta . "/" . $nombre_img;
                $stmt_img = $conexion->prepare("UPDATE obras SET imagen = ? WHERE id = ?");
                $stmt_img->bind_param("si", $imagen_path, $obra_id);
                $stmt_img->execute();
                $stmt_img->close();
            }
        }

        // Si han subido un AUDIO nuevo para este cuadro
        if (isset($_FILES['audios']['name'][$i]) && $_FILES['audios']['error'][$i] === 0) {
            $ext = pathinfo($_FILES['audios']['name'][$i], PATHINFO_EXTENSION);
            $nombre_audio = "audio_" . $obra_id . "_" . time() . "." . $ext;
            $ruta_fisica_audio = $dir_audios . $nombre_audio;

            if (move_uploaded_file($_FILES['audios']['tmp_name'][$i], $ruta_fisica_audio)) {
                $audio_path = "src/uploads/audios/" . $nombre_carpeta . "/" . $nombre_audio;
                $stmt_aud = $conexion->prepare("UPDATE obras SET audio = ? WHERE id = ?");
                $stmt_aud->bind_param("si", $audio_path, $obra_id);
                $stmt_aud->execute();
                $stmt_aud->close();
            }
        }
    }

    // Al terminar, volvemos a la página de obras
    header("Location: ../obras.php?msg=experiencia_actualizada");
    exit();
} else {
    header("Location: ../obras.php");
    exit();
}
?>