<?php

$destinatario = "ferransansaloni@gmail.com";

$asunto = "Prueba Resignificarte";

$mensaje = "Este es un correo de prueba.";

$headers = "From: hola@mzazzaro.epsg.upv.es\r\n";
$headers .= "Reply-To: hola@mzazzaro.epsg.upv.es\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$resultado = mail(
    $destinatario,
    $asunto,
    $mensaje,
    $headers,
    "-f hola@mzazzaro.epsg.edu.es"
);

var_dump($resultado);

if (!$resultado) {
    echo "<pre>";
    print_r(error_get_last());
    echo "</pre>";
}