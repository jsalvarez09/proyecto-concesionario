<?php
// Parámetros de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "concesionario_db";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Establecer el juego de caracteres a UTF-8
$conn->set_charset("utf8");

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

/* DEFINICIÓN DE LA RUTA BASE 
   Cambia '/NUEVO_FROME/' por el nombre real de tu carpeta.
   Si subes esto a un hosting (ej. www.tuweb.com), cámbialo a '/'
*/
define('BASE_URL', '/NUEVO_FROME/');
?>