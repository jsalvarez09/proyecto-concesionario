<?php
// Iniciar la sesión para poder acceder a ella
session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Borrar la cookie de sesión del navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión del servidor.
session_destroy();

// Redirigir al usuario al homepage
header("Location: /NUEVO_FROME/index.php");
exit();
?>