<?php
header("Content-Type: text/plain; charset=UTF-8");

$servidor   = "localhost";
$usuario    = "manuela";
$contrasena = "elena2009plesk@";
$base_datos = "resignificarte";

$conexion = new mysqli($servidor, $usuario, $contrasena, $base_datos);

if ($conexion->connect_error) {
    die("ERROR|DB");
}

$conexion->set_charset("utf8mb4");

// ----------------------------
// COMPROBAR DATOS
// ----------------------------
if (!isset($_POST["usuario_id"]) || !isset($_POST["obra_id"]) || !isset($_POST["comentario"])) {
    echo "ERROR|FALTAN_DATOS";
    exit;
}

$usuarioId = intval($_POST["usuario_id"]);
$obraId = intval($_POST["obra_id"]);
$comentario = trim($_POST["comentario"]);

// ----------------------------
// VALIDACIONES
// ----------------------------
if ($usuarioId <= 0) {
    echo "ERROR|USUARIO_INVALIDO";
    exit;
}

if ($obraId < 1 || $obraId > 4) {
    echo "ERROR|OBRA_INVALIDA";
    exit;
}

if ($comentario === "") {
    echo "ERROR|COMENTARIO_VACIO";
    exit;
}

// ----------------------------
// COMPROBAR USUARIO
// ----------------------------
$sqlUsuario = "SELECT id FROM usuarios WHERE id = ?";
$stmtUsuario = $conexion->prepare($sqlUsuario);
$stmtUsuario->bind_param("i", $usuarioId);
$stmtUsuario->execute();
$resultUsuario = $stmtUsuario->get_result();

if ($resultUsuario->num_rows === 0) {
    echo "ERROR|USUARIO_NO_EXISTE";
    $stmtUsuario->close();
    $conexion->close();
    exit;
}
$stmtUsuario->close();

// ----------------------------
// COMPROBAR OBRA
// ----------------------------
$sqlObra = "SELECT id FROM obras WHERE id = ?";
$stmtObra = $conexion->prepare($sqlObra);
$stmtObra->bind_param("i", $obraId);
$stmtObra->execute();
$resultObra = $stmtObra->get_result();

if ($resultObra->num_rows === 0) {
    echo "ERROR|OBRA_NO_EXISTE";
    $stmtObra->close();
    $conexion->close();
    exit;
}
$stmtObra->close();

// ----------------------------
// GUARDAR COMENTARIO
// ----------------------------
$sqlComentario = "INSERT INTO comentarios (obra_id, usuario_id, comentario) VALUES (?, ?, ?)";
$stmtComentario = $conexion->prepare($sqlComentario);
$stmtComentario->bind_param("iis", $obraId, $usuarioId, $comentario);

if ($stmtComentario->execute()) {
    echo "OK|COMENTARIO_GUARDADO";
} else {
    echo "ERROR|INSERT";
}

$stmtComentario->close();
$conexion->close();
?>