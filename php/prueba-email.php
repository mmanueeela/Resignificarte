<?php

$destinatario = "ferransansaloni@gmail.com";

$asunto = "Prueba Resignificarte";

$mensaje = "Este es un correo de prueba.";

$headers = "From: resiignificaarte@gmail.com\r\n";
$headers .= "Reply-To: resiignificaarte@gmail.com\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$resultado = mail(
    $destinatario,
    $asunto,
    $mensaje,
    $headers,
    "-f resiignificaarte@gmail.com"
);

var_dump($resultado);

if (!$resultado) {
    echo "<pre>";
    print_r(error_get_last());
    echo "</pre>";
}