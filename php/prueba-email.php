<?php

$destinatario = "ferransansaloni@gmail.com";

$asunto = "Prueba Resignificarte";

$mensaje = "Este es un correo de prueba.";

$headers = "From: mzazzar@epsg.upv.es\r\n";
$headers .= "Reply-To: mzazzar@epsg.upv.es\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$resultado = mail(
    $destinatario,
    $asunto,
    $mensaje,
    $headers,
    "-f mzazzar@epsg.upv.es"
);

var_dump($resultado);

if (!$resultado) {
    echo "<pre>";
    print_r(error_get_last());
    echo "</pre>";
}