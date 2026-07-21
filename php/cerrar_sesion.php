<?php
session_start();

// 1. Borramos todas las variables de sesión
session_unset();
session_destroy();

// 2. Destruimos la cookie mágica de "Recuérdame" poniéndole una fecha de caducidad en el pasado
if (isset($_COOKIE['recuerdame_token'])) {
    setcookie("recuerdame_token", "", time() - 3600, "/");
}

// 3. Lo devolvemos al login
header("Location: ../login.html");
exit();
?>